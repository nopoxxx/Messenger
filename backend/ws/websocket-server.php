<?php

class WebSocketServer implements MessageComponentInterface
{
    private $db;
    private $sessionModel;
    private $clients = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->sessionModel = new Session($db);
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo "Новое соединение ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $conn, $msg)
    {
        $data = json_decode($msg, true);
        if (!$data || !isset($data['action'])) {
            return;
        }

        switch ($data['action']) {
            case 'auth':
                $this->auth($conn, $data['token']);
                break;
            case 'getUsers':
                $this->getUsers($conn);
                break;
            case 'getContacts':
                $this->getContacts($conn);
                break;
            case 'addContact':
                $this->addContact($conn, $data['contactId']);
                break;
            case 'deleteContact':
                $this->deleteContact($conn, $data['contactId']);
                break;
            case 'sendMessage':
                $this->sendMessage($conn, $data['receiverId'], $data['message']);
                break;
            case 'getChatMessages':
                $this->getChatMessages($conn, $data['contactId']);
                break;
            case 'deleteMessage':
                $this->deleteMessage($conn, $data['messageId']);
                break;
            case 'editMessage':
                $this->editMessage($conn, $data['messageId'], $data['message']);
                break;
        }
    }

    private function auth($conn, $token)
    {
        $checkToken = $this->sessionModel->check($token);

        if ($checkToken['status'] === 'error') {
            $conn->send(json_encode(['action' => 'error', 'message' => $checkToken['desc']]));
            $conn->close();
            return;
        }

        if ($checkToken['status'] === 'ok') {
            $userId = $checkToken['desc'];
        }

        if ($userId !== null) {
            $this->clients[$conn->resourceId] = $userId;
            echo "Пользователь {$userId} подключился.\n";
        }
    }

    private function getUsers($conn)
    {
        $userId = $this->clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "SELECT id, username, email, avatar, email_visibility FROM users";
        $stmt = $this->db->query($query);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array_map(function ($user) {
            return [
                    'id' => $user['id'],
                    'username' => $user['username'] ?? null,
                    'email' => $user['email_visibility'] ? $user['email'] : null,
                    'avatar' => $user['avatar']
            ];
        }, $users);

        $conn->send(json_encode([
                'action' => 'getUsers',
                'users' => $result
        ]));
    }


    private function getContacts($conn)
    {
        $userId = $this->clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "
            SELECT 
                CASE 
                    WHEN user_id = :userId THEN contact_id
                    ELSE user_id
                END AS contactId
            FROM contacts
            WHERE user_id = :userId OR contact_id = :userId
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['userId' => $userId]);
        $contacts = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($contacts)) {
            $conn->send(json_encode(['action' => 'getContacts', 'contacts' => []]));
            return;
        }

        $placeholders = implode(',', array_fill(0, count($contacts), '?'));
        $query = "
            SELECT id, username, email, avatar, email_visibility
            FROM users
            WHERE id IN ($placeholders)
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute($contacts);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $contactsData = array_map(function ($user) {
            return [
                'id' => $user['id'],
                'username' => $user['username'] ?? null,
                'email' => $user['email_visibility'] ? $user['email'] : null,
                'avatar' => $user['avatar']
            ];
        }, $users);

        $conn->send(json_encode(['action' => 'getContacts', 'contacts' => $contactsData]));
    }

    private function addContact($conn, $contactId)
    {
        $userId = $this->clients[$conn->resourceId] ?? null;

        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Вы не авторизованы']));
            return;
        }

        if ($userId === $contactId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Нельзя добавить себя в контакты']));
            return;
        }

        $query = "
						INSERT INTO contacts (user_id, contact_id)
						VALUES (:userId, :contactId)
						ON DUPLICATE KEY UPDATE user_id = user_id
				";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['userId' => $userId, 'contactId' => $contactId]);

        if (isset($this->clients[$contactId])) {
            $this->clients[$contactId]->send(json_encode([
                    'action' => 'contactAdded'
            ]));
        }

        $conn->send(json_encode([
                'action' => 'contactAdded'
        ]));
    }

    private function deleteContact($conn, $contactId)
    {
        $userId = $this->clients[$conn->resourceId] ?? null;

        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Вы не авторизованы']));
            return;
        }

        $query = "
						DELETE FROM contacts WHERE user_id, contact_id = :userId, :contactId
						OR user_id, contact_id = :contactId, :userId
						VALUES (:userId, :contactId)
				";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['userId' => $userId, 'contactId' => $contactId]);

        if (isset($this->clients[$contactId])) {
            $this->clients[$contactId]->send(json_encode([
                    'action' => 'contactDeleted'
            ]));
        }

        $conn->send(json_encode([
                'action' => 'contactDeleted'
        ]));
    }

    private function sendMessage($conn, $receiverId, $message)
    {
        $userId = $this->clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (:senderId, :receiverId, :message)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['senderId' => $userId, 'receiverId' => $receiverId, 'message' => $message]);

        $messageId = $this->db->lastInsertId();
        $sentAt = date('Y-m-d H:i:s');

        if (isset($this->clients[$receiverId])) {
            $this->clients[$receiverId]->send(json_encode([
                'action' => 'newMessage',
                'senderId' => $userId,
                'receiverId' => $receiverId,
                'message' => $message,
                'sentAt' => $sentAt
            ]));
        }

        $conn->send(json_encode([
            'action' => 'messageSent',
            'messageId' => $messageId,
            'receiverId' => $receiverId,
            'message' => $message,
            'sentAt' => $sentAt
        ]));
    }

    private function getChatMessages($conn, $contactId)
    {
        $userId = $this->clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "SELECT sender_id, receiver_id, message, sent_at FROM messages WHERE (sender_id = :userId AND receiver_id = :contactId) OR (sender_id = :contactId AND receiver_id = :userId) ORDER BY sent_at ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['userId' => $userId, 'contactId' => $contactId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $conn->send(json_encode(['action' => 'chatMessages', 'messages' => $messages]));
    }

    private function deleteMessage($conn, $messageId)
    {
        $userId = $this->clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "DELETE FROM messages WHERE id = :messageId AND sender_id = :userId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['messageId' => $messageId, 'userId' => $userId]);

        if ($stmt->rowCount() > 0) {
            $conn->send(json_encode(['action' => 'messageDeleted', 'messageId' => $messageId]));
        } else {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Сообщение не найдено или нет прав на удаление']));
        }
    }

    private function editMessage($conn, $messageId, $newMessage)
    {
        $userId = $this->clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "UPDATE messages SET message = :message WHERE id = :messageId AND sender_id = :userId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['message' => $newMessage, 'messageId' => $messageId, 'userId' => $userId]);

        if ($stmt->rowCount() > 0) {
            $conn->send(json_encode(['action' => 'messageEdited', 'messageId' => $messageId, 'message' => $newMessage]));
        } else {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Сообщение не найдено или нет прав на редактирование']));
        }
    }


    public function onClose(ConnectionInterface $conn)
    {
        $userId = $this->clients[$conn->resourceId] ?? null;
        unset($this->clients[$conn->resourceId]);

        if ($userId) {
            echo "Пользователь {$userId} отключился.\n";
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Ошибка: {$e->getMessage()}\n";
        $conn->close();
    }
}

$db = Database::connect();

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketServer($db)
        )
    ),
    8080
);

echo "WebSocket сервер запущен на порту 8080\n";
$server->run();

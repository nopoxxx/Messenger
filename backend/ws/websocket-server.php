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

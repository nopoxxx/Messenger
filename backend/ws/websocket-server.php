<?php

require_once 'controllers/MessageController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/ContactController.php';
require_once 'controllers/GroupController.php';

class WebSocketServer implements MessageComponentInterface
{
    private $db;
    private $clients = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
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
                UserController::auth($conn, $data['token'], $this->clients, $this->db);
                break;
            case 'getUsers':
                UserController::getUsers($conn, $this->clients, $this->db);
                break;
            case 'getContacts':
                ContactController::getContacts($conn, $this->clients, $this->db);
                break;
            case 'addContact':
                ContactController::addContact($conn, $data['contactId'], $this->clients, $this->db);
                break;
            case 'deleteContact':
                ContactController::deleteContact($conn, $data['contactId'], $this->clients, $this->db);
                break;
            case 'sendMessage':
                MessageController::sendMessage($conn, $data['receiverId'], $data['message'], $data['isGroup'], $this->clients, $this->db);
                break;
            case 'getChatMessages':
                MessageController::getChatMessages($conn, $data['contactId'], $data['isGroup'], $this->clients, $this->db);
                break;
            case 'deleteMessage':
                MessageController::deleteMessage($conn, $data['messageId'], $this->clients, $this->db);
                break;
            case 'editMessage':
                MessageController::editMessage($conn, $data['messageId'], $data['message'], $this->clients, $this->db);
                break;
            case 'createGroup':
                GroupController::createGroup($conn, $data['groupName'], $this->clients, $this->db);
                break;
            case 'addGroupMember':
                GroupController::addGroupMember($conn, $data['groupId'], $data['memberId'], $this->clients, $this->db);
                break;
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

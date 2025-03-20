<?php

require 'vendor/autoload.php';

require_once __DIR__ . '/../config/database.php';

require_once 'controllers/MessageController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/ContactController.php';
require_once 'controllers/GroupController.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

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
        error_log("Новое соединение ({$conn->resourceId})\n");
    }

    public function onMessage(ConnectionInterface $conn, $msg)
    {
        $data = json_decode($msg, true);
        if (!$data || !isset($data['action'])) {
            return;
        }

        switch ($data['action']) {
            case 'auth':
                error_log("Новая авторизация ({$conn->resourceId})\n");
                UserController::auth($conn, $data['token'], $this->clients, $this->db);
                break;
            case 'getUsers':
                UserController::getUsers($conn, $this->clients, $this->db);
                break;
            case 'setProfile':
                UserController::setProfile($conn, $data['avatar'], $data['username'], $data['isEmailVisible'], $this->clients, $this->db);
                break;
            case 'getProfile':
                error_log("Запрос профиля ({$conn->resourceId})\n");
                UserController::getProfile($conn, $this->clients, $this->db);
                break;
            case 'sendMessage':
                MessageController::sendMessage($conn, $data['receiverId'], $data['message'], $data['isGroup'], $this->clients, $this->db);
                break;
            case 'getChatMessages':
                MessageController::getChatMessages($conn, $data['contactId'], $data['isGroup'], $this->clients, $this->db);
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

<?php

class MessageController
{
    public static function sendMessage($conn, $receiverId, $message, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (:senderId, :receiverId, :message)";
        $stmt = $db->prepare($query);
        $stmt->execute(['senderId' => $userId, 'receiverId' => $receiverId, 'message' => $message]);

        $messageId = $db->lastInsertId();
        $sentAt = date('Y-m-d H:i:s');

        $message = [
            'id' => $messageId,
            'user_name' => UserController::getDisplayNameById($db, $userId),
            'sender_name' => UserController::getDisplayNameById($db, $userId),
            'receiver_name' => UserController::getDisplayNameById($db, $receiverId),
            'message' => $message,
            'sent_at' => $sentAt
        ];

        if (isset($clients[$receiverId])) {
            $clients[$receiverId]->send(json_encode([
                'action' => 'newMessage',
                'data' => $message
            ]));
        }

        $conn->send(json_encode([
            'action' => 'messageSent',
            'data' => $message

        ]));
    }

    public static function getChatMessages($conn, $receiverId, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "SELECT id, sender_id, receiver_id, message, sent_at FROM messages WHERE (sender_id = :userId AND receiver_id = :receiverId) OR (sender_id = :receiverId AND receiver_id = :userId) ORDER BY sent_at ASC";
        $stmt = $db->prepare($query);
        $stmt->execute(['userId' => $userId, 'receiverId' => $receiverId]);

        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($messages as &$message) {
            $message['sender_name'] = UserController::getDisplayNameById($db, $message['sender_id']);
            $message['user_name'] = UserController::getDisplayNameById($db, $message['sender_id']);
            $message['receiver_name'] = UserController::getDisplayNameById($db, $message['receiver_id']);
            unset($message['sender_id'], $message['receiver_id']);
        }

        $conn->send(json_encode([
            'action' => 'getChatMessages',
            'data' => $messages
        ]));
    }
}

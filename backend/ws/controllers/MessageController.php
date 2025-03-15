<?php

class MessageController
{
    public static function sendMessage($conn, $receiverId, $message, $isGroup, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        if ($isGroup) {
            // Сообщение для группы
            $query = "INSERT INTO chat_messages (chat_id, sender_id, message) VALUES (:chatId, :senderId, :message)";
            $stmt = $db->prepare($query);
            $stmt->execute(['chatId' => $receiverId, 'senderId' => $userId, 'message' => $message]);

            $messageId = $db->lastInsertId();
            $sentAt = date('Y-m-d H:i:s');

            // Отправляем сообщение всем участникам группы
            $query = "SELECT user_id FROM chat_members WHERE chat_id = :chatId";
            $stmt = $db->prepare($query);
            $stmt->execute(['chatId' => $receiverId]);
            $members = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($members as $memberId) {
                if (isset($clients[$memberId])) {
                    $clients[$memberId]->send(json_encode([
                        'action' => 'newGroupMessage',
                        'groupId' => $receiverId,
                        'senderId' => $userId,
                        'message' => $message,
                        'sentAt' => $sentAt
                    ]));
                }
            }

            $conn->send(json_encode([
                'action' => 'groupMessageSent',
                'messageId' => $messageId,
                'groupId' => $receiverId,
                'message' => $message,
                'sentAt' => $sentAt
            ]));
        } else {
            // Обычное личное сообщение
            $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (:senderId, :receiverId, :message)";
            $stmt = $db->prepare($query);
            $stmt->execute(['senderId' => $userId, 'receiverId' => $receiverId, 'message' => $message]);

            $messageId = $db->lastInsertId();
            $sentAt = date('Y-m-d H:i:s');

            if (isset($clients[$receiverId])) {
                $clients[$receiverId]->send(json_encode([
                    'action' => 'newMessage',
                    'senderId' => $userId,
                    'receiverId' => $receiverId,
                    'message' => $message,
                    'sentAt' => $sentAt
                ]));
            }

            $conn->send(json_encode([
                'action' => 'messageSent',
                'data' => [
                    'receiver_id' => $receiverId,
                    'sender_id' => $userId,
                    'message' => $message,
                    'sent_at' => $sentAt
                ]
            ]));
        }
    }

    public static function getChatMessages($conn, $chatId, $isGroup, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        if ($isGroup) {
            // Получаем сообщения группы
            $query = "SELECT sender_id, message, created_at FROM chat_messages WHERE chat_id = :chatId ORDER BY created_at ASC";
        } else {
            // Получаем личные сообщения между пользователями
            $query = "SELECT sender_id, receiver_id, message, sent_at FROM messages WHERE (sender_id = :userId AND receiver_id = :chatId) OR (sender_id = :chatId AND receiver_id = :userId) ORDER BY sent_at ASC";
        }

        $stmt = $db->prepare($query);
        if ($isGroup) {
            $stmt->execute(['chatId' => $chatId]);
        } else {
            $stmt->execute(['userId' => $userId, 'chatId' => $chatId]);
        }

        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $conn->send(json_encode([
            'action' => 'getChatMessages',
            'data' => $messages
        ]));
    }

    public static function deleteMessage($conn, $messageId, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "DELETE FROM messages WHERE id = :messageId AND sender_id = :userId";
        $stmt = $db->prepare($query);
        $stmt->execute(['messageId' => $messageId, 'userId' => $userId]);

        if ($stmt->rowCount() > 0) {
            $conn->send(json_encode(['action' => 'messageDeleted', 'messageId' => $messageId]));
        } else {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Сообщение не найдено или нет прав на удаление']));
        }
    }

    public static function editMessage($conn, $messageId, $newMessage, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "UPDATE messages SET message = :message WHERE id = :messageId AND sender_id = :userId";
        $stmt = $db->prepare($query);
        $stmt->execute(['message' => $newMessage, 'messageId' => $messageId, 'userId' => $userId]);

        if ($stmt->rowCount() > 0) {
            $conn->send(json_encode(['action' => 'messageEdited', 'messageId' => $messageId, 'message' => $newMessage]));
        } else {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Сообщение не найдено или нет прав на редактирование']));
        }
    }
}

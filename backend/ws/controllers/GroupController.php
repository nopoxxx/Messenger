<?php

class GroupController
{
    public static function createGroup($conn, $groupName, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "INSERT INTO chats (name, owner_id) VALUES (:name, :ownerId)";
        $stmt = $db->prepare($query);
        $stmt->execute(['name' => $groupName, 'ownerId' => $userId]);

        $groupId = $db->lastInsertId();
        $conn->send(json_encode(['action' => 'groupCreated', 'groupId' => $groupId, 'name' => $groupName]));
    }

    public static function addGroupMember($conn, $groupId, $memberId, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "INSERT INTO chat_members (chat_id, user_id) VALUES (:chatId, :userId)";
        $stmt = $db->prepare($query);
        $stmt->execute(['chatId' => $groupId, 'userId' => $memberId]);

        $conn->send(json_encode(['action' => 'memberAdded', 'groupId' => $groupId, 'userId' => $memberId]));
    }
}

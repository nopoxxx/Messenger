<?php

class ContactController
{
    public static function getContacts($conn, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
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

        $stmt = $db->prepare($query);
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

        $stmt = $db->prepare($query);
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

    public static function addContact($conn, $contactId, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;

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

        $stmt = $db->prepare($query);
        $stmt->execute(['userId' => $userId, 'contactId' => $contactId]);

        if (isset($clients[$contactId])) {
            $clients[$contactId]->send(json_encode([
                'action' => 'contactAdded'
            ]));
        }

        $conn->send(json_encode([
            'action' => 'contactAdded'
        ]));
    }

    public static function deleteContact($conn, $contactId, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;

        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Вы не авторизованы']));
            return;
        }

        $query = "
            DELETE FROM contacts WHERE (user_id = :userId AND contact_id = :contactId)
            OR (user_id = :contactId AND contact_id = :userId)
        ";

        $stmt = $db->prepare($query);
        $stmt->execute(['userId' => $userId, 'contactId' => $contactId]);

        if (isset($clients[$contactId])) {
            $clients[$contactId]->send(json_encode([
                'action' => 'contactDeleted'
            ]));
        }

        $conn->send(json_encode([
            'action' => 'contactDeleted'
        ]));
    }
}

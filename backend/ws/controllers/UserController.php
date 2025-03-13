<?php

class UserController
{
    public static function auth($conn, $token, &$clients, $db)
    {
        $sessionModel = new Session($db);
        $checkToken = $sessionModel->check($token);

        if ($checkToken['status'] === 'error') {
            $conn->send(json_encode(['action' => 'error', 'message' => $checkToken['desc']]));
            $conn->close();
            return;
        }

        if ($checkToken['status'] === 'ok') {
            $userId = $checkToken['desc'];
        }

        if ($userId !== null) {
            $clients[$conn->resourceId] = $userId;
            echo "Пользователь {$userId} подключился.\n";
        }
    }

    public static function getUsers($conn, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "SELECT id, username, email, avatar, email_visibility FROM users";
        $stmt = $db->query($query);
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
}

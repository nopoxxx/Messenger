<?php

require_once __DIR__ . "/../../app/models/Session.php";

class UserController
{
    public static function auth($conn, $token, &$clients, $db)
    {
        $sessionModel = new Session($db);
        $checkToken = $sessionModel->check($token);

        if ($checkToken['status'] === 'error') {
            $conn->send(json_encode(['action' => 'error', 'data' => $checkToken['desc']]));
            $conn->close();
            return;
        }

        if ($checkToken['status'] === 'ok') {
            $userId = $checkToken['desc'];
        }

        if ($userId !== null) {
            $clients[$conn->resourceId] = $userId;
            error_log("Авторизован ({$userId})\n");
        }
    }

    public static function setProfile($conn, $avatar, $username, $isEmailVisible, &$clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        try {
            $emailVisibility = $username ? ($isEmailVisible ? 1 : 0) : 1;
            $avatarName = null;

            if (isset($avatar['metadata']) && isset($avatar['file'])) {
                $meta = json_decode($avatar['metadata'], true);
                if (!$meta || !isset($meta['fileName']) || !isset($meta['fileType'])) {
                    throw new Exception("Неверные метаданные файла");
                }
                $fileName = uniqid() . "-" . basename($meta['fileName']);
                $filePath = __DIR__ . "/../../public/uploads/avatars/" . $fileName;

                $fileData = base64_decode($avatar['file']);
                if ($fileData === false) {
                    throw new Exception("Ошибка декодирования файла");
                }

                file_put_contents($filePath, $fileData);

                $avatarName = $fileName;
            }


            if ($avatarName !== null) {
                $query = "UPDATE users SET username = ?, avatar = ?, email_visibility = ? WHERE id = ?";
                $params = [$username, $avatarName, $emailVisibility, $userId];
            } else {
                $query = "UPDATE users SET username = ?, email_visibility = ? WHERE id = ?";
                $params = [$username, $emailVisibility, $userId];
            }

            $stmt = $db->prepare($query);
            $stmt->execute($params);

            $conn->send(json_encode([
                'action' => 'profileUpdated'
            ]));
        } catch (Exception $e) {
            $conn->send(json_encode([
                'action' => 'error',
                'data' => 'Ошибка обновления профиля: ' . $e->getMessage()
            ]));
        }
    }

    public static function getProfile($conn, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "SELECT username, avatar, email_visibility FROM users WHERE id = ?";
        $params = [$userId];
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Отправил профиль ({$conn->resourceId})\n");

        $conn->send(json_encode([
            'action' => 'getProfile',
            'data' => $result
        ]));
    }

    public static function getUsers($conn, $clients, $db)
    {
        $userId = $clients[$conn->resourceId] ?? null;
        if (!$userId) {
            $conn->send(json_encode(['action' => 'error', 'message' => 'Неавторизованный запрос']));
            return;
        }

        $query = "SELECT id, username, email, avatar, email_visibility FROM users WHERE id != ?";
        $params = [$userId];
        $stmt = $db->prepare($query);
        $stmt->execute($params);
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
            'data' => $result
        ]));
    }
}

<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/../services/JwtService.php';

class Session
{
    private $JwtService;
    private $expire;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->expire = $_ENV['SESSION_EXPIRE'];
        $this->JwtService = new JwtService();
    }

    public function create($userId)
    {
        try {
            $token = $this->JwtService->generateJWT($userId, $this->expire, "session");
            $stmt = $this->db->prepare("INSERT INTO sessions (user_id, token) VALUES (?, ?)");
            $stmt->execute([$userId, $token]);

            return ["status" => "ok", "desc" => $token];
        } catch (PDOException $e) {
            error_log("Ошибка Session create: " . $e->getMessage());
            return ["status" => "error", "desc" => "Внутренняя ошибка сервера"];
        }
    }

    public function destroy($userId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM sessions WHERE user_id = ?;");
            $stmt->execute([$userId]);

            return ["status" => "ok", "desc" => "Сессия завершена"];
        } catch (PDOException $e) {
            error_log("Ошибка Session destroy: " . $e->getMessage());
            return ["status" => "error", "desc" => "Внутренняя ошибка сервера"];
        }
    }


    public function check($jwt)
    {
        $userId = $this->JwtService->verify($jwt);

        if (!$userId) {
            try {
                $stmt = $this->db->prepare("DELETE FROM sessions WHERE token = ?;");
                $stmt->execute([$jwt]);
            } catch (PDOException $e) {
                error_log("Ошибка Session check: " . $e->getMessage());
            }
            return ["status" => "error", "desc" => "Недействительный или истёкший токен"];
        }

        $stmt = $this->db->prepare("SELECT * FROM sessions WHERE token = ?;");
        $stmt->execute([$jwt]);
        $session = $stmt->fetch();

        if (!$session) {
            return ["status" => "error", "desc" => "Сессия не найдена"];
        }

        return ["status" => "ok", "desc" => $userId];
    }

}

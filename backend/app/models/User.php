<?php

class User
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findByEmail($email)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch();
            return ["status" => "ok", "desc" => $result];
        } catch (PDOException $e) {
            error_log("Ошибка User findByEmail: " . $e->getMessage());
            return ["status" => "error", "desc" => "Внутренняя ошибка сервера"];
        }
    }

    public function findByNickname($nickname)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$nickname]);
            $result = $stmt->fetch();
            return ["status" => "ok", "desc" => $result];
        } catch (PDOException $e) {
            error_log("Ошибка User findByNickname: " . $e->getMessage());
            return ["status" => "error", "desc" => "Внутренняя ошибка сервера"];
        }
    }

    public function create($email, $password, $is_email_visible, $username = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["status" => "error", "desc" => "Некорректный email"];
        }

        $userByEmail = $this->findByEmail($email);

        if ($userByEmail["status"] === "error") {
            return ["status" => "error", "desc" => "Внутренняя ошибка сервера"];
        }

        if ($userByEmail["desc"] !== false) {
            return ["status" => "error", "desc" => "Пользователь с таким email уже зарегистрирован"];
        }

        $userByNickname = $this->findByNickname($username);

        if ($userByNickname["status"] === "error") {
            return ["status" => "error", "desc" => "Внутренняя ошибка сервера"];
        }

        if ($userByNickname["desc"] !== false) {
            return ["status" => "error", "desc" => "Пользователь с таким nickname уже зарегистрирован"];
        }

        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
            INSERT INTO users (email, password, username, avatar, email_visibility, email_confirmed) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
            $stmt->execute([$email, $password_hash, $username, null, $is_email_visible, 0]);
            $result = [$this->db->lastInsertId(), $username ? $username : $email];
            return ["status" => "ok", "desc" => $result];
        } catch (PDOException $e) {
            error_log("Ошибка User create: " . $e->getMessage());
            return ["status" => "error", "desc" => "Внутренняя ошибка сервера"];
        }
    }

    public function check($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?;");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ["status" => "error", "desc" => "Пользователь не найден"];
        }

        if (!password_verify($password, $user["password"])) {
            return ["status" => "error", "desc" => "Неверный пароль"];
        }

        return ["status" => "ok", "desc" => $user["id"]];
    }
}

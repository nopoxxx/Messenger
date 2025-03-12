<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Dotenv\Dotenv;

class JwtService
{
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'];
    }

    // Генерация JWT
    public function generateJWT($userId, $expire, $type)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $expire;
        $payload = [
            'typ' => $type,
            'exp' => $expirationTime,
            'sub' => $userId
        ];

        // Генерация токена
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    // Проверка JWT
    public function verify($jwt)
    {
        if (!$jwt) {
            return false;
        }
        try {
            // Декодируем токен
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            return $decoded->sub;
        } catch (ExpiredException $e) {
            error_log("Токен истёк: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Ошибка jwtService verify: " . $e->getMessage());
            return false;
        }
    }
}

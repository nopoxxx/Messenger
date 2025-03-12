<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/../services/JwtService.php';
require_once __DIR__ . '/../services/MailerService.php';

class Mail
{
    private $JwtService;
    private $mailerService;
    private $expire;
    private $db;
    private $base_url;

    public function __construct($db)
    {
        $this->db = $db;
        $this->expire = $_ENV['MAIL_TOKEN_EXPIRE'];
        $this->JwtService = new JwtService();
        $this->mailerService = new MailerService();
        $this->base_url = $_ENV['APP_URL'];
    }

    public function create($email, $userId)
    {
        try {
            $token = $this->JwtService->generateJWT($userId, $this->expire, "mail");

            $confirmation_link = "http://" . $this->base_url . "/confirm?token=$token";

            $email_template = <<<HTML
            <!DOCTYPE html>
            <html lang="ru">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Подтверждение email</title>
            </head>
            <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; text-align: center;">
                <div style="max-width: 600px; margin: 20px auto; background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
                    <h1 style="color: #333;">Подтвердите ваш email</h1>
                    <p style="font-size: 16px; color: #666;">Здравствуйте! Вы зарегистрировались в нашем сервисе. Чтобы подтвердить ваш email, нажмите на кнопку ниже:</p>
                    <a href="{$confirmation_link}" style="display: inline-block; padding: 12px 20px; margin-top: 20px; font-size: 16px; color: #ffffff; background: #007bff; text-decoration: none; border-radius: 5px;">Подтвердить email</a>
                    <p style="margin-top: 20px; font-size: 12px; color: #999;">Если вы не регистрировались, просто проигнорируйте это письмо.</p>
                </div>
            </body>
            </html>
            HTML;


            $result = $this->mailerService->sendEmail($email, 'Messenger by nopox', $email_template);

            if ($result !== true) {
                return ["status" => "error", "desc" => "Ошибка отправки email"];
            }

            return ["status" => "ok", "desc" => $token];
        } catch (PDOException $e) {
            error_log("Ошибка Mail create: " . $e->getMessage());
            return ["status" => "error", "desc" => "Внутренняя ошибка сервера"];
        }
    }

    public function activate($jwt)
    {
        $userId = $this->JwtService->verify($jwt);

        if (!$userId) {
            error_log("Ошибка подтверждения: токен " . $jwt . " недействителен");
            return ["status" => "error", "desc" => "Недействительный или истёкший токен"];
        }

        $stmt = $this->db->prepare("SELECT email_confirmed FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();


        try {
            $stmt = $this->db->prepare("UPDATE users SET email_confirmed = 1 WHERE id = ? AND email_confirmed = 0");
            $stmt->execute([$userId]);

            if ($stmt->rowCount() === 0) {
                return ["status" => "error", "desc" => "Email уже подтверждён"];
            }
        } catch (PDOException $e) {
            error_log("Ошибка Mail activate: " . $e->getMessage());
            return ["status" => "error", "desc" => "Не удалось подтвердить email"];

        }
        return ["status" => "ok", "desc" => true];
    }

}

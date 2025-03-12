<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['MAIL_HOST'];
        $this->mailer->Port = $_ENV['MAIL_PORT'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'];

        $this->mailer->setFrom($_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
    }

    public function sendEmail($to, $subject, $body)
    {
        try {
            error_log("Отправка email на: $to");

            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->isHTML(true);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Ошибка Mailer: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}

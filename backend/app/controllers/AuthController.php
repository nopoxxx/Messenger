<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Session.php';
require_once __DIR__ . '/../models/Mail.php';

class AuthController
{
    private $userModel;
    private $mailModel;
    private $sessionModel;

    public function __construct($db)
    {
        $this->userModel = new User($db);
        $this->mailModel = new Mail($db);
        $this->sessionModel = new Session($db);
    }

    public function register($email, $password, $username, $is_email_visible)
    {
        $user = $this->userModel->create($email, $password, $is_email_visible, $username);
        if ($user["status"] === "error") {
            Response::send(["status" => "error", "desc" => $user["desc"]]);
        }

        $mail = $this->mailModel->create($email, $user["desc"]);
        if ($mail["status"] === "error") {
            Response::send(["status" => "error", "desc" => $mail["desc"]]);
        }

        $session = $this->sessionModel->create($user["desc"]);
        if ($session["status"] === "error") {
            Response::send(["status" => "error", "desc" => $session["desc"]]);
        }
        Response::send(["status" => "ok", "desc" => $session["desc"]]);
    }

    public function login($email, $password)
    {
        $user = $this->userModel->check($email, $password);
        if ($user["status"] === "error") {
            Response::send(["status" => "error", "desc" => $user["desc"]]);
        }

        $session = $this->sessionModel->create($user["desc"]);
        if ($session["status"] === "error") {
            Response::send(["status" => "error", "desc" => $session["desc"]]);
        }
        Response::send(["status" => "ok", "desc" => $session["desc"]]);
    }

    public function confirm($token)
    {
        $mail = $this->mailModel->activate($token);
        if ($mail["status"] === "error") {
            Response::send(["status" => "error", "desc" => $mail["desc"]]);
        }
        Response::send(["status" => "ok", "desc" => $mail["desc"]]);
    }

    public function verifySession($token)
    {
        $session = $this->sessionModel->check($token);
        if ($session["status"] === "error") {
            Response::send(["status" => "error", "desc" => $session["desc"]]);
        }
        Response::send(["status" => "ok", "desc" => $session["desc"]]);

    }
}

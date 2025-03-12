<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/services/Response.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$db = Database::connect();

$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);

$requestPath = trim($parsedUrl['path'], '/');

parse_str($parsedUrl['query'] ?? '', $queryParams);

error_log("MainPath: " . $requestPath);
error_log("Query Parameters: " . json_encode($queryParams));

$uriParts = explode('/', $requestPath);
$mainPath = $uriParts[0] ?? '';
$params = array_slice($uriParts, 1);

switch ($mainPath) {
    case 'register':
        $controller = new AuthController($db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);
            $email = $input['email'] ?? $_POST['email'] ?? null;
            $password = $input['password'] ?? $_POST['password'] ?? null;
            $username = $input['username'] ?? $_POST['username'] ?? null;
            $is_email_visible = $input['is_email_visible'] ?? $_POST['is_email_visible'] ?? false;

            $controller->register($email, $password, $username, $is_email_visible);
        } else {
            Response::send(["status" => "error", "desc" => "Запрещённый метод"], 405);
        }
        break;

    case 'login':
        $controller = new AuthController($db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);
            $email = $input['email'] ?? $_POST['email'] ?? null;
            $password = $input['password'] ?? $_POST['password'] ?? null;

            $controller->login($email, $password);
        } else {
            Response::send(["status" => "error", "desc" => "Запрещённый метод"], 405);
        }
        break;

    case 'verify-session':
        $controller = new AuthController($db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);
            $token = $input['token'] ?? $_POST['token'] ?? null;

            $controller->verifySession($token);
        } else {
            Response::send(["status" => "error", "desc" => "Запрещённый метод"], 405);
        }
        break;

    case 'confirm':
        $token = $queryParams['token'] ?? null;

        if (!$token) {
            Response::send(["status" => "error", "desc" => "Токен не передан"], 400);
            exit();
        }

        $controller = new AuthController($db);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->confirm($token);
        } else {
            Response::send(["status" => "error", "desc" => "Запрещённый метод"], 405);
        }
        break;

    default:
        Response::send(["status" => "error", "desc" => "Не найдено"], 404);
        break;
}

<?php
header("Content-Type: application/json");
require_once 'jwt_helper.php';

$secret_key = "SARANYA256"; // Keep this same as jwt_helper.php
$users = [
    ["username" => "admin", "password" => password_hash("password123", PASSWORD_DEFAULT)]
];

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

 function authenticate() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["message" => "Access denied. No token provided."]);
        exit;
    }

    $token = trim(str_replace("Bearer ", "", $headers['Authorization']));

    try {
        $decoded = JWT::decode($token);
        return $decoded; // If successful, return decoded data
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Invalid or expired token", "error" => $e->getMessage()]);
        exit;
    }
}

if ($request[0] === "register" && $method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['username'], $input['password'])) {
        global $users;
        $users[] = [
            "username" => $input['username'],
            "password" => password_hash($input['password'], PASSWORD_DEFAULT)
        ];
        echo json_encode(["message" => "User registered successfully"]);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
    }
    exit;
}

if ($request[0] === "login" && $method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    global $users;
    foreach ($users as $user) {
        if ($user['username'] === $input['username'] && password_verify($input['password'], $user['password'])) {
            $token = JWT::encode(["username" => $user['username'], "exp" => time() + 3600]);
            echo json_encode(["token" => $token]);
            exit;
        }
    }
    http_response_code(401);
    echo json_encode(["message" => "Invalid credentials"]);
    exit;
}

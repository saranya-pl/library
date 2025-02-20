<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
require 'jwt_helper.php';  // Ensure JWT authentication is included

$secret_key = "SARANYA256"; // Keep this same as jwt_helper.php

// Sample book data (In a real app, fetch this from a database)
$books = [
    1 => ["id" => 1, "title" => "The Great Gatsby", "author" => "F. Scott Fitzgerald", "published_year" => 1925],
    2 => ["id" => 2, "title" => "1984", "author" => "George Orwell", "published_year" => 1949]
];

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

// Function to generate book summary using OpenAI API
function generateBookSummary($book) {
    $apiKey = "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";  // Replace with your OpenAI API key
    $prompt = "Summarize the book titled '{$book['title']}' by {$book['author']} published in {$book['published_year']}. Keep it brief.";

    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [["role" => "user", "content" => $prompt]],
        "temperature" => 0.7
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $apiKey"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? "Summary not available.";
}

// Secure authentication
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
        return $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Invalid or expired token", "error" => $e->getMessage()]);
        exit;
    }
}

// Handle book summary generation
if ($request[0] === "books" && $request[1] === "generate-summary" && $method === "POST") {
    authenticate(); // Ensure the request is authenticated

    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['book_id']) || !isset($books[$input['book_id']])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid book ID"]);
        exit;
    }

    $book = $books[$input['book_id']];
    $summary = generateBookSummary($book);

    echo json_encode([
        "book" => $book,
        "summary" => $summary
    ]);
    exit;
}
?>

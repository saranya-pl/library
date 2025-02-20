<?php
header("Content-Type: application/json");

// Sample in-memory storage
$books = [
    ["id" => 1, "title" => "The Great Gatsby", "author" => "F. Scott Fitzgerald", "published_year" => 1925],
    ["id" => 2, "title" => "1984", "author" => "George Orwell", "published_year" => 1949],
    ["id" => 3, "title" => "Harry Potter", "author" => "J K Rowling", "published_year" => 1990]
];

// Get the HTTP method and request data
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));

// Helper function to find book by ID
function findBookIndexById($id) {
    global $books;
    foreach ($books as $index => $book) {
        if ($book['id'] == $id) {
            return $index;
        }
    }
    return false;
}

switch ($method) {
    case 'GET':
        if (isset($request[1])) {
            $bookId = (int) $request[1];
            $index = findBookIndexById($bookId);
            if ($index !== false) {
                echo json_encode($books[$index]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Book not found"]);
            }
        } else {
            echo json_encode($books);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        if (isset($input['title'], $input['author'], $input['published_year'])) {
            $newBook = [
                "id" => count($books) + 1,
                "title" => $input['title'],
                "author" => $input['author'],
                "published_year" => $input['published_year']
            ];
            $books[] = $newBook;
            echo json_encode($newBook);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Invalid input"]);
        }
        break;

    case 'PUT':
        if (isset($request[1])) {
            $bookId = (int) $request[1];
            $index = findBookIndexById($bookId);
            if ($index !== false) {
                $input = json_decode(file_get_contents("php://input"), true);
                $books[$index] = array_merge($books[$index], $input);
                echo json_encode($books[$index]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Book not found"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Book ID required"]);
        }
        break;

    case 'DELETE':
        if (isset($request[1])) {
            $bookId = (int) $request[1];
            $index = findBookIndexById($bookId);
            if ($index !== false) {
                array_splice($books, $index, 1);
                echo json_encode(["message" => "Book deleted"]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Book not found"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Book ID required"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}
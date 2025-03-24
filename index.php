<?php
require 'config/database.php';
require 'models/CategoryModel.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Kết nối database
$database = new Database();
$db = $database->getConnection();
$categoryModel = new CategoryModel($db);

// Lấy phương thức HTTP
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if ($segments[0] === 'categories') {
    if ($method === 'GET') {
        if (isset($segments[1]) && is_numeric($segments[1])) {
            if ($categoryModel->read_single($segments[1])) {
                echo json_encode([
                    "id" => $categoryModel->id,
                    "name" => $categoryModel->name,
                    "description" => $categoryModel->description
                ]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Category not found"]);
            }
        } else {
            $stmt = $categoryModel->read();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        }
    }
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->name) && !empty($data->description)) {
            $categoryModel->name = $data->name;
            $categoryModel->description = $data->description;
            if ($categoryModel->create()) {
                echo json_encode(["message" => "Category created successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error creating category"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Invalid input"]);
        }
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->name) && !empty($data->description)) {
            $categoryModel->id = $data->id;
            $categoryModel->name = $data->name;
            $categoryModel->description = $data->description;
            if ($categoryModel->update()) {
                echo json_encode(["message" => "Category updated successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error updating category"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Invalid input"]);
        }
    }
    elseif ($method === 'DELETE') {
        if (isset($segments[1]) && is_numeric($segments[1])) {
            $categoryModel->id = $segments[1];
            if ($categoryModel->delete()) {
                echo json_encode(["message" => "Category deleted successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error deleting category"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Invalid ID"]);
        }
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Not Found"]);
}

?>

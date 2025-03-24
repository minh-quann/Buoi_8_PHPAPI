<?php
require 'config/database.php';
require 'models/ProductModel.php';
require 'models/CategoryModel.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$database = new Database();
$db = $database->getConnection();

$productModel = new ProductModel($db);
$categoryModel = new CategoryModel($db);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($uri, '/'));

if ($segments[0] === 'products') {
    if ($method === 'GET') {
        if (isset($segments[1]) && is_numeric($segments[1])) {
            $result = $productModel->getProductById($segments[1]);
        } else {
            $result = $productModel->getProducts();
        }
        echo json_encode($result);
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? '';
        $category_id = $data['category_id'] ?? '';

        $result = $productModel->addProduct($name, $description, $price, $category_id);

        if ($result === true) {
            echo json_encode(["success" => true, "message" => "Sản phẩm đã được thêm"]);
        } elseif (is_array($result)) {
            echo json_encode(["success" => false, "errors" => $result]);
        } else {
            echo json_encode(["success" => false, "message" => "Thêm sản phẩm thất bại"]);
        }
    } elseif ($method === 'PUT') {
        if (isset($segments[1]) && is_numeric($segments[1])) {
            $data = json_decode(file_get_contents("php://input"), true);
            $name = $data['name'] ?? '';
            $description = $data['description'] ?? '';
            $price = $data['price'] ?? '';
            $category_id = $data['category_id'] ?? '';

            $result = $productModel->updateProduct($segments[1], $name, $description, $price, $category_id);

            if ($result) {
                echo json_encode(["success" => true, "message" => "Cập nhật sản phẩm thành công"]);
            } else {
                echo json_encode(["success" => false, "message" => "Cập nhật sản phẩm thất bại"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Thiếu ID sản phẩm"]);
        }
    } elseif ($method === 'DELETE') {
        if (isset($segments[1]) && is_numeric($segments[1])) {
            $result = $productModel->deleteProduct($segments[1]);

            if ($result) {
                echo json_encode(["success" => true, "message" => "Xóa sản phẩm thành công"]);
            } else {
                echo json_encode(["success" => false, "message" => "Xóa sản phẩm thất bại"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Thiếu ID sản phẩm"]);
        }
    }
} elseif ($segments[0] === 'categories') {
    if ($method === 'GET') {
        if (isset($segments[1]) && is_numeric($segments[1])) {
            echo json_encode($categoryModel->read_single($segments[1]));
        } else {
            $stmt = $categoryModel->read();
            $categories = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $categories[] = ["id" => $id, "name" => $name, "description" => $description];
            }
            echo json_encode($categories);
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->name) && !empty($data->description)) {
            $categoryModel->name = $data->name;
            $categoryModel->description = $data->description;

            if ($categoryModel->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Category created successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error creating category"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Missing required fields"]);
        }
    } elseif ($method === 'PUT') {
        if (isset($segments[1]) && is_numeric($segments[1])) {
            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->name) && !empty($data->description)) {
                $categoryModel->id = $segments[1];
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
                echo json_encode(["message" => "Missing required fields"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Invalid category ID"]);
        }
    } elseif ($method === 'DELETE') {
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
            echo json_encode(["message" => "Invalid category ID"]);
        }
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Not Found"]);
}
?>

<?php
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../helpers/ResponseHelper.php';

class CategoryController {
    private $db;
    private $conn;
    private $category;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->category = new Category($this->conn);
    }

    public function getAllCategories() {
        try {
            $categories = $this->category->getAllCategories();

            if (!$categories) {
                ResponseHelper::sendResponse(404, ["message" => "No categories found"]);
            }

            ResponseHelper::sendResponse(200, $categories);
        } catch (PDOException $e) {
            ResponseHelper::sendResponse(500, ["error" => "Database error", "message" => $e->getMessage()]);
        }
    }
}
?>

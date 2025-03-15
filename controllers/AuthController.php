<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../helpers/jwt_helper.php";


class AuthController{
    private $db;
    private $conn;
    private $user;

    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->user = new User($this->conn);
    }

    public function login($data) {
        try {
            if (empty($data['regNo']) || empty($data['password'])) {
                http_response_code(400); // Bad Request
                echo json_encode(["message" => "Missing regNo or password"]);
                return;
            }
    
            $userData = $this->user->login($data['regNo']);
            if (!$userData) {
                http_response_code(401); // Unauthorized
                echo json_encode(["message" => "Invalid credentials"]);
                return;
            }
    
            if (!password_verify($data['password'], $userData['password'])) {
                http_response_code(401); // Unauthorized
                echo json_encode(["message" => "Invalid credentials"]);
                return;
            }
    
            $token = createJWT($userData['userId'], $userData['regNo']);
    
            http_response_code(200); // OK
            echo json_encode(["token" => $token]);
    
        } catch (Exception $e) {
            http_response_code(500); // Internal Server Error
            echo json_encode(["error" => "An error occurred", "message" => $e->getMessage()]);
        }
    }
    
}
?>
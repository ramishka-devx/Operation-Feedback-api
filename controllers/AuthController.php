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
        $userData = $this->user->login($data['regNo']);
        if ($userData && password_verify($data['password'], $userData['password'])) {
            $token = createJWT($userData['userId'], $userData['regNo']);
            echo json_encode(["token" => $token]);
        } else {
            echo json_encode(["message" => "Invalid credentials"]);
        }
    }
}
?>
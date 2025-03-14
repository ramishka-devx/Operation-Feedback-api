<?php
require_once __DIR__ . '/../models/Complain.php';
require_once __DIR__ . '/../helpers/ResponseHelper.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
class ComplainController {
    private $db;
    private $conn;
    private $complain;

    public function __construct() { 
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->complain = new Complain($this->conn);
        
    }

    public function getAllComplaints($params = []) {
        try {
            $sortOrder = isset($params['sortOrder']) ? strtoupper($params['sortOrder']) : 'DESC';
            if (!in_array($sortOrder, ['ASC', 'DESC'])) {
                $sortOrder = 'DESC';
            }

            $complaints = $this->complain->getAllComplaints($sortOrder);

            if (!$complaints) {
                ResponseHelper::sendResponse(404, ["message" => "No complaints found"]);
            }

            ResponseHelper::sendResponse(200, $complaints);
        } catch (PDOException $e) {
            ResponseHelper::sendResponse(500, ["error" => "Database error", "message" => $e->getMessage()]);
        }
    }

    public function getUserComplaints($params) {
        try {
            if (!isset($_GET['token'])) {
                ResponseHelper::sendResponse(400, ["message" => "Token is required"]);
            }
    
            // Decode the JWT token
            $decoded = AuthMiddleware::decodeToken($_GET['token']);
    
            $userId = $decoded['userId']; // Extract userId from token
    
            // Fetch complaints for that user
            $complaints = $this->complain->getUserComplaints($userId, 'DESC');
    
            if (!$complaints) {
                ResponseHelper::sendResponse(404, ["message" => "No complaints found"]);
            }
    
            ResponseHelper::sendResponse(200, $complaints);
        } catch (PDOException $e) {
            ResponseHelper::sendResponse(500, ["error" => "Database error", "message" => $e->getMessage()]);
        }
    }

    public function addComplain($data) {
        try {
            if (!isset($_GET['token'])) {
                ResponseHelper::sendResponse(400, ["message" => "Token is required"]);
            }
    
            // Decode the JWT token
            $decoded = AuthMiddleware::decodeToken($_GET['token']);
            $userId = $decoded['userId']; // Extract userId from token
    
            // Add the userId to the data array
            $data['userId'] = $userId;
    
            // Validate input
            if (empty($data['categoryId']) || empty($data['description'])) {
                ResponseHelper::sendResponse(400, ["message" => "Missing required fields"]);
            }
    
            // Insert complaint
            if ($this->complain->addComplain($data)) {
                ResponseHelper::sendResponse(201, ["message" => "Complaint added successfully"]);
            } else {
                ResponseHelper::sendResponse(500, ["error" => "Failed to add complaint"]);
            }
        } catch (PDOException $e) {
            ResponseHelper::sendResponse(500, ["error" => "Database error", "message" => $e->getMessage()]);
        }
    }

    public function getComplainHistory($params) {
        try {
            $token = $_GET['token'] ?? null;
            if (!$token) {
                ResponseHelper::sendResponse(401, ["message" => "Unauthorized: Missing token"]);
            }
    
            // Decode JWT to get userId
            $decoded = AuthMiddleware::decodeToken($token);
            $userId = $decoded['userId'] ?? null;
            if (!$userId) {
                ResponseHelper::sendResponse(401, ["message" => "Invalid token"]);
            }
    
            $complainId = $params['complainID'];
            if (!$complainId) {
                ResponseHelper::sendResponse(400, ["message" => "Complaint ID is required"]);
            }
    
            // Check if the user has submitted this complaint
            if (!$this->complain->userHasComplaint($userId, $complainId)) {
                ResponseHelper::sendResponse(403, ["message" => "You do not have access to this complaint"]);
            }
    
            // Fetch complaint history
            $history = $this->complain->getComplaintHistory($complainId);
    
            if (!$history) {
                ResponseHelper::sendResponse(404, ["message" => "No history found for this complaint"]);
            }
    
            ResponseHelper::sendResponse(200, $history);
    
        } catch (Exception $e) {
            ResponseHelper::sendResponse(500, ["error" => "Server error", "message" => $e->getMessage()]);
        }
    }
}

?>
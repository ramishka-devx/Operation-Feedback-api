<?php
require_once __DIR__ . '/../models/Complain.php';
require_once __DIR__ . '/../helpers/ResponseHelper.php';

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

    public function getUserComplaints($userId, $sortOrder = 'DESC') {
        try {
            $sortOrder = strtoupper($sortOrder);
            if (!in_array($sortOrder, ['ASC', 'DESC'])) {
                $sortOrder = 'DESC';
            }

            $complaints = $this->complain->getUserComplaints($userId, $sortOrder);

            if (!$complaints) {
                ResponseHelper::sendResponse(404, ["message" => "No complaints found for this user"]);
            }

            ResponseHelper::sendResponse(200, $complaints);
        } catch (PDOException $e) {
            ResponseHelper::sendResponse(500, ["error" => "Database error", "message" => $e->getMessage()]);
        }
    }

    public function addComplain($data) {
        try {
            if (empty($data['userId']) || empty($data['categoryId']) || empty($data['status'])) {
                ResponseHelper::sendResponse(400, ["message" => "Missing required fields"]);
            }

            if ($this->complain->addComplain($data)) {
                ResponseHelper::sendResponse(201, ["message" => "Complaint added successfully"]);
            } else {
                ResponseHelper::sendResponse(500, ["error" => "Failed to add complaint"]);
            }
        } catch (PDOException $e) {
            ResponseHelper::sendResponse(500, ["error" => "Database error", "message" => $e->getMessage()]);
        }
    }
}

?>
<?php
require_once __DIR__ . '/../models/Complain.php';
require_once __DIR__ . '/../helpers/ResponseHelper.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/checkPermission.php';
require_once __DIR__ . '/../helpers/jwt_helper.php';

class ComplainController
{
    private $db;
    private $conn;
    private $complain;

    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->complain = new Complain($this->conn);
    }

    public function getAllComplaints($params = [])
    {
        try {
            if (!isset($_GET['token'])) {
                ResponseHelper::sendResponse(400, ["message" => "Missing required fields"]);
            }

            //middleware to check if user has permission to fetch all complains
            if (!checkPermission($_GET['token'], "ViewAllComplains")) {
                ResponseHelper::sendResponse(403, ["message" => "Unauthorized"]);
                exit;
            }

            $limit = $_GET['limit'] ?? 10;
            $page  = $_GET['page'] ?? 1;
            $offset = ($page - 1) * $limit;

            $sortMethod = isset($_GET['sortMethod']) ? strtoupper($_GET['sortMethod']) : 'DESC';
            if (!in_array($sortMethod, ['ASC', 'DESC'])) {
                $sortMethod = 'DESC';
            }

            $orderBy = $_GET['orderBy'] ?? 'date';

            $complaints = $this->complain->getAllComplaints($limit, $offset,$orderBy, $sortMethod);

            if (!$complaints) {
                ResponseHelper::sendResponse(404, ["message" => "No complaints found"]);
            }

            ResponseHelper::sendResponse(200, $complaints);
        } catch (PDOException $e) {
            ResponseHelper::sendResponse(500, ["error" => "Database error", "message" => $e->getMessage()]);
        }
    }

    public function getUserComplaints($params)
    {
        try {
            if (!isset($_GET['token'])) {
                ResponseHelper::sendResponse(400, ["message" => "Token is required"]);
            }

            // Decode the JWT token
            $decoded = AuthMiddleware::decodeToken($_GET['token']);

            $userId = $decoded['userId']; // Extract userId from token

            
            $limit = $_GET['limit'] ?? 10;
            $page  = $_GET['page'] ?? 1;
            $offset = ($page - 1) * $limit;

            $sortMethod = isset($_GET['sortMethod']) ? strtoupper($_GET['sortMethod']) : 'DESC';
            if (!in_array($sortMethod, ['ASC', 'DESC'])) {
                $sortMethod = 'DESC';
            }

            $orderBy = $_GET['orderBy'] ?? 'date';

            // Fetch complaints for that user
            $complaints = $this->complain->getUserComplaints($userId, $limit, $offset, $orderBy, $sortMethod);

            if (!$complaints) {
                ResponseHelper::sendResponse(404, ["message" => "No complaints found"]);
            }

            ResponseHelper::sendResponse(200, $complaints);
        } catch (PDOException $e) {
            ResponseHelper::sendResponse(500, ["error" => "Database error", "message" => $e->getMessage()]);
        }
    }

    public function addComplain($data)
    {
        try {
            if (!isset($_GET['token'])) {
                ResponseHelper::sendResponse(400, ["message" => "Token is required"]);
            }

            // Decode the JWT token
            $decoded = AuthMiddleware::decodeToken($_GET['token']);
            $userId = $decoded['userId']; 

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

    public function getComplainHistory($params)
    {
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

    public function getInchargeComplaints($params)
    {
        try {
            if (!isset($_GET['token'])) {
                ResponseHelper::sendResponse(400, ["message" => "Token is required"]);
            }

            //middleware to check if user has permission to view incharge complains
            if (!checkPermission($_GET['token'], "viewInchargedComplains")) {
                ResponseHelper::sendResponse(403, ["message" => "Unauthorized"]);
                exit;
            }

            // Decode the JWT token
            $decoded = AuthMiddleware::decodeToken($_GET['token']);

            $userId = $decoded['userId']; // Extract userId from token

            //
            $limit = $_GET['limit'] ?? 10;
            $page  = $_GET['page'] ?? 1;
            $offset = ($page - 1) * $limit;

            $sortMethod = isset($_GET['sortMethod']) ? strtoupper($_GET['sortMethod']) : 'DESC';
            if (!in_array($sortMethod, ['ASC', 'DESC'])) {
                $sortMethod = 'DESC';
            }

            $orderBy = $_GET['orderBy'] ?? 'date';
            
            // Fetch complaints for that user
            $complaints = $this->complain->fetchComplaintsForIncharge($userId,$limit,$offset,$orderBy,$sortMethod);

            if (!$complaints) {
                ResponseHelper::sendResponse(404, ["message" => "No complaints found"]);
            }

            ResponseHelper::sendResponse(200, $complaints);
        } catch (PDOException $e) {
            ResponseHelper::sendResponse(500, ["error" => "Database error", "message" => $e->getMessage()]);
        }
    }

    public function updateComplaintStatus()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $token = $_GET['token'] ?? null;
        $complainId = $data['complainId'] ?? null;
        $newStatus = $data['status'] ?? null;

        if (!$token || !$complainId || !$newStatus) {
            echo json_encode(["error" => "Missing required parameters"]);
            http_response_code(400);
            return;
        }

        // Decode JWT token
        $decoded = AuthMiddleware::decodeToken($token);
        if (!$decoded) {
            echo json_encode(["error" => "Invalid token"]);
            http_response_code(401);
            return;
        }

        $userId = $decoded['userId'];

        // Verify the user is in charge of the complaint's category
        if (!$this->complain->isUserInchargeOfComplaint($userId, $complainId)) {
            echo json_encode(["error" => "Unauthorized"]);
            http_response_code(403);
            return;
        }

        // Update the complaint status
        $updated = $this->complain->updateStatus($userId, $complainId, $newStatus);
        if ($updated) {
            echo json_encode(["message" => "Complaint status updated successfully"]);
        } else {
            echo json_encode(["error" => "Failed to update status"]);
            http_response_code(500);
        }
    }

    public function updateComplainPriority()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $token = $_GET['token'] ?? null;
        $complainId = $data['complainId'] ?? null;
        $newPriority = $data['priority'] ?? null;

        if (!$token || !$complainId || !$newPriority) {
            echo json_encode(["error" => "Missing required parameters"]);
            http_response_code(400);
            return;
        }

        // Decode JWT token
        $decoded = AuthMiddleware::decodeToken($token);
        if (!$decoded) {
            echo json_encode(["error" => "Invalid token"]);
            http_response_code(401);
            return;
        }

        $userId = $decoded['userId'];

        // Verify the user is in charge of the complaint's category
        if (!$this->complain->isUserInchargeOfComplaint($userId, $complainId)) {
            echo json_encode(["error" => "Unauthorized"]);
            http_response_code(403);
            return;
        }

        // Update the complaint status
        $updated = $this->complain->updatePriority($complainId, $newPriority);
        if ($updated) {
            echo json_encode(["message" => "Complaint status updated successfully"]);
        } else {
            echo json_encode(["error" => "Failed to update status"]);
            http_response_code(500);
        }
    }
}

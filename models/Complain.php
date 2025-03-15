<?php
class Complain {
    private $conn;
    private $table = "complains";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fetch all complaints with sorting
    public function getAllComplaints($sortOrder = 'DESC') {
        $query = "SELECT * FROM {$this->table} ORDER BY createdAt $sortOrder";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch complaints of a specific user
    public function getUserComplaints($userId, $sortOrder = 'DESC') {
        $query = "SELECT * FROM {$this->table} WHERE userId = :userId ORDER BY createdAt $sortOrder";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add a complaint
    public function addComplain($data) {
        $query = "INSERT INTO {$this->table} (userId, categoryId, description) VALUES (:userId, :categoryId, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $data['userId'], PDO::PARAM_INT);
        $stmt->bindParam(':categoryId', $data['categoryId'], PDO::PARAM_INT);
        $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function userHasComplaint($userId, $complainId) {
        $query = "SELECT COUNT(*) FROM complains WHERE complainId = :complainId AND userId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':complainId', $complainId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
    
    public function getComplaintHistory($complainId) {
        $query = "SELECT * FROM activities WHERE complainId = :complainId ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':complainId', $complainId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}

?>
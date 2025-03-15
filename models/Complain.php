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
        $query = "INSERT INTO {$this->table} (userId, categoryId, status, createdAt) VALUES (:userId, :categoryId, :status, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $data['userId'], PDO::PARAM_INT);
        $stmt->bindParam(':categoryId', $data['categoryId'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
        return $stmt->execute();
    }
}

?>
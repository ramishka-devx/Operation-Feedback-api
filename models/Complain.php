<?php
class Complain {
    private $conn;
    private $table = "complains";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fetch all complaints with sorting
    public function getAllComplaints($limit, $offset, $orderBy = 'complainId', $sortMethod = 'DESC')
    {
        $validOrderColumns = ['priority', 'complainId'];
        if (!in_array($orderBy, $validOrderColumns)) {
            $orderBy = 'complainId';
        }

        $sortMethod = strtoupper($sortMethod);
        if (!in_array($sortMethod, ['ASC', 'DESC'])) {
            $sortMethod = 'DESC';
        }

        $query = "SELECT * FROM {$this->table} ORDER BY $orderBy $sortMethod LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $complainData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch total complaint count
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        return ['complainData' => $complainData, 'count' => $count];
    }


    // Fetch complaints of a specific user
    public function getUserComplaints($userId, $limit = 10, $offset = 0, $orderBy = 'complainId', $sortMethod = 'DESC')
    {
        $validOrderColumns = ['priority', 'complainId'];
        if (!in_array($orderBy, $validOrderColumns)) {
            $orderBy = 'complainId';
        }

        $sortMethod = strtoupper($sortMethod);
        if (!in_array($sortMethod, ['ASC', 'DESC'])) {
            $sortMethod = 'DESC';
        }

        $query = "SELECT * FROM {$this->table} WHERE userId = :userId ORDER BY {$orderBy} {$sortMethod} LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $complainData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT COUNT(*) FROM complains WHERE userId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        return ['complainData' => $complainData, 'count' => $count];
    }

    // Add a complaint
    public function addComplain($data)
    {
        $query = "INSERT INTO {$this->table} (userId, categoryId, description) VALUES (:userId, :categoryId, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $data['userId'], PDO::PARAM_INT);
        $stmt->bindParam(':categoryId', $data['categoryId'], PDO::PARAM_INT);
        $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function userHasComplaint($userId, $complainId)
    {
        $query = "SELECT COUNT(*) FROM complains WHERE complainId = :complainId AND userId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':complainId', $complainId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
    
    public function getComplaintHistory($complainId)
    {
        $query = "SELECT * FROM activities WHERE complainId = :complainId ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':complainId', $complainId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchComplaintsForIncharge($userId, $limit = 10, $offset = 0, $orderBy = 'complainId', $sortMethod = 'DESC'){
        $validOrderColumns = ['priority', 'complainId'];
        if (!in_array($orderBy, $validOrderColumns)) {
            $orderBy = 'complainId';
        }

        $sortMethod = strtoupper($sortMethod);
        if (!in_array($sortMethod, ['ASC', 'DESC'])) {
            $sortMethod = 'DESC';
        }

        $query = "SELECT c.*, ct.title FROM complains c
                        JOIN categoryIncharge ci ON c.categoryId = ci.categoryId
                        JOIN users u ON ci.rollId = u.rollId
                        JOIN categories ct ON c.categoryId = ct.categoryId
                    WHERE u.userId = :userId ORDER BY c.{$orderBy} {$sortMethod} LIMIT :limit OFFSET :offset ;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $complainData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch total complaint count
        $query = "SELECT COUNT(*) FROM complains c
                        JOIN categoryIncharge ci ON c.categoryId = ci.categoryId
                        JOIN users u ON ci.rollId = u.rollId
                        JOIN categories ct ON c.categoryId = ct.categoryId
                    WHERE u.userId = :userId ;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        return ['complainData' => $complainData, 'count' => $count];
    }

    public function isUserInchargeOfComplaint($userId, $complainId)
    {
        $query = "SELECT 1 FROM complains c
                        JOIN categoryIncharge ci ON c.categoryId = ci.categoryId
                        JOIN users u ON u.rollId = ci.rollId
                    WHERE u.userId = :userId AND c.complainId = :complainId ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':complainId', $complainId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() ? true : false;
    }
    
    public function updateStatus($userId, $complainId,$newStatus)
    {
        $query = "INSERT INTO activities (userId, complainId, description) VALUES (:userId, :complainId, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':complainId', $complainId, PDO::PARAM_INT);
        $stmt->bindParam(':description', $newStatus, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function updatePriority($complainId,$newPriority)
    {
        $query = "UPDATE complains SET priority = :priority WHERE complainId = :complainId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':priority', $newPriority, PDO::PARAM_INT);
        $stmt->bindParam(':complainId', $complainId, PDO::PARAM_INT);

        return $stmt->execute();
    }
    
}

?>
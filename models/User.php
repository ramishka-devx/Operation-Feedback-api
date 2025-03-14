<?php

class User {
    private $conn;
    private $table = "users";

    public $id;
    public $fullName;
    public $regNo;
    public $batch;
    public $facultyId;
    public $rollId;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($regNo) {
        $query = "SELECT * FROM " . $this->table . " WHERE regNo = :regNo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':regNo', $regNo);
        $stmt -> execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}// class User
?>
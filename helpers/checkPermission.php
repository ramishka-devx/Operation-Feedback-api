<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/jwt_helper.php';

function checkPermission($token, $requiredPermission) {
    $db = new Database();
    $conn = $db->connect();

    // Decode JWT token
    $decodedToken = decodeJWT($token);
    if (!$decodedToken || !isset($decodedToken['userId'])) {
        return false; // Invalid token
    }
    
    $userId = $decodedToken['userId'];

    // Get user's role
    $query = "SELECT rolls.rollId FROM users 
              JOIN rolls ON users.rollId = rolls.rollId
              WHERE users.userId = :userId";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$role) return false;

    // Check if the role has the required permission
    $query = "SELECT rp.permissionId FROM rolepermissions rp
              JOIN permissions p ON rp.permissionId = p.permissionId
              WHERE rp.rollId = :rollId AND p.title = :permission";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':rollId', $role['rollId']);
    $stmt->bindParam(':permission', $requiredPermission);
    $stmt->execute();
    $hasPermission = $stmt->fetch(PDO::FETCH_ASSOC);

    return $hasPermission ? true : false;
}
?>

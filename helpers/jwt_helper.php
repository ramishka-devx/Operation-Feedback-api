<?php
require_once __DIR__.'/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "facultyOFEngineeringUORisTheBESTOFLK24UORPCC"; // TODO : move this to env file - Ramishka

function createJWT($userId, $regNo) {
    global $key;
    $payload = [
        "iss" => "localhost", // TODO :: make sure to change this before deploy -- Ramishka
        "iat" => time(),
        "exp" => time() + 3600,
        "userId" => $userId,
        "regNo" => $regNo
    ];
    return JWT::encode($payload, $key, 'HS256');
}

function decodeJWT($token) {
    try {
        global $key;
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return (array) $decoded;
    } catch (Exception $e) {
        return false; // Invalid token
    }
}

?>

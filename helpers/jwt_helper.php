<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;

$key = "facultyOFEngineeringUORisTheBESTOFLK24UORPCC";

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
?>

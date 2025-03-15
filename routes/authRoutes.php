<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . "/../controllers/AuthController.php";
use FastRoute\RouteCollector;

return function (RouteCollector $r) {
    $r->addRoute('POST', '/login', ['AuthController', 'login']);
    $r->addRoute('GET', '/', function () {
        echo json_encode(["message" => "hello world"]);
    });
};
?>

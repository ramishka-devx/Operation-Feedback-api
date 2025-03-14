<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$authController = new AuthController();

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

header("Content-Type: application/json");

// FastRoute Dispatcher
$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('POST', '/login', ['AuthController', 'login']);
    $r->addRoute('GET', '/', function () {
        echo json_encode(["message" => "hello world"]);
    });

    // $r->addRoute('GET','/test',function(){
    //     echo json_encode(["message"=>"testing routes"]);
    // });
});

// Get HTTP method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Dispatch the route
$routeInfo = $dispatcher->dispatch($httpMethod, $requestUri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        if (is_array($handler)) {
            $controller = new $handler[0]();
            call_user_func([$controller, $handler[1]], json_decode(file_get_contents("php://input"), true));
        } else {
            call_user_func($handler);
        }
        break;
    
    case FastRoute\Dispatcher::NOT_FOUND:
        echo json_encode(["message" => "Route not found"]);
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        echo json_encode(["message" => "Method not allowed"]);
        break;
}
?>

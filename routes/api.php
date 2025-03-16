<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ComplainController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

header("Content-Type: application/json");


// FastRoute Dispatcher
$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    // Auth Routes
    $r->addRoute('POST', '/auth/login', ['AuthController', 'login']);

    // Complaint Routes
    $r->addRoute('GET', '/admin/complaints/all', ['ComplainController', 'getAllComplaints']);
    $r->addRoute('GET', '/admin/complaints/my', ['ComplainController', 'getInchargeComplaints']);

    //user Routes
    $r->addRoute('GET', '/user/complaints', ['ComplainController', 'getUserComplaints']);
    $r->addRoute('POST', '/user/complaints/new', ['ComplainController', 'addComplain']);
    $r->addRoute('GET', '/user/complaints/{complainID}', ['ComplainController', 'getComplainHistory']);
    $r->addRoute('GET', '/categories', ['CategoryController', 'getAllCategories']);

    
    // Default route
    $r->addRoute('GET', '/', function () {
        echo json_encode(["message" => "Welcome to the API"]);
    });
});

// Get HTTP method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Get request body for POST requests
$requestBody = ($httpMethod === 'POST' || $httpMethod === 'PUT') ? json_decode(file_get_contents("php://input"), true) : [];

// Dispatch the route
$routeInfo = $dispatcher->dispatch($httpMethod, $requestUri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2] ?? []; // Ensure an array is passed

        if (is_array($handler)) {
            $controller = new $handler[0]();
            if ($httpMethod === 'POST' || $httpMethod === 'PUT') {
                call_user_func([$controller, $handler[1]], $requestBody); // Pass request body
            } else {
                call_user_func([$controller, $handler[1]], $vars);
            }
        } else {
            call_user_func($handler);
        }
        break;

    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(["message" => "Route not found"]);
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

?>
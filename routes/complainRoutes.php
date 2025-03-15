<?php
use FastRoute\RouteCollector;

return function (RouteCollector $r) {
    $r->addRoute('GET', '/complaints', ['ComplainController', 'getAllComplaints']);
    $r->addRoute('GET', '/user-complaints/{id}', ['ComplainController', 'getUserComplaints']);
    $r->addRoute('POST', '/add-complaint', ['ComplainController', 'addComplain']);
};
?>

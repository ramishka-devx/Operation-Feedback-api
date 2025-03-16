<!-- // this file is only for testing -->
<!-- call the wanted functions here and you can access the from the server -->

<?php
require_once __DIR__.'/./models/Complain.php';  // Include the Complain class
require_once __DIR__.'/./config/database.php'; // Make sure this file contains the DB connection

// Create database connection
$database = new Database();
$db = $database->connect();

// Initialize the Complain class
$complain = new Complain($db);

// Test userId (replace with an existing user ID from your database)
$userId = 5; 

// Call fetchComplaintsForIncharge function
$results = $complain->fetchComplaintsForIncharge($userId, "DESC");

// Display the output
echo "<pre>";
print_r($results);
echo "</pre>";
?>

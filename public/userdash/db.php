<?php
// session_start();

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

// Temporary DB connection for testing if db.php is missing or not working
$host = "localhost";
$db   = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = intval($_SESSION['user_id']);

// Then the rest of your code...
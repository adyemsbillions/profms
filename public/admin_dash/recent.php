<?php
// Start session
session_start();

// Ensure the user is logged in (admin check)
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// DB connection details
$host = "localhost";
$db = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch the last 4 users sorted by registration date
$sql = "SELECT first_name, last_name, email, registration_date, is_approved FROM users ORDER BY registration_date DESC LIMIT 4";
$result = $conn->query($sql);

// Check if the query returns results
if ($result->num_rows > 0) {
    $recent_users = [];
    while ($row = $result->fetch_assoc()) {
        $recent_users[] = $row;
    }
} else {
    $recent_users = [];
}

// Close the database connection
$conn->close();

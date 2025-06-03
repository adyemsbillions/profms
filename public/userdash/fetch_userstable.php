<?php
session_start();  // Start the session at the very top

require('db.php'); // Include your DB connection once

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = intval($_SESSION['user_id']);

// Prepare and execute query to fetch user data
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, country, institution, position, research_interests, wants_newsletter, is_reviewer, is_approved, registration_date, last_login FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Store user data in session for echo script or elsewhere as needed
$_SESSION['user_data'] = $user;

// Optionally output JSON here or just end
// echo json_encode($user);
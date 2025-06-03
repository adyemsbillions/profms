<?php
session_start();

// DB connection
$host = "localhost";
$db   = "fms";     // Your DB name
$user = "root";    // Your DB username
$pass = "";        // Your DB password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(['error' => "DB Connection failed: " . $conn->connect_error]));
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['error' => 'Invalid request method']));
}

// Get POST data and sanitize
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$organization = trim($_POST['organization'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Basic validation
if (strlen($name) < 2) {
    die(json_encode(['error' => 'Please enter your full name.']));
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['error' => 'Invalid email address.']));
}
if (strlen($organization) < 2) {
    die(json_encode(['error' => 'Please enter your organization name.']));
}
if (strlen($password) < 8) {
    die(json_encode(['error' => 'Password must be at least 8 characters.']));
}
if ($password !== $confirmPassword) {
    die(json_encode(['error' => 'Passwords do not match.']));
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die(json_encode(['error' => 'Email is already registered.']));
}
$stmt->close();

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert new admin user
$stmt = $conn->prepare("INSERT INTO admin (name, email, organization, password_hash) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $organization, $password_hash);
if ($stmt->execute()) {
    echo json_encode(['success' => 'Account created successfully!']);
} else {
    echo json_encode(['error' => 'Failed to create account.']);
}
$stmt->close();
$conn->close();

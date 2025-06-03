<?php
session_start();

// DB connection
$host = "localhost";
$db   = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => "DB Connection failed: " . $conn->connect_error]);
    error_log("DB Connection failed: " . $conn->connect_error);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Get POST data and sanitize
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address.']);
    exit;
}
if (strlen($password) < 6) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters.']);
    exit;
}

// Check if user exists and verify password
try {
    // Dynamically check if admin_access column exists
    $columns = "id, password_hash";
    $result = $conn->query("SHOW COLUMNS FROM admin LIKE 'admin_access'");
    if ($result->num_rows > 0) {
        $columns .= ", admin_access";
    }

    $stmt = $conn->prepare("SELECT $columns FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password.']);
        $stmt->close();
        exit;
    }

    $user = $result->fetch_assoc();
    if (!password_verify($password, $user['password_hash'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password.']);
        $stmt->close();
        exit;
    }

    // Set session variables
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_access'] = isset($user['admin_access']) ? $user['admin_access'] : 0;

    header('Content-Type: application/json');
    echo json_encode(['success' => 'Login successful! Redirecting to admin dashboard...']);
    $stmt->close();
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred during login.']);
}
$conn->close();

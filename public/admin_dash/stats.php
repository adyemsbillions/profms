<?php

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Set error reporting (disable display in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Initialize counts
$total_users = 0;
$total_articles = 0;
$total_inquiries = 0;
$total_rejected = 0;
$total_approved = 0;
$total_amount = 0;  // Initialize total amount

// DB connection
$host = "localhost";
$db   = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("DB Connection failed: " . $conn->connect_error);
    return; // Exit silently, let including file handle errors
}

try {
    // Fetch total users
    $stmt_users = $conn->prepare("SELECT COUNT(id) AS total_users FROM admin");
    if ($stmt_users) {
        $stmt_users->execute();
        $result_users = $stmt_users->get_result();
        $total_users = $result_users->fetch_assoc()['total_users'] ?? 0;
        $stmt_users->close();
    } else {
        error_log("Prepare failed for users: " . $conn->error);
    }

    // Fetch total articles
    $stmt_articles = $conn->prepare("SELECT COUNT(id) AS total_articles FROM articles");
    if ($stmt_articles) {
        $stmt_articles->execute();
        $result_articles = $stmt_articles->get_result();
        $total_articles = $result_articles->fetch_assoc()['total_articles'] ?? 0;
        $stmt_articles->close();
    } else {
        error_log("Prepare failed for articles: " . $conn->error);
    }

    // Fetch total support inquiries
    $stmt_inquiries = $conn->prepare("SELECT COUNT(id) AS total_inquiries FROM support_inquiries");
    if ($stmt_inquiries) {
        $stmt_inquiries->execute();
        $result_inquiries = $stmt_inquiries->get_result();
        $total_inquiries = $result_inquiries->fetch_assoc()['total_inquiries'] ?? 0;
        $stmt_inquiries->close();
    } else {
        error_log("Prepare failed for inquiries: " . $conn->error);
    }

    // Fetch total rejected articles
    $stmt_rejected = $conn->prepare("SELECT COUNT(id) AS total_rejected FROM articles WHERE status = 'rejected'");
    if ($stmt_rejected) {
        $stmt_rejected->execute();
        $result_rejected = $stmt_rejected->get_result();
        $total_rejected = $result_rejected->fetch_assoc()['total_rejected'] ?? 0;
        $stmt_rejected->close();
    } else {
        error_log("Prepare failed for rejected articles: " . $conn->error);
    }

    // Fetch total approved articles
    $stmt_approved = $conn->prepare("SELECT COUNT(id) AS total_approved FROM articles WHERE status = 'approved'");
    if ($stmt_approved) {
        $stmt_approved->execute();
        $result_approved = $stmt_approved->get_result();
        $total_approved = $result_approved->fetch_assoc()['total_approved'] ?? 0;
        $stmt_approved->close();
    } else {
        error_log("Prepare failed for approved articles: " . $conn->error);
    }

    // Fetch total amount paid (only successful payments)
    $stmt_amount = $conn->prepare("SELECT SUM(amount) AS total_amount FROM payments WHERE status = 'success'");
    if ($stmt_amount) {
        $stmt_amount->execute();
        $result_amount = $stmt_amount->get_result();
        $total_amount = $result_amount->fetch_assoc()['total_amount'] ?? 0;
        $stmt_amount->close();
    } else {
        error_log("Prepare failed for total amount: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Stats error: " . $e->getMessage());
}

$conn->close();

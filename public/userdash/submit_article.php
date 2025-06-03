<?php
session_start();
require('db.php'); // Adjust path if needed

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Check form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method");
}

// Validate required fields
$title = trim($_POST['title'] ?? '');
$abstract = trim($_POST['abstract'] ?? '');
$journal = $_POST['journal'] ?? '';
$keywords = trim($_POST['keywords'] ?? '');
$author_names = trim($_POST['author_names'] ?? '');  // you might want to handle authors differently
$action = $_POST['action'] ?? 'draft';  // submit or draft

$errors = [];
if ($title === '') $errors[] = "Title is required";
if ($abstract === '') $errors[] = "Abstract is required";
if ($journal === '') $errors[] = "Journal selection is required";
if ($author_names === '') $errors[] = "Author names are required";

// File upload check
if (!isset($_FILES['manuscript']) || $_FILES['manuscript']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = "Manuscript file is required";
}

if ($errors) {
    // Simple error display, improve as needed
    foreach ($errors as $error) {
        echo "<p style='color:red;'>$error</p>";
    }
    echo '<p><a href="javascript:history.back()">Go Back</a></p>';
    exit;
}

// Handle file upload
$allowed_ext = ['pdf', 'doc', 'docx'];
$file = $_FILES['manuscript'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed_ext)) {
    die("Invalid file type. Only PDF, DOC, DOCX allowed.");
}

if ($file['size'] > 50 * 1024 * 1024) {  // 10MB limit
    die("File size exceeds 50MB.");
}

// Create uploads directory if not exists
$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique file name to avoid collisions
$new_filename = uniqid('manuscript_', true) . '.' . $ext;
$destination = $upload_dir . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    die("Failed to upload file.");
}

// Prepare data for DB insertion
$now = date('Y-m-d H:i:s');
$status = $action === 'submit' ? 'submitted' : 'draft';
$doi = null;  // assuming doi is generated later
$published_date = null;  // not published yet

// Insert article
$stmt = $conn->prepare("INSERT INTO articles (title, abstract, keywords, submission_date, last_update, status, doi, published_date, submitted_by, file_path, journal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$file_path_db = 'uploads/' . $new_filename;  // relative path for storing in DB

$stmt->bind_param(
    "ssssssssiss",
    $title,
    $abstract,
    $keywords,
    $now,
    $now,
    $status,
    $doi,
    $published_date,
    $user_id,
    $file_path_db,
    $journal
);

if ($stmt->execute()) {
    echo "<p>Article " . ($status === 'submitted' ? "submitted" : "saved as draft") . " successfully.</p>";
    echo '<p><a href="dashboard.php">Go to Dashboard</a></p>';
} else {
    echo "<p>Failed to save article: " . $stmt->error . "</p>";
    echo '<p><a href="javascript:history.back()">Go Back</a></p>';
}

$stmt->close();
$conn->close();

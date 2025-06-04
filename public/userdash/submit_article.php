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
$author_names = trim($_POST['author_names'] ?? '');
$action = $_POST['action'] ?? 'draft';

$errors = [];
if ($title === '') $errors[] = "Title is required";
if ($abstract === '') $errors[] = "Abstract is required";
if ($journal === '') $errors[] = "Journal selection is required";
if ($author_names === '') $errors[] = "Author names are required";

// File upload check for manuscript
if (!isset($_FILES['manuscript']) || $_FILES['manuscript']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = "Manuscript file is required";
}

// File upload check for publication image (optional)
$image_path_db = null;
if (isset($_FILES['publication_image']) && $_FILES['publication_image']['error'] === UPLOAD_ERR_OK) {
    $image = $_FILES['publication_image'];
    $allowed_image_ext = ['jpg', 'jpeg', 'png'];
    $image_ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    if (!in_array($image_ext, $allowed_image_ext)) {
        $errors[] = "Invalid image file type. Only JPG, JPEG, PNG allowed.";
    }
    if ($image['size'] > 5 * 1024 * 1024) { // 5MB limit
        $errors[] = "Image file size exceeds 5MB.";
    }
}

if ($errors) {
    // Simple error display, improve as needed
    foreach ($errors as $error) {
        echo "<p style='color:red;'>$error</p>";
    }
    echo '<p><a href="javascript:history.back()">Go Back</a></p>';
    exit;
}

// Handle manuscript file upload
$allowed_ext = ['pdf', 'doc', 'docx'];
$file = $_FILES['manuscript'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed_ext)) {
    die("Invalid file type. Only PDF, DOC, DOCX allowed.");
}

if ($file['size'] > 50 * 1024 * 1024) { // 50MB limit
    die("File size exceeds 50MB.");
}

// Create uploads directory if not exists
$upload_dir = __DIR__ . '/../uploads/';
$image_upload_dir = __DIR__ . '/../uploads/images/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if (!is_dir($image_upload_dir)) {
    mkdir($image_upload_dir, 0755, true);
}

// Generate unique file name for manuscript
$new_filename = uniqid('manuscript_', true) . '.' . $ext;
$destination = $upload_dir . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    die("Failed to upload manuscript file.");
}

// Handle publication image upload
if (isset($_FILES['publication_image']) && $_FILES['publication_image']['error'] === UPLOAD_ERR_OK) {
    $image = $_FILES['publication_image'];
    $image_ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    $new_image_filename = uniqid('image_', true) . '.' . $image_ext;
    $image_destination = $image_upload_dir . $new_image_filename;

    if (!move_uploaded_file($image['tmp_name'], $image_destination)) {
        die("Failed to upload image file.");
    }
    $image_path_db = 'uploads/images/' . $new_image_filename;
}

// Prepare data for DB insertion
$now = date('Y-m-d H:i:s');
$status = $action === 'submit' ? 'submitted' : 'draft';
$doi = null; // Assuming doi is generated later
$published_date = null; // Not published yet
$file_path_db = 'Uploads/' . $new_filename;

// Insert article
$stmt = $conn->prepare("INSERT INTO articles (title, abstract, keywords, submission_date, last_update, status, doi, published_date, submitted_by, file_path, image_path, journal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "ssssssssisss",
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
    $image_path_db,
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
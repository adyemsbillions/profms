<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "localhost";
$db   = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("DB Connection failed: " . $conn->connect_error);
    die("Database connection failed.");
}

// Create and verify Uploads directories
$uploads_dir = __DIR__ . '/Uploads';
$images_dir = $uploads_dir . '/images';

// Ensure Uploads directory exists and is writable
if (!is_dir($uploads_dir)) {
    if (!mkdir($uploads_dir, 0755, true)) {
        error_log("Failed to create directory: $uploads_dir");
        die("Failed to create uploads directory.");
    }
}
if (!is_writable($uploads_dir)) {
    if (!chmod($uploads_dir, 0755)) {
        error_log("Failed to set write permissions for directory: $uploads_dir");
        die("Uploads directory is not writable.");
    }
}

// Ensure images directory exists and is writable
if (!is_dir($images_dir)) {
    if (!mkdir($images_dir, 0755, true)) {
        error_log("Failed to create directory: $images_dir");
        die("Failed to create images directory.");
    }
}
if (!is_writable($images_dir)) {
    if (!chmod($images_dir, 0755)) {
        error_log("Failed to set write permissions for directory: $images_dir");
        die("Images directory is not writable.");
    }
}

// Initialize variables
$errors = [];
$success = "";
$title = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize text input
    $title = trim($_POST['title'] ?? '');

    // Validate text input
    if (empty($title)) {
        $errors[] = "Title is required.";
    }

    // Log $_FILES for debugging
    error_log("FILES array: " . print_r($_FILES, true));

    // Validate and handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image = $_FILES['image'];
        $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_image_size = 50 * 1024 * 1024; // 50MB

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Image upload error: " . get_upload_error_message($_FILES['image']['error']);
        } elseif (!in_array($image['type'], $allowed_image_types)) {
            $errors[] = "Image must be JPEG, PNG, or GIF.";
        } elseif ($image['size'] > $max_image_size) {
            $errors[] = "Image size exceeds 50MB.";
        } elseif (!is_uploaded_file($image['tmp_name'])) {
            $errors[] = "Invalid image file upload.";
        } else {
            $image_ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $image_filename = 'publication_image_' . uniqid() . '.' . strtolower($image_ext);
            $image_target = $images_dir . '/' . $image_filename;

            if (move_uploaded_file($image['tmp_name'], $image_target)) {
                $image_path = 'Uploads/images/' . $image_filename;
                error_log("Image uploaded successfully: $image_path");
            } else {
                $errors[] = "Failed to move image file to $image_target.";
                error_log("Failed to move image file to $image_target. Check permissions or disk space.");
            }
        }
    }

    // Validate and handle file upload
    $file_path = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['file'];
        $allowed_file_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_file_size = 50 * 1024 * 1024; // 50MB

        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Document upload error: " . get_upload_error_message($_FILES['file']['error']);
        } elseif (!in_array($file['type'], $allowed_file_types)) {
            $errors[] = "File must be PDF or DOCX.";
        } elseif ($file['size'] > $max_file_size) {
            $errors[] = "File size exceeds 50MB.";
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = "Invalid document file upload.";
        } else {
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_filename = 'publication_file_' . uniqid() . '.' . strtolower($file_ext);
            $file_target = $uploads_dir . '/' . $file_filename;

            if (move_uploaded_file($file['tmp_name'], $file_target)) {
                $file_path = 'Uploads/' . $file_filename;
                error_log("File uploaded successfully: $file_path");
            } else {
                $errors[] = "Failed to move document file to $file_target.";
                error_log("Failed to move document file to $file_target. Check permissions or disk space.");
            }
        }
    } else {
        $errors[] = "Document file is required.";
    }

    // Insert into database if no errors
    if (empty($errors) && $file_path) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO publications (title, image_path, file_path)
                VALUES (?, ?, ?)
            ");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("sss", $title, $image_path, $file_path);

            if ($stmt->execute()) {
                $success = "Publication uploaded successfully.";
                $title = "";
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Database error: " . htmlspecialchars($e->getMessage());
            error_log("Publication upload error: " . $e->getMessage());
        }
    }
}

// Function to get detailed upload error messages
function get_upload_error_message($error_code)
{
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive in the form.";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded.";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk.";
        case UPLOAD_ERR_EXTENSION:
            return "A PHP extension stopped the file upload.";
        default:
            return "Unknown upload error.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Publication - FMS Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    :root {
        --primary-color: #1e3a8a;
        --secondary-color: #059669;
        --danger-color: #dc2626;
        --bg-light: #f8fafc;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--bg-light);
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .upload-form {
        padding: 40px 0;
    }

    .form-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }

    .form-card h2 {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: #1a2a44;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-weight: 500;
        color: #1a2a44;
        margin-bottom: 5px;
        display: block;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 1rem;
    }

    .btn-submit {
        background: var(--primary-color);
        color: #fff;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
    }

    .alert {
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .form-card h2 {
            font-size: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <section class="upload-form">
        <div class="container">
            <div class="form-card">
                <h2>Upload Publication</h2>
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <form action="publications_upload.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title (Required)</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="image">Publication Image (Optional, JPEG/PNG/GIF, Max 50MB)</label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                    </div>
                    <div class="form-group">
                        <label for="file">Document File (Required, PDF/DOCX, Max 50MB)</label>
                        <input type="file" id="file" name="file" accept=".pdf,.doc,.docx" required>
                    </div>
                    <button type="submit" class="btn btn-submit"><i class="fas fa-upload"></i> Upload
                        Publication</button>
                </form>
            </div>
        </div>
    </section>
</body>

</html>
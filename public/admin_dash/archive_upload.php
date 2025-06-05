<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Restrict access to admins
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied: Admins only.";
    exit;
}

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

// Create Uploads directories if they don't exist
$uploads_dir = __DIR__ . '/Uploads';
$images_dir = $uploads_dir . '/images';
if (!is_dir($uploads_dir)) {
    if (!mkdir($uploads_dir, 0755, true)) {
        error_log("Failed to create directory: $uploads_dir");
        die("Failed to create uploads directory.");
    }
}
if (!is_dir($images_dir)) {
    if (!mkdir($images_dir, 0755, true)) {
        error_log("Failed to create directory: $images_dir");
        die("Failed to create images directory.");
    }
}

// Initialize variables
$errors = [];
$success = "";
$abstract = $details = $published_date = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize text inputs
    $abstract = trim($_POST['abstract'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $published_date = $_POST['published_date'] ?? '';
    $uploaded_by = $_SESSION['admin_id'];

    // Validate text inputs
    if (empty($abstract)) {
        $errors[] = "Abstract is required.";
    }
    if (!empty($published_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $published_date)) {
        $errors[] = "Invalid published date format.";
    }

    // Validate and handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image = $_FILES['image'];
        $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_image_size = 50 * 1024 * 1024; // 50MB

        if (!in_array($image['type'], $allowed_image_types)) {
            $errors[] = "Image must be JPEG, PNG, or GIF.";
        } elseif ($image['size'] > $max_image_size) {
            $errors[] = "Image size exceeds 50MB.";
        } else {
            $image_ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $image_filename = 'archive_image_' . uniqid() . '.' . strtolower($image_ext);
            $image_target = $images_dir . '/' . $image_filename;

            if (move_uploaded_file($image['tmp_name'], $image_target)) {
                $image_path = 'Uploads/images/' . $image_filename;
                error_log("Image uploaded: $image_path");
            } else {
                $errors[] = "Failed to upload image.";
                error_log("Image upload failed: " . $image['error']);
            }
        }
    }

    // Validate and handle file upload
    $file_path = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $allowed_file_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_file_size = 50 * 1024 * 1024; // 50MB

        if (!in_array($file['type'], $allowed_file_types)) {
            $errors[] = "File must be PDF or DOCX.";
        } elseif ($file['size'] > $max_file_size) {
            $errors[] = "File size exceeds 50MB.";
        } else {
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_filename = 'archive_file_' . uniqid() . '.' . strtolower($file_ext);
            $file_target = $uploads_dir . '/' . $file_filename;

            if (move_uploaded_file($file['tmp_name'], $file_target)) {
                $file_path = 'Uploads/' . $file_filename;
                error_log("File uploaded: $file_path");
            } else {
                $errors[] = "Failed to upload file.";
                error_log("File upload failed: " . $file['error']);
            }
        }
    } else {
        $errors[] = "Document file is required.";
    }

    // Insert into database if no errors
    if (empty($errors) && $file_path) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO archives (image_path, published_date, abstract, file_path, details, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            // Bind parameters, handling null values
            $null = null;
            $published_date = $published_date ?: null;
            $stmt->bind_param(
                "sssssi",
                $image_path,
                $published_date,
                $abstract,
                $file_path,
                $details,
                $uploaded_by
            );

            if ($stmt->execute()) {
                $success = "Archive article uploaded successfully.";
                $abstract = $details = $published_date = "";
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Database error: " . htmlspecialchars($e->getMessage());
            error_log("Archive upload error: " . $e->getMessage());
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Archive Article - FMS Journal</title>
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

    header {
        background: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 10px 0;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .logo-icon i {
        font-size: 2rem;
        color: var(--primary-color);
    }

    .logo-text h1 {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        margin: 0;
        color: var(--primary-color);
    }

    .logo-text p {
        margin: 0;
        font-size: 0.9rem;
        color: #6c757d;
    }

    nav ul {
        list-style: none;
        padding: 0;
        display: flex;
        gap: 20px;
        margin: 0;
    }

    nav ul li a {
        text-decoration: none;
        color: #1a2a44;
        font-weight: 500;
    }

    nav ul li a.btn {
        padding: 8px 16px;
        border-radius: 4px;
    }

    .btn-primary {
        background: var(--primary-color);
        color: #fff;
        border: none;
    }

    .btn-outline {
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        background: transparent;
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

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 1rem;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
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

    footer {
        background: #1a2a44;
        color: #fff;
        padding: 40px 0;
        margin-top: 40px;
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .footer-main h2 {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
    }

    .social-links a {
        color: #fff;
        font-size: 1.2rem;
        margin-right: 10px;
    }

    .footer-links h3 {
        font-size: 1.2rem;
        margin-bottom: 10px;
    }

    .footer-links ul {
        list-style: none;
        padding: 0;
    }

    .footer-links ul li a {
        color: #fff;
        text-decoration: none;
        font-size: 0.95rem;
    }

    .contact-info .contact-item {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .footer-bottom {
        border-top: 1px solid #fff;
        margin-top: 20px;
        padding-top: 20px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .form-card h2 {
            font-size: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="logo-text">
                    <h1>FMS Journal</h1>
                    <p>Faculty of Management Science</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../index.php#about">About</a></li>
                    <li><a href="../index.php#features">Research Areas</a></li>
                    <li><a href="../index.php#publications">Publications</a></li>
                    <li><a href="../index.php#submission">Submit</a></li>
                    <?php if (isset($_SESSION['admin_id'])): ?>
                    <li><a href="archive_upload.php" class="btn btn-outline">Upload Archive</a></li>
                    <?php endif; ?>
                    <li><a href="../login.php" class="btn btn-outline">Login</a></li>
                    <li><a href="../signup.php" class="btn btn-primary">Join Us</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="upload-form">
        <div class="container">
            <div class="form-card">
                <h2>Upload Archive Article</h2>
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
                <form action="archive_upload.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="image">Article Image (Optional, JPEG/PNG/GIF, Max 50MB)</label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                    </div>
                    <div class="form-group">
                        <label for="published_date">Published Date (Optional)</label>
                        <input type="date" id="published_date" name="published_date"
                            value="<?php echo htmlspecialchars($published_date); ?>">
                    </div>
                    <div class="form-group">
                        <label for="abstract">Abstract (Required)</label>
                        <textarea id="abstract" name="abstract"
                            required><?php echo htmlspecialchars($abstract); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="file">Document File (Required, PDF/DOCX, Max 50MB)</label>
                        <input type="file" id="file" name="file" accept=".pdf,.doc,.docx" required>
                    </div>
                    <div class="form-group">
                        <label for="details">Details (Optional)</label>
                        <textarea id="details" name="details"><?php echo htmlspecialchars($details); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-submit"><i class="fas fa-upload"></i> Upload Article</button>
                </form>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-main">
                    <h2>FMS Journal</h2>
                    <p>Advancing management science research through rigorous peer-reviewed publications and fostering
                        academic excellence.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-researchgate"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3>Journal</h3>
                    <ul>
                        <li><a href="../index.php#about">About Us</a></li>
                        <li><a href="../index.php#features">Research Areas</a></li>
                        <li><a href="../index.php#publications">Current Issue</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Information</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <p>Faculty of Management Science</p>
                                <p>University of Maiduguri</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <p>editor@fmsjournal.edu.ng</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 FMS Journal - Faculty of Management Science. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>
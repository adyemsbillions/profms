<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
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

// Initialize variables
$errors = [];
$success = "";
$content = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize text input
    $content = trim($_POST['content'] ?? '');

    // Validate input
    if (empty($content)) {
        $errors[] = "Message content is required.";
    }

    // Insert into database if no errors
    if (empty($errors)) {
        try {
            $sender_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("
                INSERT INTO messages (content, sender_id)
                VALUES (?, ?)
            ");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("si", $content, $sender_id);

            if ($stmt->execute()) {
                $success = "Notification sent successfully.";
                $content = "";
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Database error: " . htmlspecialchars($e->getMessage());
            error_log("Notification send error: " . $e->getMessage());
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
    <title>Send Notification - FMS Journal</title>
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

    .notification-form {
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

    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 1rem;
        min-height: 150px;
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
    <section class="notification-form">
        <div class="container">
            <div class="form-card">
                <h2>Send Notification</h2>
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
                <form action="send_notification.php" method="POST">
                    <div class="form-group">
                        <label for="content">Message (Required)</label>
                        <textarea id="content" name="content"
                            required><?php echo htmlspecialchars($content); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-submit"><i class="fas fa-paper-plane"></i> Send
                        Notification</button>
                </form>
            </div>
        </div>
    </section>
</body>

</html>
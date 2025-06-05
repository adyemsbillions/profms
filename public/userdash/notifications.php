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

// Fetch messages with sender info
$messages = [];
try {
    $result = $conn->query("
        SELECT m.id, m.content, m.created_at, CONCAT(u.first_name, ' ', u.last_name) AS sender_name
        FROM messages m
        LEFT JOIN users u ON m.sender_id = u.id
        ORDER BY m.created_at DESC
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $result->free();
    } else {
        throw new Exception("Query failed: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error fetching messages: " . $e->getMessage());
    $error = "Failed to load notifications. Please try again later.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - FMS Journal</title>
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

    .notifications-section {
        padding: 40px 0;
    }

    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: #1a2a44;
        margin-bottom: 20px;
    }

    .notification-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }

    .notification-card p {
        font-size: 1rem;
        color: #1a2a44;
        margin-bottom: 10px;
    }

    .notification-card .meta {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .alert {
        margin-bottom: 20px;
    }

    .no-notifications {
        font-size: 1rem;
        color: #6c757d;
        text-align: center;
    }

    @media (max-width: 768px) {
        .section-title {
            font-size: 1.5rem;
        }

        .notification-card p {
            font-size: 0.9rem;
        }

        .notification-card .meta {
            font-size: 0.8rem;
        }
    }
    </style>
</head>

<body>
    <section class="notifications-section">
        <div class="container">
            <h2 class="section-title">Notifications</h2>
            <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (empty($messages)): ?>
            <p class="no-notifications">No notifications found.</p>
            <?php else: ?>
            <?php foreach ($messages as $message): ?>
            <div class="notification-card">
                <p><?php echo htmlspecialchars($message['content']); ?></p>
                <div class="meta">
                    <span>Sent by: Admin</span> |
                    <span><?php echo date('F j, Y, g:i A', strtotime($message['created_at'])); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>
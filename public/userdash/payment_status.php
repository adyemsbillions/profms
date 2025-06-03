<?php
session_start();
require('db.php');  // your DB connection

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

$stmt = $conn->prepare("SELECT first_name, is_paid FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Payment Status</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            margin: 2rem;
            color: #1f2937;
        }

        .status-container {
            max-width: 400px;
            margin: 2rem auto;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .status-message {
            font-size: 1.25rem;
            font-weight: 600;
            color: #10b981;
            /* green */
            margin-bottom: 1rem;
        }

        .greeting {
            font-size: 1rem;
            margin-bottom: 1.5rem;
            color: #374151;
        }

        .info {
            color: #6b7280;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="status-container">
        <p class="greeting">Hello, <?= htmlspecialchars($user['first_name']) ?>!</p>

        <?php if ($user['is_paid'] == 1): ?>
            <p class="status-message">✅ You have paid.</p>
            <p class="info">Thank you for your payment. You now have full access.</p>
        <?php else: ?>
            <p class="status-message" style="color:#ef4444;">❌ Payment Pending</p>
            <p class="info">Please complete your payment to unlock all features.</p>
        <?php endif; ?>
    </div>
</body>

</html>
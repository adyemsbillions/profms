<?php
session_start();
require('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    $errors = [];
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    if (strlen($subject) > 255) {
        $errors[] = 'Subject cannot exceed 255 characters.';
    }
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }

    if (empty($errors)) {
        try {
            // Insert inquiry into database
            $stmt = $conn->prepare("INSERT INTO support_inquiries (user_id, subject, message) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }
            $stmt->bind_param("iss", $user_id, $subject, $message);

            if ($stmt->execute()) {
                $success_message = 'Your support inquiry has been submitted successfully!';
            } else {
                throw new Exception('Failed to submit inquiry: ' . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            $error_message = 'Database error: ' . $e->getMessage() . '. Please try again or contact support.';
        }
    } else {
        $error_message = implode(' ', $errors);
    }
}

// Fetch user data
try {
    $stmt = $conn->prepare("SELECT first_name, last_name, email, institution, position, research_interests, orcid, linkedin FROM users WHERE id = ?");
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("User not found.");
    }

    $user = $result->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
$conn->close();

function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Help & Support</title>
    <style>
    /* Reset and base styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f4f7fc;
        color: #1f2937;
        line-height: 1.5;
        padding: 2rem;
    }

    /* Messages */
    .message {
        max-width: 800px;
        margin: 1rem auto;
        padding: 1rem;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 500;
        text-align: center;
    }

    .message.success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .message.error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #f87171;
    }

    /* Page header */
    .page-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 2.25rem;
        font-weight: 700;
        color: #1e3a8a;
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 1.1rem;
        font-weight: 400;
    }

    /* Container */
    .container {
        max-width: 800px;
        margin: 0 auto;
    }

    /* User info section */
    .user-info-section {
        background: white;
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        display: flex;
        gap: 2rem;
        align-items: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .user-info-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .user-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        color: white;
        font-weight: 700;
        font-size: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        user-select: none;
        transition: transform 0.3s ease;
    }

    .user-avatar:hover {
        transform: scale(1.05);
    }

    .user-details {
        flex: 1;
    }

    .user-details p {
        margin-bottom: 0.5rem;
        font-size: 1rem;
    }

    .user-details p strong {
        color: #374151;
        font-weight: 600;
    }

    /* Contact form section */
    .form-container {
        background: white;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .form-container:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 0.875rem;
        font-size: 1rem;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        background: #f9fafb;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
    }

    /* Buttons */
    .btn {
        padding: 0.875rem 1.75rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: #1e3a8a;
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* Support contact info */
    .support-contact {
        margin-top: 2rem;
        text-align: center;
        color: #6b7280;
    }

    .support-contact p {
        margin-bottom: 0.5rem;
    }

    .support-contact a {
        color: #3b82f6;
        text-decoration: none;
    }

    .support-contact a:hover {
        text-decoration: underline;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .user-info-section {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .form-container {
            padding: 1.5rem;
        }

        .page-header {
            text-align: left;
        }
    }

    @media (max-width: 480px) {
        body {
            padding: 1rem;
        }

        .page-title {
            font-size: 1.75rem;
        }

        .page-subtitle {
            font-size: 1rem;
        }

        .btn {
            padding: 0.75rem 1.25rem;
            font-size: 0.9rem;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            font-size: 2rem;
        }
    }
    </style>
</head>

<body>
    <div class="page-header">
        <h1 class="page-title">Help & Support</h1>
        <p class="page-subtitle">Get assistance with your account or platform queries</p>
    </div>

    <?php if ($success_message): ?>
    <div class="message success"><?= e($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
    <div class="message error"><?= e($error_message) ?></div>
    <?php endif; ?>

    <div class="container">
        <!-- User Info Section -->
        <div class="user-info-section">
            <div class="user-avatar">
                <?= e(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
            </div>
            <div class="user-details">
                <p><strong>Name:</strong> <?= e($user['first_name'] . ' ' . $user['last_name']) ?></p>
                <p><strong>Email:</strong> <?= e($user['email']) ?></p>
                <?php if (!empty($user['institution'])): ?>
                <p><strong>Institution:</strong> <?= e($user['institution']) ?></p>
                <?php endif; ?>
                <?php if (!empty($user['position'])): ?>
                <p><strong>Designation:</strong> <?= e($user['position']) ?></p>
                <?php endif; ?>
                <?php if (!empty($user['research_interests'])): ?>
                <p><strong>Bio:</strong> <?= e($user['research_interests']) ?></p>
                <?php endif; ?>
                <?php if (!empty($user['orcid'])): ?>
                <p><strong>ORCID ID:</strong> <?= e($user['orcid']) ?></p>
                <?php endif; ?>
                <?php if (!empty($user['linkedin'])): ?>
                <p><strong>LinkedIn:</strong> <a href="<?= e($user['linkedin']) ?>"
                        target="_blank"><?= e($user['linkedin']) ?></a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact Form Section -->
        <div class="form-container">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">Submit a Support Request</h2>
            <form id="supportForm" method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-input"
                        value="<?= e($_POST['subject'] ?? '') ?>" required maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-label" for="message">Message</label>
                    <textarea id="message" name="message" class="form-textarea" rows="5"
                        placeholder="Describe your issue or question"
                        required><?= e($_POST['message'] ?? '') ?></textarea>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Submit Inquiry</button>
                </div>
            </form>
        </div>

        <!-- Support Contact Info -->
        <div class="support-contact">
            <p>Need immediate assistance? Contact us:</p>
            <p>Email: <a href="mailto:fadimaalfa12@unimaid.edu.ng">fadimaalfa12@unimaid.edu.ng</a></p>
            <p>Phone: <a href="tel:+2348034536092">+2348034536092</a></p>
            <p>faculty of management sciences university of maiduguri</p>
        </div>
    </div>
</body>

</html>
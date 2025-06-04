<?php
session_start();
require_once 'db.php'; // contains $pdo = new PDO(...) connection

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);

    try {
        // Check if token exists and is not already verified
        $stmt = $pdo->prepare("SELECT id, email_verified FROM users WHERE verification_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['email_verified'] == 1) {
                $_SESSION['verify_message'] = "Your email is already verified.";
                header("Location: login.php");
                exit;
            }

            // Update email_verified status
            $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE verification_token = ?");
            $stmt->execute([$token]);

            $_SESSION['verify_message'] = "Email verified successfully! Await admin approval to log in.";
            header("Location: login.php");
            exit;
        } else {
            $_SESSION['verify_error'] = "Invalid or expired verification link.";
            header("Location: signup_form.php");
            exit;
        }
    } catch (Exception $e) {
        error_log("Verification error: " . $e->getMessage());
        $_SESSION['verify_error'] = "An error occurred during verification. Please try again.";
        header("Location: signup_form.php");
        exit;
    }
} else {
    http_response_code(400);
    echo "Invalid request.";
}

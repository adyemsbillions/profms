<?php
session_start();
require_once 'db.php'; // your PDO connection

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['loginEmail'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['loginPassword'] ?? '';

    if (!$email || !$password) {
        $login_error = "Please enter both email and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, password_hash, is_approved FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $login_error = "Invalid email or password.";
            } elseif (!$user['is_approved']) {
                $login_error = "Your account is not yet approved. Please wait for admin approval.";
            } elseif (!password_verify($password, $user['password_hash'])) {
                $login_error = "Invalid email or password.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                header("Location: dashboard.php");
                exit;
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $login_error = "An error occurred. Please try again later.";
        }
    }
}
<?php
session_start();
require_once 'db.php'; // contains $pdo = new PDO(...) connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $researchInterests = trim($_POST['researchInterests'] ?? '');
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    $reviewer = isset($_POST['reviewer']) ? 1 : 0;
    $terms = isset($_POST['terms']) ? true : false;

    // Basic validation
    $errors = [];

    if (!$firstName) $errors[] = "First name is required.";
    if (!$lastName) $errors[] = "Last name is required.";
    if (!$email) $errors[] = "Valid email is required.";
    if (!$country) $errors[] = "Country is required.";
    if (!$institution) $errors[] = "Institution is required.";
    if (!$position) $errors[] = "Position is required.";
    if (!$password) $errors[] = "Password is required.";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";
    if (!$terms) $errors[] = "You must agree to the terms and conditions.";

    if (!empty($errors)) {
        $_SESSION['signup_errors'] = $errors;
        header("Location: signup_form.php"); // Adjust to your signup page
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['signup_errors'] = ["Email address already registered."];
            header("Location: signup_form.php");
            exit;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user with is_approved = 0 by default (needs admin approval)
        $stmt = $pdo->prepare("INSERT INTO users 
            (first_name, last_name, email, phone, country, institution, position, password_hash, research_interests, wants_newsletter, is_reviewer, is_approved) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

        $stmt->execute([
            $firstName,
            $lastName,
            $email,
            $phone,
            $country,
            $institution,
            $position,
            $passwordHash,
            $researchInterests,
            $newsletter,
            $reviewer
        ]);

        // Optionally: Send email notification to admin for approval & user confirmation

        $_SESSION['signup_success'] = "Registration successful! Await admin approval before login.";
        header("Location: signup_success.php"); // Redirect to a success page or login
        exit;
    } catch (Exception $e) {
        // Log error internally
        error_log("Signup error: " . $e->getMessage());

        $_SESSION['signup_errors'] = ["An unexpected error occurred. Please try again."];
        header("Location: signup_form.php");
        exit;
    }
} else {
    // Reject non-POST requests
    http_response_code(405);
    echo "Method Not Allowed";
}
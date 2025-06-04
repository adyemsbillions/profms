<?php
session_start();
require_once 'db.php'; // contains $pdo = new PDO(...) connection
require 'vendor/autoload.php'; // Include Composer's autoloader for PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to log security events
function logSecurityEvent($message)
{
    $log_file = 'security.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

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
        logSecurityEvent("Signup failed for $email: " . implode(", ", $errors));
        header("Location: signup_form.php");
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['signup_errors'] = ["Email address already registered."];
            logSecurityEvent("Signup failed for $email: Email already registered");
            header("Location: signup_form.php");
            exit;
        }

        // Generate a unique verification token
        $verificationToken = bin2hex(random_bytes(32));

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user with is_approved = 0 and email_verified = 0
        $stmt = $pdo->prepare("INSERT INTO users 
            (first_name, last_name, email, phone, country, institution, position, password_hash, research_interests, wants_newsletter, is_reviewer, is_approved, verification_token, email_verified) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0)");

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
            $reviewer,
            $verificationToken
        ]);

        // Send verification email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Enable verbose debugging
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function ($str, $level) {
                file_put_contents('phpmailer.log', "[$level] $str\n", FILE_APPEND);
            };
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'adyemsgodlove@gmail.com';
            $mail->Password = 'dxtk fzks djyo vazs'; // Use the working App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Recipients
            $mail->setFrom('adyemsgodlove@gmail.com', 'FMS Journal');
            $mail->addAddress($email, $firstName . ' ' . $lastName);

            // Content
            $verificationLink = "http://localhost/profms/public/verify_email.php?token=$verificationToken";
            $mail->isHTML(true); // Fixed the typo here
            $mail->Subject = 'Verify Your Email Address - FMS Journal';
            $mail->Body = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Email Verification</title>
                </head>
                <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
                    <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                        <tr>
                            <td style='background-color: #1b5e20; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>FMS Journal</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px; text-align: center;'>
                                <h2 style='color: #1b5e20; font-size: 20px; margin-bottom: 20px;'>Verify Your Email Address</h2>
                                <p style='color: #333; font-size: 16px; line-height: 1.5; margin-bottom: 20px;'>
                                    Dear " . htmlspecialchars($firstName . ' ' . $lastName) . ",<br>
                                    Thank you for registering with FMS Journal. Please click the link below to verify your email address:
                                </p>
                                <a href='$verificationLink' style='display: inline-block; padding: 12px 24px; background-color: #1976d2; color: #ffffff; text-decoration: none; font-size: 16px; border-radius: 5px;'>Verify Email</a>
                                <p style='color: #666; font-size: 14px; line-height: 1.5; margin-top: 20px;'>
                                    If you did not register, please ignore this email.
                                </p>
                                <p style='color: #666; font-size: 14px; line-height: 1.5;'>
                                    Best regards,<br>FMS Journal Team
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style='background-color: #f9f9f9; padding: 20px; text-align: center; border-radius: 0 0 8px 8px;'>
                                <p style='color: #666; font-size: 12px; margin: 0;'>
                                    © " . date('Y') . " FMS Journal. All rights reserved.
                                </p>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>
            ";
            $mail->AltBody = "Welcome to FMS Journal! Please verify your email by visiting this link: $verificationLink";

            $mail->send();
            logSecurityEvent("Verification email sent to $email");
            // For debugging, capture debug output
            // Comment out in production
            $debugOutput = file_get_contents('phpmailer.log');
            echo "Email sent successfully.<br>Debug Output:<br><pre>$debugOutput</pre>";
        } catch (Exception $e) {
            $debugOutput = file_get_contents('phpmailer.log');
            logSecurityEvent("Email sending failed for $email: " . $e->getMessage());
            file_put_contents('phpmailer_error.log', "[" . date('Y-m-d H:i:s') . "] Email sending failed for $email: " . $e->getMessage() . "\nDebug: $debugOutput\n", FILE_APPEND);
            echo "Email sending failed: " . $e->getMessage() . "<br>Debug Output:<br><pre>$debugOutput</pre>";
            $_SESSION['signup_errors'] = ["Failed to send verification email: " . $e->getMessage()];
            // Comment out redirect for debugging
            // header("Location: signup_form.php");
            exit;
        } finally {
            $mail->clearAddresses();
        }

        $_SESSION['signup_success'] = "Registration successful! Please check your email to verify your account.";
        header("Location: signup_success.php");
        exit;
    } catch (Exception $e) {
        logSecurityEvent("Signup error for $email: " . $e->getMessage());
        file_put_contents('phpmailer_error.log', "[" . date('Y-m-d H:i:s') . "] Signup error for $email: " . $e->getMessage() . "\n", FILE_APPEND);
        $_SESSION['signup_errors'] = ["An unexpected error occurred. Please try again."];
        header("Location: signup_form.php");
        exit;
    }
} else {
    http_response_code(405);
    echo "Method Not Allowed";
}

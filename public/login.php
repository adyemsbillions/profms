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
                header("Location: userdash/dashboard.php");
                exit;
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $login_error = "An error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign In - FMS Journal</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <style>
    .error-message {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    </style>
</head>

<body>
    <header class="minimal-header">
        <div class="container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="logo-text">
                    <h1><a href="index.php">FMS Journal</a></h1>
                    <p>Faculty of Management Science</p>
                </div>
            </div>
        </div>
    </header>

    <section class="auth-section">
        <div class="auth-background">
            <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80"
                alt="Academic Background" class="auth-bg-image" />
            <div class="auth-overlay"></div>
        </div>

        <div class="container">
            <div class="auth-container login-container">
                <div class="auth-form-container">
                    <div class="auth-header">
                        <div class="auth-icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <h2>Welcome Back</h2>
                        <p>Sign in to your FMS Journal account to access your dashboard and manage your submissions</p>
                    </div>

                    <!-- Display login errors here -->
                    <?php if ($login_error): ?>
                    <div class="error-message"><?= htmlspecialchars($login_error) ?></div>
                    <?php endif; ?>

                    <form class="auth-form" method="POST" action="login.php" autocomplete="on">
                        <div class="form-group">
                            <label for="loginEmail">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="loginEmail" name="loginEmail" required
                                    placeholder="Enter your email address" autocomplete="email" autofocus
                                    value="<?= isset($_POST['loginEmail']) ? htmlspecialchars($_POST['loginEmail']) : '' ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="loginPassword">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="loginPassword" name="loginPassword" required
                                    placeholder="Enter your password" autocomplete="current-password" />
                                <button type="button" class="password-toggle" onclick="togglePassword('loginPassword')"
                                    aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <div class="checkbox-group">
                                <input type="checkbox" id="rememberMe" name="rememberMe" />
                                <label for="rememberMe">Remember me</label>
                            </div>
                            <a href="#" class="forgot-password-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full btn-large">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In
                        </button>
                    </form>

                    <div class="auth-links">
                        <p>Don't have an account? <a href="signup.php">Create Account</a></p>
                    </div>

                    <div class="auth-divider">
                        <span>or</span>
                    </div>

                    <div class="social-auth">
                        <button class="social-btn google-btn">
                            <i class="fab fa-google"></i>
                            Continue with Google
                        </button>
                        <button class="social-btn orcid-btn">
                            <i class="fab fa-orcid"></i>
                            Continue with ORCID
                        </button>
                    </div>

                    <div class="help-section">
                        <h4>Need Help?</h4>
                        <div class="help-links">
                            <a href="#"><i class="fas fa-question-circle"></i> Account Recovery</a>
                            <a href="#"><i class="fas fa-envelope"></i> Contact Support</a>
                            <a href="#"><i class="fas fa-book"></i> User Guide</a>
                        </div>
                    </div>
                </div>

                <div class="auth-info">
                    <div class="info-content">
                        <h3>Latest Research Highlights</h3>

                        <div class="featured-research">
                            <div class="research-item">
                                <div class="research-image">
                                    <img src="https://images.unsplash.com/photo-1677442136019-21780ecad995?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=120&q=80"
                                        alt="AI Research" />
                                </div>
                                <div class="research-content">
                                    <h4>AI in Financial Services</h4>
                                    <p>Latest research on artificial intelligence applications in Nigerian banking
                                        sector</p>
                                    <span class="research-date">March 2025</span>
                                </div>
                            </div>

                            <div class="research-item">
                                <div class="research-image">
                                    <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=120&q=80"
                                        alt="Sustainability Research" />
                                </div>
                                <div class="research-content">
                                    <h4>Sustainability Reporting</h4>
                                    <p>Environmental accounting practices in emerging markets</p>
                                    <span class="research-date">February 2025</span>
                                </div>
                            </div>

                            <div class="research-item">
                                <div class="research-image">
                                    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=120&q=80"
                                        alt="Corporate Governance" />
                                </div>
                                <div class="research-content">
                                    <h4>Corporate Governance</h4>
                                    <p>Board effectiveness and firm performance in African markets</p>
                                    <span class="research-date">January 2025</span>
                                </div>
                            </div>
                        </div>

                        <div class="upcoming-events">
                            <h4>Upcoming Events</h4>
                            <div class="event-item">
                                <div class="event-date">
                                    <span class="day">15</span>
                                    <span class="month">Apr</span>
                                </div>
                                <div class="event-info">
                                    <h5>Call for Papers: Special Issue</h5>
                                    <p>Digital Transformation in Management</p>
                                </div>
                            </div>
                            <div class="event-item">
                                <div class="event-date">
                                    <span class="day">30</span>
                                    <span class="month">Jun</span>
                                </div>
                                <div class="event-info">
                                    <h5>Submission Deadline</h5>
                                    <p>Regular Issue Vol. 2 No. 2</p>
                                </div>
                            </div>
                        </div>

                        <div class="journal-impact">
                            <h4>Journal Impact</h4>
                            <div class="impact-stats">
                                <div class="impact-item">
                                    <i class="fas fa-eye"></i>
                                    <div>
                                        <span class="impact-number">50K+</span>
                                        <span class="impact-label">Article Views</span>
                                    </div>
                                </div>
                                <div class="impact-item">
                                    <i class="fas fa-download"></i>
                                    <div>
                                        <span class="impact-number">25K+</span>
                                        <span class="impact-label">Downloads</span>
                                    </div>
                                </div>
                                <div class="impact-item">
                                    <i class="fas fa-quote-right"></i>
                                    <div>
                                        <span class="impact-number">1.2K+</span>
                                        <span class="impact-label">Citations</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="minimal-footer">
        <div class="container">
            <div class="footer-minimal-content">
                <p>&copy; 2025 FMS Journal - Faculty of Management Science. All rights reserved.</p>
                <div class="footer-minimal-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Help</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        if (input.type === "password") {
            input.type = "text";
        } else {
            input.type = "password";
        }
    }
    </script>
</body>

</html>
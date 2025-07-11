<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join FMS Journal - Create Account</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
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
                alt="Academic Background" class="auth-bg-image">
            <div class="auth-overlay"></div>
        </div>
        <div class="container">
            <div class="auth-container">
                <div class="auth-form-container">
                    <div class="auth-header">
                        <div class="auth-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h2>Join Our Research Community</h2>
                        <p>Create your account to submit manuscripts, access exclusive content, and connect with fellow
                            researchers</p>
                    </div>

                    <form class="auth-form" method="POST" action="signup_script.php">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="firstName" name="firstName" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="lastName" name="lastName" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="phone" name="phone">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="country">Country</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-globe"></i>
                                    <select id="country" name="country" required>
                                        <option value="">Select Country</option>
                                        <option value="nigeria">Nigeria</option>
                                        <option value="ghana">Ghana</option>
                                        <option value="kenya">Kenya</option>
                                        <option value="south-africa">South Africa</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="institution">Institution/Organization</label>
                            <div class="input-wrapper">
                                <i class="fas fa-university"></i>
                                <input type="text" id="institution" name="institution" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="position">Academic Position</label>
                            <div class="input-wrapper">
                                <i class="fas fa-briefcase"></i>
                                <select id="position" name="position" required>
                                    <option value="">Select Position</option>
                                    <option value="professor">Professor</option>
                                    <option value="associate-professor">Associate Professor</option>
                                    <option value="assistant-professor">Assistant Professor</option>
                                    <option value="lecturer">Lecturer</option>
                                    <option value="researcher">Researcher</option>
                                    <option value="phd-student">PhD Student</option>
                                    <option value="masters-student">Masters Student</option>
                                    <option value="practitioner">Industry Practitioner</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill"></div>
                                    </div>
                                    <span class="strength-text">Password strength</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                                    <button type="button" class="password-toggle"
                                        onclick="togglePassword('confirmPassword')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="researchInterests">Research Interests (Optional)</label>
                            <div class="input-wrapper">
                                <i class="fas fa-microscope"></i>
                                <textarea id="researchInterests" name="researchInterests" rows="3"
                                    placeholder="Briefly describe your research interests and areas of expertise..."></textarea>
                            </div>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to the <a href="#" target="_blank">Terms and Conditions</a> and
                                <a href="#" target="_blank">Privacy Policy</a></label>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" id="newsletter" name="newsletter" checked>
                            <label for="newsletter">Subscribe to the FMS Journal newsletter and research updates</label>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" id="reviewer" name="reviewer">
                            <label for="reviewer">I'm interested in becoming a peer reviewer</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full btn-large">
                            <i class="fas fa-user-plus"></i>
                            Create My Account
                        </button>
                    </form>






                    <div class="auth-links">
                        <p>Already have an account? <a href="login.php">Sign In</a></p>
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
                </div>

                <div class="auth-info">
                    <div class="info-content">
                        <h3>Why Join FMS Journal?</h3>
                        <div class="benefits-list">
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div class="benefit-text">
                                    <h4>Submit Research</h4>
                                    <p>Submit your manuscripts and track their progress through our streamlined review
                                        process</p>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="benefit-text">
                                    <h4>Access Full Articles</h4>
                                    <p>Get unlimited access to our complete archive of published research articles</p>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="benefit-text">
                                    <h4>Join Peer Review</h4>
                                    <p>Contribute to the academic community by participating in our peer review process
                                    </p>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="benefit-text">
                                    <h4>Stay Updated</h4>
                                    <p>Receive notifications about new issues, calls for papers, and research
                                        opportunities</p>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <div class="benefit-text">
                                    <h4>Build Reputation</h4>
                                    <p>Enhance your academic profile with publications in our peer-reviewed journal</p>
                                </div>
                            </div>
                        </div>

                        <div class="community-stats">
                            <h4>Join Our Growing Community</h4>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <span class="stat-number">2,500+</span>
                                    <span class="stat-label">Registered Authors</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">50+</span>
                                    <span class="stat-label">Countries</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">500+</span>
                                    <span class="stat-label">Published Articles</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">150+</span>
                                    <span class="stat-label">Expert Reviewers</span>
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

    <script src="script.js"></script>
</body>

</html>
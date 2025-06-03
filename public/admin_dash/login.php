<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Journal Platform</title>
    <style>
        :root {
            --primary-color: #1e3a8a;
            --primary-light: #3b82f6;
            --secondary-color: #059669;
            --secondary-light: #10b981;
            --accent-color: #f59e0b;
            --accent-light: #fbbf24;
            --danger-color: #dc2626;
            --danger-light: #ef4444;
            --text-color: #1f2937;
            --text-light: #6b7280;
            --text-lighter: #9ca3af;
            --bg-color: #ffffff;
            --bg-light: #f9fafb;
            --bg-lighter: #f3f4f6;
            --border-color: #e5e7eb;
            --border-light: #f3f4f6;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(1deg);
            }
        }

        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: floatElement 15s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 20%;
            right: 10%;
            animation-delay: 5s;
        }

        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 15%;
            animation-delay: 10s;
        }

        @keyframes floatElement {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            33% {
                transform: translateY(-30px) rotate(5deg);
            }

            66% {
                transform: translateY(15px) rotate(-3deg);
            }
        }

        .auth-container {
            background: var(--bg-color);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 10;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .auth-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {

            0%,
            100% {
                transform: rotate(0deg);
            }

            50% {
                transform: rotate(180deg);
            }
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .logo-icon {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin-right: 0.75rem;
            vertical-align: middle;
            position: relative;
        }

        .logo-icon::before {
            content: '🔐';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.2rem;
        }

        .auth-subtitle {
            opacity: 0.9;
            font-size: 0.95rem;
            position: relative;
            z-index: 2;
        }

        .admin-badge {
            background: var(--danger-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.5rem;
            display: inline-block;
            position: relative;
            z-index: 2;
        }

        .form-toggle {
            display: flex;
            background: var(--bg-lighter);
            border-radius: 12px;
            padding: 0.25rem;
            margin: 2rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .toggle-btn {
            flex: 1;
            padding: 0.75rem 1rem;
            background: transparent;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
            color: var(--text-light);
        }

        .toggle-btn.active {
            color: var(--primary-color);
            background: var(--bg-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            padding: 0 2rem 2rem;
            position: relative;
        }

        .auth-form {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }

        .auth-form.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: var(--bg-color);
            position: relative;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }

        .form-input:valid {
            border-color: var(--secondary-color);
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            fill: var(--text-light);
            transition: fill 0.3s ease;
            z-index: 2;
        }

        .input-group .form-input {
            padding-left: 3rem;
        }

        .input-group .form-input:focus+.input-icon {
            fill: var(--primary-color);
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-light);
            transition: color 0.3s ease;
            z-index: 2;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border-color);
            border-radius: 4px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkbox input {
            opacity: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .checkbox input:checked+.checkmark {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .checkbox input:checked+.checkmark::after {
            opacity: 1;
            transform: scale(1);
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .checkmark::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            color: white;
            font-size: 12px;
            font-weight: bold;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .checkbox-label {
            font-size: 0.9rem;
            color: var(--text-color);
            cursor: pointer;
            user-select: none;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .auth-link:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--text-light);
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .divider span {
            padding: 0 1rem;
            background: var(--bg-color);
        }

        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .social-btn {
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            background: var(--bg-color);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            color: var(--text-color);
        }

        .social-btn:hover {
            border-color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .social-icon {
            width: 20px;
            height: 20px;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: var(--danger-color);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            display: none;
        }

        .error-message.show {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        .success-message {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: var(--secondary-color);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            display: none;
        }

        .success-message.show {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .auth-container {
                margin: 0.5rem;
                border-radius: 16px;
            }

            .auth-header {
                padding: 1.5rem;
            }

            .logo {
                font-size: 1.75rem;
            }

            .form-container {
                padding: 0 1.5rem 1.5rem;
            }

            .form-toggle {
                margin: 1.5rem;
                margin-bottom: 1rem;
            }

            .social-login {
                grid-template-columns: 1fr;
            }

            .btn {
                padding: 0.875rem;
            }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --text-color: #f9fafb;
                --text-light: #d1d5db;
                --text-lighter: #9ca3af;
                --bg-color: #1f2937;
                --bg-light: #111827;
                --bg-lighter: #374151;
                --border-color: #4b5563;
            }
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .toggle-btn:focus,
        .form-input:focus,
        .btn:focus,
        .auth-link:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
    </style>
</head>

<body>
    <div class="floating-element">
        <svg width="60" height="60" viewBox="0 0 24 24" fill="rgba(255,255,255,0.1)">
            <path
                d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19Z" />
        </svg>
    </div>
    <div class="floating-element">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="rgba(255,255,255,0.1)">
            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
        </svg>
    </div>
    <div class="floating-element">
        <svg width="70" height="70" viewBox="0 0 24 24" fill="rgba(255,255,255,0.1)">
            <path
                d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" />
        </svg>
    </div>

    <div class="auth-container">
        <div class="auth-header">
            <div class="logo">
                <span class="logo-icon"></span>
                Admin Portal
            </div>
            <p class="auth-subtitle">Journal Platform Administration</p>
            <span class="admin-badge">Secure Access</span>
        </div>

        <div class="form-toggle">
            <button class="toggle-btn active" onclick="showForm('login')" id="loginToggle">Sign In</button>
            <button class="toggle-btn" onclick="showForm('signup')" id="signupToggle">Create Account</button>
        </div>

        <div class="form-container">
            <div id="errorMessage" class="error-message"></div>
            <div id="successMessage" class="success-message"></div>

            <form id="loginForm" name="loginForm" class="auth-form active" method="POST">
                <div class="form-group">
                    <label class="form-label" for="loginEmail">Email Address</label>
                    <div class="input-group">
                        <input type="email" id="loginEmail" name="email" class="form-input"
                            placeholder="admin@journalplatform.com" required autocomplete="email">
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path
                                d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                        </svg>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="loginPassword">Password</label>
                    <div class="input-group">
                        <input type="password" id="loginPassword" name="password" class="form-input"
                            placeholder="Enter your password" required autocomplete="current-password">
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path
                                d="M12,17A2,2 0 0,0 14,15C14,13.89 13.1,13 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V10C4,8.89 4.9,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z" />
                        </svg>
                        <button type="button" class="password-toggle"
                            onclick="togglePassword('loginPassword')">👁️</button>
                    </div>
                </div>

                <div class="checkbox-group">
                    <label class="checkbox">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        <span class="checkmark"></span>
                    </label>
                    <label class="checkbox-label" for="rememberMe">Remember me for 30 days</label>
                </div>

                <button type="submit" class="btn btn-primary">Sign In to Admin Panel</button>

                <div class="auth-links">
                    <a href="#" class="auth-link" onclick="showForgotPassword()">Forgot your password?</a>
                </div>
            </form>

            <form id="signupForm" name="signupForm" class="auth-form" method="POST">
                <div class="form-group">
                    <label class="form-label" for="signupName">Full Name</label>
                    <div class="input-group">
                        <input type="text" id="signupName" name="name" class="form-input"
                            placeholder="Enter your full name" required autocomplete="name">
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path
                                d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" />
                        </svg>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="signupEmail">Email Address</label>
                    <div class="input-group">
                        <input type="email" id="signupEmail" name="email" class="form-input"
                            placeholder="admin@yourorganization.com" required autocomplete="email">
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path
                                d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                        </svg>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="signupOrganization">Organization</label>
                    <div class="input-group">
                        <input type="text" id="signupOrganization" name="organization" class="form-input"
                            placeholder="Your organization name" required autocomplete="organization">
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path
                                d="M12,7V3H2V21H22V7H12M6,19H4V17H6V19M6,15H4V13H6V15M6,11H4V9H6V11M6,7H4V5H6V7M10,19H8V17H10V19M10,15H8V13H10V15M10,11H8V9H10V11M10,7H8V5H10V7M20,19H12V17H14V15H12V13H14V11H12V9H20V19M18,11H16V13H18V11M18,15H16V17H18V15Z" />
                        </svg>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="signupPassword">Password</label>
                    <div class="input-group">
                        <input type="password" id="signupPassword" name="password" class="form-input"
                            placeholder="Create a strong password" required autocomplete="new-password" minlength="8">
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path
                                d="M12,17A2,2 0 0,0 14,15C14,13.89 13.1,13 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V10C4,8.89 4.9,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z" />
                        </svg>
                        <button type="button" class="password-toggle"
                            onclick="togglePassword('signupPassword')">👁️</button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-input"
                            placeholder="Confirm your password" required autocomplete="new-password">
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path
                                d="M12,17A2,2 0 0,0 14,15C14,13.89 13.1,13 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V10C4,8.89 4.9,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z" />
                        </svg>
                        <button type="button" class="password-toggle"
                            onclick="togglePassword('confirmPassword')">👁️</button>
                    </div>
                </div>

                <div class="checkbox-group">
                    <label class="checkbox">
                        <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                        <span class="checkmark"></span>
                    </label>
                    <label class="checkbox-label" for="agreeTerms">
                        I agree to the <a href="#" class="auth-link">Terms of Service</a> and <a href="#"
                            class="auth-link">Privacy Policy</a>
                    </label>
                </div>

                <div class="checkbox-group">
                    <label class="checkbox">
                        <input type="checkbox" id="adminAccess" name="adminAccess">
                        <span class="checkmark"></span>
                    </label>
                    <label class="checkbox-label" for="adminAccess">
                        Request administrator privileges (requires approval)
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Create Admin Account</button>

                <div class="auth-links">
                    <span style="color: var(--text-light); font-size: 0.85rem;">
                        Already have an account?
                        <a href="#" class="auth-link" onclick="showForm('login')">Sign in here</a>
                    </span>
                </div>
            </form>

            <div class="divider">
                <span>Or continue with</span>
            </div>

            <div class="social-login">
                <a href="#" class="social-btn" onclick="socialLogin('google')">
                    <svg class="social-icon" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    Google
                </a>
                <a href="#" class="social-btn" onclick="socialLogin('microsoft')">
                    <svg class="social-icon" viewBox="0 0 24 24">
                        <path fill="#f25022" d="M1 1h10v10H1z" />
                        <path fill="#00a4ef" d="M13 1h10v10H13z" />
                        <path fill="#7fba00" d="M1 13h10v10H1z" />
                        <path fill="#ffb900" d="M13 13h10v10H13z" />
                    </svg>
                    Microsoft
                </a>
            </div>
        </div>
    </div>

    <script>
        // Form switching functionality
        function showForm(formType) {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const loginToggle = document.getElementById('loginToggle');
            const signupToggle = document.getElementById('signupToggle');

            hideMessages();

            if (formType === 'login') {
                loginForm.classList.add('active');
                signupForm.classList.remove('active');
                loginToggle.classList.add('active');
                signupToggle.classList.remove('active');
            } else {
                signupForm.classList.add('active');
                loginForm.classList.remove('active');
                signupToggle.classList.add('active');
                loginToggle.classList.remove('active');
            }
            focusFirstInput();
        }

        // Password visibility toggle
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.parentNode.querySelector('.password-toggle');
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '🙈';
            } else {
                input.type = 'password';
                button.textContent = '👁️';
            }
        }

        // Show/hide messages
        function showMessage(message, type = 'error') {
            hideMessages();
            const messageElement = document.getElementById(type + 'Message');
            messageElement.textContent = message;
            messageElement.classList.add('show');
            setTimeout(() => hideMessages(), 5000);
        }

        function hideMessages() {
            document.getElementById('errorMessage').classList.remove('show');
            document.getElementById('successMessage').classList.remove('show');
        }

        // Form validation
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function validatePassword(password) {
            const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/;
            return re.test(password);
        }

        // AJAX form submission
        function submitForm(form, url, successCallback) {
            const submitBtn = form.querySelector('.btn');
            submitBtn.classList.add('loading');
            submitBtn.textContent = form.id === 'loginForm' ? 'Signing In...' : 'Creating Account...';

            const formData = new FormData(form);
            fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    submitBtn.classList.remove('loading');
                    submitBtn.textContent = form.id === 'loginForm' ? 'Sign In to Admin Panel' : 'Create Admin Account';

                    if (data.error) {
                        showMessage(data.error);
                    } else {
                        showMessage(data.success, 'success');
                        successCallback(data);
                    }
                })
                .catch(error => {
                    submitBtn.classList.remove('loading');
                    submitBtn.textContent = form.id === 'loginForm' ? 'Sign In to Admin Panel' : 'Create Admin Account';
                    showMessage('An error occurred. Please try again.');
                    console.error('Error:', error);
                });
        }

        // Login form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const rememberMe = document.getElementById('rememberMe').checked;

            if (!validateEmail(email)) {
                showMessage('Please enter a valid email address.');
                return;
            }

            if (password.length < 6) {
                showMessage('Password must be at least 6 characters long.');
                return;
            }

            submitForm(this, 'login_process.php', function(data) {
                if (rememberMe) {
                    localStorage.setItem('adminLoggedIn', 'true');
                    localStorage.setItem('adminEmail', email);
                } else {
                    sessionStorage.setItem('adminLoggedIn', 'true');
                    sessionStorage.setItem('adminEmail', email);
                }
                setTimeout(() => {
                    window.location.href = 'admin_dash.php';
                }, 2000);
            });
        });

        // Signup form submission
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('signupName').value;
            const email = document.getElementById('signupEmail').value;
            const organization = document.getElementById('signupOrganization').value;
            const password = document.getElementById('signupPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const agreeTerms = document.getElementById('agreeTerms').checked;

            if (name.length < 2) {
                showMessage('Please enter your full name.');
                return;
            }

            if (!validateEmail(email)) {
                showMessage('Please enter a valid email address.');
                return;
            }

            if (organization.length < 2) {
                showMessage('Please enter your organization name.');
                return;
            }

            if (!validatePassword(password)) {
                showMessage('Password must be at least 8 characters with uppercase, lowercase, and number.');
                return;
            }

            if (password !== confirmPassword) {
                showMessage('Passwords do not match.');
                return;
            }

            if (!agreeTerms) {
                showMessage('Please agree to the Terms of Service and Privacy Policy.');
                return;
            }

            submitForm(this, 'signup_process.php', function(data) {
                setTimeout(() => {
                    showForm('login');
                    document.getElementById('loginEmail').value = email;
                }, 3000);
            });
        });

        // Social login (placeholder)
        function socialLogin(provider) {
            showMessage(`${provider.charAt(0).toUpperCase() + provider.slice(1)} login not implemented yet.`, 'error');
        }

        // Forgot password
        function showForgotPassword() {
            const email = prompt('Enter your email address to reset your password:');
            if (email && validateEmail(email)) {
                showMessage('Password reset instructions have been sent to your email.', 'success');
            } else if (email) {
                showMessage('Please enter a valid email address.');
            }
        }

        // Real-time password validation
        document.getElementById('signupPassword').addEventListener('input', function() {
            const password = this.value;
            const isValid = validatePassword(password);
            this.style.borderColor = password.length > 0 ? (isValid ? 'var(--secondary-color)' :
                'var(--danger-color)') : 'var(--border-color)';
        });

        // Confirm password validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('signupPassword').value;
            const confirmPassword = this.value;
            this.style.borderColor = confirmPassword.length > 0 ? (password === confirmPassword ?
                'var(--secondary-color)' : 'var(--danger-color)') : 'var(--border-color)';
        });

        // Check login state
        document.addEventListener('DOMContentLoaded', function() {
            const isLoggedIn = localStorage.getItem('adminLoggedIn') || sessionStorage.getItem('adminLoggedIn');
            const adminEmail = localStorage.getItem('adminEmail') || sessionStorage.getItem('adminEmail');
            if (isLoggedIn && adminEmail) {
                showMessage(`Welcome back! You are already logged in as ${adminEmail}`, 'success');
                document.getElementById('loginEmail').value = adminEmail;
            }
            focusFirstInput();
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.classList.contains('toggle-btn')) {
                e.target.click();
            }
        });

        // Auto-focus first input
        function focusFirstInput() {
            setTimeout(() => {
                const activeForm = document.querySelector('.auth-form.active');
                const firstInput = activeForm.querySelector('.form-input');
                if (firstInput) firstInput.focus();
            }, 100);
        }
    </script>
</body>

</html>
?>
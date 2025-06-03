<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require('db.php');  // your DB connection

$user_id = intval($_SESSION['user_id']);

$stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Dashboard</title>
    <style>
        /* Base Styles */
        :root {
            --primary-color: #1e3a8a;
            --primary-light: #3b82f6;
            --secondary-color: #059669;
            --secondary-light: #10b981;
            --accent-color: #f59e0b;
            --accent-light: #fbbf24;
            --text-color: #1f2937;
            --text-light: #6b7280;
            --text-lighter: #9ca3af;
            --bg-color: #ffffff;
            --bg-light: #f9fafb;
            --bg-lighter: #f3f4f6;
            --border-color: #e5e7eb;
            --border-light: #f3f4f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1002;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            font-size: 1.2rem;
        }

        .mobile-menu-btn:hover {
            background: var(--primary-light);
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 280px;
            background: var(--bg-color);
            border-right: 1px solid var(--border-color);
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1001;
            transition: transform 0.3s ease;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 2rem 2rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
        }

        .user-details h3 {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .user-details p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .nav-menu {
            list-style: none;
            padding: 0 1rem;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: var(--text-color);
            border-radius: 8px;
            transition: all 0.2s;
            cursor: pointer;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            fill: currentColor;
            flex-shrink: 0;
        }

        .notification-count {
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            flex-shrink: 0;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            width: calc(100% - 280px);
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        /* Header */
        .page-header {
            background: var(--bg-color);
            padding: 1.5rem 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--text-light);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-color);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-color);
        }

        .stat-card.secondary {
            border-left-color: var(--secondary-color);
        }

        .stat-card.accent {
            border-left-color: var(--accent-color);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Forms */
        .form-container {
            background: var(--bg-color);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .file-upload {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .file-upload:hover {
            border-color: var(--primary-color);
        }

        .file-upload input {
            display: none;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-light);
        }

        .btn-secondary {
            background: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background: var(--secondary-light);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        /* Tables */
        .table-container {
            background: var(--bg-color);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .table th {
            background: var(--bg-lighter);
            font-weight: 600;
            color: var(--text-color);
            white-space: nowrap;
        }

        .table td {
            vertical-align: top;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Notifications */
        .notification-item {
            background: var(--bg-color);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .notification-item.unread {
            background: #eff6ff;
        }

        .notification-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .notification-time {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        /* Journal Cards */
        .journal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .journal-card {
            background: var(--bg-color);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }

        .journal-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .journal-description {
            color: var(--text-light);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .journal-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: var(--text-lighter);
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        /* Profile Grid */
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            align-items: start;
        }

        .profile-avatar-section {
            text-align: center;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            font-size: 2rem;
            margin: 0 auto 1rem;
        }

        /* Support Grid */
        .support-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .faq-item {
            margin-bottom: 1rem;
        }

        .faq-question {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .faq-answer {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Button Groups */
        .btn-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            .journal-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .support-grid {
                grid-template-columns: 1fr;
            }

            .profile-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay.active {
                display: block;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                width: 100%;
                padding-top: 4rem;
            }

            .page-header {
                padding: 1rem 1.5rem;
                margin-bottom: 1.5rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .form-container {
                padding: 1.5rem;
            }

            .table-container {
                margin: 0 -1rem;
                border-radius: 0;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }

            .journal-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .journal-card {
                padding: 1rem;
            }

            .journal-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }

            .file-upload {
                padding: 1.5rem 1rem;
            }

            .notification-item {
                padding: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .user-info {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .profile-avatar-section {
                order: -1;
            }

            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 0.75rem;
            }

            .page-header {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .form-container {
                padding: 1rem;
            }

            .stat-card {
                padding: 0.75rem;
            }

            .stat-number {
                font-size: 1.25rem;
            }

            .journal-card {
                padding: 0.75rem;
            }

            .table th,
            .table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.8rem;
            }

            .btn {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }

            .btn-small {
                padding: 0.4rem 0.75rem;
                font-size: 0.75rem;
            }

            .sidebar {
                width: 100%;
            }

            .sidebar-header {
                padding: 0 1rem 1.5rem;
            }

            .nav-menu {
                padding: 0 0.5rem;
            }

            .nav-link {
                padding: 0.6rem 0.75rem;
                font-size: 0.9rem;
            }
        }

        /* Landscape phone orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            .main-content {
                padding-top: 1rem;
            }

            .mobile-menu-btn {
                top: 0.5rem;
                left: 0.5rem;
                padding: 0.5rem;
            }
        }

        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            .nav-link {
                padding: 1rem;
            }

            .btn {
                padding: 0.875rem 1.5rem;
                min-height: 44px;
            }

            .form-input,
            .form-textarea,
            .form-select {
                padding: 1rem;
                font-size: 16px;
                /* Prevents zoom on iOS */
            }
        }
    </style>
</head>

<body>
    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">Journal Platform</div>
                <div class="user-info">
                    <div class="user-avatar">JD</div>
                    <div class="user-details">
                        <p><?= htmlspecialchars($user['first_name']) ?></p>
                        <p>Research Scholar</p>
                    </div>
                </div>
            </div>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a class="nav-link active" onclick="showSection('dashboard')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="showSection('articles')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                        </svg>
                        My Articles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="showSection('submit')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                        </svg>
                        Submit New Article
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="showSection('journals')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19Z" />
                        </svg>
                        Journal Subscriptions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="showSection('notifications')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M21,19V20H3V19L5,17V11C5,7.9 7.03,5.17 10,4.29C10,4.19 10,4.1 10,4A2,2 0 0,1 12,2A2,2 0 0,1 14,4C14,4.1 14,4.19 14,4.29C16.97,5.17 19,7.9 19,11V17L21,19M14,21A2,2 0 0,1 12,23A2,2 0 0,1 10,21" />
                        </svg>
                        Notifications
                        <span class="notification-count">3</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="showSection('profile')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" />
                        </svg>
                        My Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="showSection('support')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M11,18H13V16H11V18M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,6A4,4 0 0,0 8,10H10A2,2 0 0,1 12,8A2,2 0 0,1 14,10C14,12 11,11.75 11,15H13C13,12.75 16,12.5 16,10A4,4 0 0,0 12,6Z" />
                        </svg>
                        Help & Support
                    </a>
                </li>
                <li class="nav-item"
                    style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <a class="nav-link" href="logout.php">
                        <svg class="nav-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z" />
                        </svg>
                        Logout
                    </a>
                </li>

            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Dashboard Section -->
            <section id="dashboard" class="content-section active">
                <div class="page-header">
                    <h1 class="page-title">Welcome back,<?= htmlspecialchars($user['first_name']) ?>
                    </h1>
                    <p class="page-subtitle">Here's an overview of your publication activities</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Total Articles Submitted</div>
                    </div>
                    <div class="stat-card secondary">
                        <div class="stat-number">8</div>
                        <div class="stat-label">Articles Approved</div>
                    </div>
                    <div class="stat-card accent">
                        <div class="stat-number">2</div>
                        <div class="stat-label">Articles Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">5</div>
                        <div class="stat-label">Journals Joined</div>
                    </div>
                    <div class="stat-card secondary">
                        <div class="stat-number">247</div>
                        <div class="stat-label">Total Citations</div>
                    </div>
                    <div class="stat-card accent">
                        <div class="stat-number">1,523</div>
                        <div class="stat-label">Article Views</div>
                    </div>
                </div>

                <div class="table-container">
                    <h3 style="padding: 1.5rem 1.5rem 0; color: var(--primary-color);">Recent Activity</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Article Title</th>
                                <th>Journal</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Machine Learning in Healthcare</strong><br>
                                    <small style="color: var(--text-light);">AI applications in medical
                                        diagnosis</small>
                                </td>
                                <td>AI Medical Journal</td>
                                <td><span class="status-badge status-approved">Approved</span></td>
                                <td>Dec 1, 2024</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Data Privacy in Digital Health</strong><br>
                                    <small style="color: var(--text-light);">Privacy concerns in health tech</small>
                                </td>
                                <td>Tech Ethics Review</td>
                                <td><span class="status-badge status-pending">Under Review</span></td>
                                <td>Nov 28, 2024</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Blockchain Applications</strong><br>
                                    <small style="color: var(--text-light);">Decentralized systems research</small>
                                </td>
                                <td>Crypto Research</td>
                                <td><span class="status-badge status-rejected">Rejected</span></td>
                                <td>Nov 25, 2024</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- My Articles Section -->
            <section id="articles" class="content-section">
                <div class="page-header">
                    <h1 class="page-title">My Articles</h1>
                    <p class="page-subtitle">Manage all your submitted articles</p>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Journal</th>
                                <th>Submission Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Machine Learning in Healthcare</strong><br>
                                    <small style="color: var(--text-light);">AI applications in medical
                                        diagnosis</small>
                                </td>
                                <td>AI Medical Journal</td>
                                <td>Nov 15, 2024</td>
                                <td><span class="status-badge status-approved">Approved</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-small">View</button>
                                        <button class="btn btn-outline btn-small">Download</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Data Privacy in Digital Health</strong><br>
                                    <small style="color: var(--text-light);">Privacy concerns in health tech</small>
                                </td>
                                <td>Tech Ethics Review</td>
                                <td>Nov 28, 2024</td>
                                <td><span class="status-badge status-pending">Under Review</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-small">Edit</button>
                                        <button class="btn btn-outline btn-small">Withdraw</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Blockchain Applications</strong><br>
                                    <small style="color: var(--text-light);">Decentralized systems research</small>
                                </td>
                                <td>Crypto Research</td>
                                <td>Nov 25, 2024</td>
                                <td><span class="status-badge status-rejected">Rejected</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-small">View Feedback</button>
                                        <button class="btn btn-outline btn-small">Resubmit</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Submit New Article Section -->
            <section id="submit" class="content-section">
                <div class="page-header">
                    <h1 class="page-title">Submit New Article</h1>
                    <p class="page-subtitle">Submit your manuscript for publication</p>
                </div>

                <div class="form-container">
                    <form id="submitForm" method="POST" enctype="multipart/form-data" action="submit_article.php">
                        <!-- Article Title -->
                        <div class="form-group">
                            <label class="form-label" for="title">Article Title *</label>
                            <input type="text" id="title" name="title" class="form-input"
                                placeholder="Enter your article title" required>
                        </div>

                        <!-- Abstract -->
                        <div class="form-group">
                            <label class="form-label" for="abstract">Abstract *</label>
                            <textarea id="abstract" name="abstract" class="form-textarea"
                                placeholder="Provide a brief abstract of your article" required></textarea>
                        </div>

                        <!-- Journal select -->
                        <div class="form-group">
                            <label class="form-label" for="journal">Select Journal *</label>
                            <select id="journal" name="journal" class="form-select" required>
                                <option value="">Choose a journal</option>
                                <option value="Sahel Analyst: Journal of Management Sciences">Sahel Analyst: Journal of
                                    Management Sciences</option>
                                <option value="Journal of Resources & Economic Development (JRED)">Journal of Resources
                                    & Economic Development (JRED)</option>
                                <option value="African Journal of Management">African Journal of Management</option>
                            </select>
                        </div>

                        <!-- Manuscript file upload -->
                        <div class="form-group">
                            <label class="form-label" for="manuscript">Upload Manuscript *</label>
                            <input type="file" id="manuscript" name="manuscript" accept=".pdf,.doc,.docx" required>
                            <p style="font-size: 0.8rem; color: var(--text-light);">PDF, DOC, DOCX (Max 10MB)</p>
                        </div>

                        <!-- Keywords -->
                        <div class="form-group">
                            <label class="form-label" for="keywords">Keywords</label>
                            <input type="text" id="keywords" name="keywords" class="form-input"
                                placeholder="Enter keywords separated by commas">
                        </div>

                        <!-- Author Names -->
                        <div class="form-group">
                            <label class="form-label" for="author_names">Author Names *</label>
                            <textarea id="author_names" name="author_names" class="form-textarea"
                                placeholder="List all authors with their affiliations" required></textarea>
                        </div>

                        <!-- Submission Guidelines -->
                        <div
                            style="background: var(--bg-lighter); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                            <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">📋 Submission Guidelines
                            </h4>
                            <ul style="color: var(--text-light); font-size: 0.9rem; margin-left: 1rem;">
                                <li>Manuscripts should be original and not published elsewhere</li>
                                <li>Follow the journal's formatting guidelines</li>
                                <li>Include proper citations and references</li>
                                <li>Ensure ethical compliance and data privacy</li>
                            </ul>
                        </div>

                        <!-- Buttons -->
                        <div class="btn-group">
                            <button type="submit" name="action" value="submit" class="btn btn-primary">Submit
                                Article</button>
                            <button type="submit" name="action" value="draft" class="btn btn-outline">Save as
                                Draft</button>
                        </div>
                    </form>

                </div>
            </section>

            <!-- Journal Subscriptions Section -->
            <section id="journals" class="content-section">
                <div class="page-header">
                    <h1 class="page-title">Journal Subscriptions</h1>
                    <p class="page-subtitle">Discover and subscribe to journals in your field</p>
                </div>

                <div class="journal-grid">
                    <div class="journal-card">
                        <h3 class="journal-title">AI Medical Journal</h3>
                        <p class="journal-description">Leading publication in artificial intelligence applications in
                            healthcare and medical research.</p>
                        <div class="journal-meta">
                            <span>Impact Factor: 4.2</span>
                            <button class="btn btn-secondary btn-small">Subscribed</button>
                        </div>
                    </div>

                    <div class="journal-card">
                        <h3 class="journal-title">Tech Ethics Review</h3>
                        <p class="journal-description">Exploring ethical implications of emerging technologies and
                            digital transformation.</p>
                        <div class="journal-meta">
                            <span>Impact Factor: 3.8</span>
                            <button class="btn btn-primary btn-small">Subscribe</button>
                        </div>
                    </div>

                    <div class="journal-card">
                        <h3 class="journal-title">Crypto Research</h3>
                        <p class="journal-description">Cutting-edge research in blockchain technology and cryptocurrency
                            systems.</p>
                        <div class="journal-meta">
                            <span>Impact Factor: 3.5</span>
                            <button class="btn btn-primary btn-small">Subscribe</button>
                        </div>
                    </div>

                    <div class="journal-card">
                        <h3 class="journal-title">Data Science Today</h3>
                        <p class="journal-description">Latest developments in data science, machine learning, and
                            statistical analysis.</p>
                        <div class="journal-meta">
                            <span>Impact Factor: 4.0</span>
                            <button class="btn btn-secondary btn-small">Subscribed</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Notifications Section -->
            <section id="notifications" class="content-section">
                <div class="page-header">
                    <h1 class="page-title">Notifications</h1>
                    <p class="page-subtitle">Stay updated with your publication activities</p>
                </div>

                <div class="notification-item unread">
                    <div class="notification-title">📝 Article Approved: "Machine Learning in Healthcare"</div>
                    <p>Your article has been approved for publication in AI Medical Journal.</p>
                    <div class="notification-time">2 hours ago</div>
                </div>

                <div class="notification-item unread">
                    <div class="notification-title">💬 Review Comments Available</div>
                    <p>Reviewer comments are now available for "Data Privacy in Digital Health".</p>
                    <div class="notification-time">1 day ago</div>
                </div>

                <div class="notification-item">
                    <div class="notification-title">📚 New Journal Edition Released</div>
                    <p>Tech Ethics Review has published its December 2024 edition.</p>
                    <div class="notification-time">3 days ago</div>
                </div>

                <div class="notification-item unread">
                    <div class="notification-title">⏰ Submission Deadline Reminder</div>
                    <p>Reminder: Crypto Research special issue deadline is approaching (Dec 15, 2024).</p>
                    <div class="notification-time">5 days ago</div>
                </div>
            </section>

            <!-- Profile Section -->
            <section id="profile" class="content-section">
                <div class="page-header">
                    <h1 class="page-title">My Profile</h1>
                    <p class="page-subtitle">Manage your account information and settings</p>
                </div>

                <div class="form-container">
                    <form id="profileForm">
                        <div class="profile-grid">
                            <div class="profile-avatar-section">
                                <div class="user-avatar profile-avatar">JD</div>
                                <button type="button" class="btn btn-outline">Change Photo</button>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-input" value="Dr. John Doe">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-input" value="john.doe@university.edu">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Institution</label>
                                    <input type="text" class="form-input" value="Stanford University">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Designation</label>
                                    <input type="text" class="form-input" value="Research Scholar">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Bio</label>
                                    <textarea
                                        class="form-textarea">Dr. John Doe is a research scholar specializing in artificial intelligence and machine learning applications in healthcare.</textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">ORCID ID</label>
                                    <input type="text" class="form-input" placeholder="0000-0000-0000-0000">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">LinkedIn Profile</label>
                                    <input type="url" class="form-input" placeholder="https://linkedin.com/in/johndoe">
                                </div>

                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                    <button type="button" class="btn btn-outline">Change Password</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Support Section -->
            <section id="support" class="content-section">
                <div class="page-header">
                    <h1 class="page-title">Help & Support</h1>
                    <p class="page-subtitle">Get assistance with your publication journey</p>
                </div>

                <div class="support-grid">
                    <div class="form-container">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">💬 Contact Support</h3>
                        <form id="supportForm">
                            <div class="form-group">
                                <label class="form-label">Subject</label>
                                <select class="form-select">
                                    <option>Technical Issue</option>
                                    <option>Submission Question</option>
                                    <option>Account Problem</option>
                                    <option>General Inquiry</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Message</label>
                                <textarea class="form-textarea"
                                    placeholder="Describe your issue or question"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>

                    <div class="form-container">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">❓ Frequently Asked Questions</h3>

                        <div class="faq-item">
                            <h4 class="faq-question">How do I submit an article?</h4>
                            <p class="faq-answer">Use the "Submit New Article" section to upload your manuscript and
                                fill in the required details.</p>
                        </div>

                        <div class="faq-item">
                            <h4 class="faq-question">What file formats are accepted?</h4>
                            <p class="faq-answer">We accept PDF, DOC, and DOCX files up to 10MB in size.</p>
                        </div>

                        <div class="faq-item">
                            <h4 class="faq-question">How long does the review process take?</h4>
                            <p class="faq-answer">Review times vary by journal, typically 2-8 weeks depending on the
                                publication.</p>
                        </div>

                        <div class="faq-item">
                            <h4 class="faq-question">Can I edit my submission after submitting?</h4>
                            <p class="faq-answer">You can edit submissions that haven't entered the review process yet.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Navigation functionality
        function showSection(sectionId) {
            // Hide all sections
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => section.classList.remove('active'));

            // Show selected section
            document.getElementById(sectionId).classList.add('active');

            // Update navigation
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => link.classList.remove('active'));
            event.target.classList.add('active');

            // Close mobile sidebar
            closeSidebar();
        }

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');

            // Prevent body scroll when sidebar is open
            if (sidebar.classList.contains('open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Form submissions
        document.getElementById('submitForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Article submitted successfully! You will receive a confirmation email shortly.');
        });

        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Profile updated successfully!');
        });

        document.getElementById('supportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Support ticket created! We will respond within 24 hours.');
        });

        // File upload handling
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileUpload = document.querySelector('.file-upload');
                fileUpload.innerHTML = `
                    <p><strong>📄 Selected:</strong> ${file.name}</p>
                    <p style="font-size: 0.8rem; color: var(--text-light);">Click to change file</p>
                `;
            }
        });

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                alert('Logged out successfully!');
                // In a real application, this would redirect to login page
            }
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });

        // Initialize tooltips and other interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers for table actions
            const actionButtons = document.querySelectorAll('.table .btn');
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const action = this.textContent.trim();
                    alert(`${action} functionality would be implemented here.`);
                });
            });

            // Add click handlers for journal subscription buttons
            const subscribeButtons = document.querySelectorAll('.journal-card .btn');
            subscribeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (this.textContent.trim() === 'Subscribe') {
                        this.textContent = 'Subscribed';
                        this.className = 'btn btn-secondary btn-small';
                        alert('Successfully subscribed to journal!');
                    } else {
                        this.textContent = 'Subscribe';
                        this.className = 'btn btn-primary btn-small';
                        alert('Unsubscribed from journal.');
                    }
                });
            });

            // Touch event handling for better mobile experience
            let touchStartY = 0;
            let touchEndY = 0;

            document.addEventListener('touchstart', function(e) {
                touchStartY = e.changedTouches[0].screenY;
            });

            document.addEventListener('touchend', function(e) {
                touchEndY = e.changedTouches[0].screenY;
                handleSwipe();
            });

            function handleSwipe() {
                const swipeThreshold = 50;
                const sidebar = document.getElementById('sidebar');

                if (touchEndY < touchStartY - swipeThreshold) {
                    // Swipe up - could be used for additional functionality
                }

                if (touchEndY > touchStartY + swipeThreshold) {
                    // Swipe down - could be used for additional functionality
                }

                if (touchStartY < touchEndY - swipeThreshold && window.innerWidth <= 768) {
                    // Swipe right - open sidebar
                    if (!sidebar.classList.contains('open')) {
                        toggleSidebar();
                    }
                }

                if (touchStartY > touchEndY + swipeThreshold && window.innerWidth <= 768) {
                    // Swipe left - close sidebar
                    if (sidebar.classList.contains('open')) {
                        closeSidebar();
                    }
                }
            }
        });

        // Prevent zoom on double tap for iOS
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>
</body>

</html>
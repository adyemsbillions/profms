<?php
session_start();
require('db.php'); // connect to DB once
include('cal_call.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Fetch user info
$stmtUser = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();
$stmtUser->close();

// Fetch recent activity (latest 2 articles)
$stmtArticles = $conn->prepare("
    SELECT title, abstract, journal, status, submission_date 
    FROM articles 
    WHERE submitted_by = ? 
    ORDER BY submission_date DESC 
    LIMIT 2
");
$stmtArticles->bind_param("i", $user_id);
$stmtArticles->execute();
$articlesResult = $stmtArticles->get_result();

// Fetch all articles for My Articles section
$stmtAllArticles = $conn->prepare("
    SELECT id, title, abstract, journal, submission_date, status 
    FROM articles 
    WHERE submitted_by = ? 
    ORDER BY submission_date DESC
");
$stmtAllArticles->bind_param("i", $user_id);
$stmtAllArticles->execute();
$allArticlesResult = $stmtAllArticles->get_result();

function formatStatusBadge($status)
{
    $classMap = [
        'draft' => 'status-draft',
        'submitted' => 'status-pending',
        'under_review' => 'status-pending',
        'revision' => 'status-revision',
        'approved' => 'status-approved',
        'published' => 'status-approved',
        'rejected' => 'status-rejected',
    ];
    $class = $classMap[$status] ?? 'status-pending';
    return "<span class='status-badge $class'>" . ucfirst(str_replace('_', ' ', $status)) . "</span>";
}

// Helper function to safely output HTML
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Dashboard</title>
    <style>
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
        --success-color: #059669;
        --error-color: #dc2626;
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
    }

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
        font-size: 1.2rem;
    }

    .mobile-menu-btn:hover {
        background: var(--primary-light);
    }

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

    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 2rem;
        width: calc(100% - 280px);
    }

    .content-section {
        display: none;
    }

    .content-section.active {
        display: block;
    }

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

    .status-draft {
        background: #e5e7eb;
        color: #374151;
    }

    .status-revision {
        background: #dbeafe;
        color: #1e40af;
    }

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
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
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

    .article-card {
        background-color: var(--bg-color);
        padding: 0.75rem;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .article-card__field {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .article-card__label {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-light);
    }

    .article-card__value {
        font-size: 0.85rem;
        color: var(--text-color);
    }

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
            width: 100%;
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

        .article-card {
            padding: 0.5rem;
        }

        .article-card__label {
            font-size: 0.75rem;
        }

        .article-card__value {
            font-size: 0.8rem;
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

        .btn {
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
        }

        .btn-small {
            padding: 0.4rem 0.75rem;
            font-size: 0.75rem;
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

    @media (min-width: 640px) {
        .article-card {
            display: grid;
            grid-template-columns: 2fr 2fr 1fr 1fr 1fr;
            align-items: center;
            gap: 0.75rem;
        }

        .article-card__label {
            display: none;
        }

        .article-card--header {
            background-color: var(--bg-lighter);
            font-weight: 600;
            color: var(--text-color);
        }
    }
    </style>
</head>

<body>
    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="dashboard-container">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">Sahel Analyst</div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo substr(e($user['first_name']), 0, 1) . substr(e($user['last_name']), 0, 1); ?></div>
                    <div class="user-details">
                        <p><?php echo e($user['first_name']); ?></p>
                        <p>Unimaid Scholar</p>
                    </div>
                </div>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active" data-section="dashboard">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="articles">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                        </svg>
                        My Articles
                    </a>
                </li>
                <li class="nav-item">
                    <a href="new_article.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                        </svg>
                        Submit New Article
                    </a>
                </li>
                <li class="nav-item">
                    <a href="payment.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19Z" />
                        </svg>
                        Journal Subscriptions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="notifications.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M21,19V20H3V19L5,17V11C5,7.9 7.03,5.17 10,4.29C10,4.19 10,4.1 10,4A2,2 0 0,1 12,2A2,2 0 0,1 14,4C14,4.1 14,4.19 14,4.29C16.97,5.17 19,7.9 19,11V17L21,19M14,21A2,2 0 0,1 12,23A2,2 0 0,1 10,21" />
                        </svg>
                        Notifications
                        <span class="notification-count">.</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="update_profile.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" />
                        </svg>
                        My Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a href="help.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M11,18H13V16H11V18M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,6A4,4 0 0,0 8,10H10A2,2 0 0,1 12,8A2,2 0 0,1 14,10C14,12 11,11.75 11,15H13C13,12.75 16,12.5 16,10A4,4 0 0,0 12,6Z" />
                        </svg>
                        Help & Support
                    </a>
                </li>
                <li class="nav-item"
                    style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <a href="logout.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z" />
                        </svg>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <section id="dashboard" class="content-section active">
                <div class="page-header">
                    <h1 class="page-title">Welcome back, <?php echo e($user['first_name']); ?></h1>
                    <p class="page-subtitle">Here's an overview of your publication activities</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?= $total_articles ?></div>
                        <div class="stat-label">Total articles</div>
                    </div>
                    <div class="stat-card secondary">
                        <div class="stat-number"><?= $total_pending ?></div>
                        <div class="stat-label">Pending/Draft</div>
                    </div>
                    <div class="stat-card accent">
                        <div class="stat-number"><?= $total_approved ?></div>
                        <div class="stat-label">Approved/Published</div>
                    </div>
                    <!-- <div class="stat-card">
                        <div class="stat-number"><?= $reviewers ?></div>
                        <div class="stat-label">Reviewers</div>
                    </div> -->
                </div>

                <div style="max-width: 1000px; margin: 0 auto; padding: 1rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e3a8a; margin-bottom: 1rem; padding: 0;">
                        Recent Activity</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php
                        if ($articlesResult->num_rows === 0) {
                            echo "<div style='background-color: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); text-align: center; color: #6b7280; font-size: 0.85rem;'>No recent activity.</div>";
                        } else {
                            echo "<div style='display: none; background-color: #f3f4f6; padding: 0.75rem; border-radius: 6px; border: 1px solid #e5e7eb; font-weight: 600; color: #1f2937; font-size: 0.85rem; display: grid; grid-template-columns: 2fr 2fr 1fr 1fr; gap: 0.75rem; align-items: center;'>
                              
                            </div>";
                            while ($article = $articlesResult->fetch_assoc()) {
                                echo "<div style='background-color: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; gap: 0.5rem;'>";
                                echo "<div style='display: flex; flex-direction: column; gap: 0.2rem;'>
                                        <span style='font-size: 0.8rem; font-weight: 500; color: #6b7280;'>Article Title</span>
                                        <div>
                                            <strong style='font-size: 0.85rem; color: #1f2937;'>" . e($article['title']) . "</strong>
                                            <br>
                                            <small style='color: #6b7280; font-size: 0.8rem;'>" . e(substr($article['abstract'], 0, 60)) . "...</small>
                                        </div>
                                    </div>";
                                echo "<div style='display: flex; flex-direction: column; gap: 0.2rem;'>
                                        <span style='font-size: 0.8rem; font-weight: 500; color: #6b7280;'>Journal</span>
                                        <span style='font-size: 0.85rem; color: #1f2937;'>" . e($article['journal']) . "</span>
                                    </div>";
                                echo "<div style='display: flex; flex-direction: column; gap: 0.2rem;'>
                                        <span style='font-size: 0.8rem; font-weight: 500; color: #6b7280;'>Status</span>
                                        <span>" . formatStatusBadge($article['status']) . "</span>
                                    </div>";
                                echo "<div style='display: flex; flex-direction: column; gap: 0.2rem;'>
                                        <span style='font-size: 0.8rem; font-weight: 500; color: #6b7280;'>Date</span>
                                        <span style='font-size: 0.85rem; color: #1f2937;'>" . date("M d, Y", strtotime($article['submission_date'])) . "</span>
                                    </div>";
                                echo "</div>";
                            }
                        }
                        $articlesResult->close();
                        ?>
                    </div>
                </div>
            </section>

            <section id="articles" class="content-section">
                <div class="page-header">
                    <h1 class="page-title">My Articles</h1>
                    <p class="page-subtitle">Manage all your submitted articles</p>
                </div>

                <div style="max-width: 1000px; margin: 0 auto; padding: 1rem;">
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php
                        if ($allArticlesResult->num_rows === 0) {
                            echo "<div style='background-color: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); text-align: center; color: #6b7280; font-size: 0.85rem;'>No articles found.</div>";
                        } else {
                            echo "<div class='article-card article-card--header' style='background-color: #f3f4f6; padding: 0.75rem; border-radius: 6px; border: 1px solid #e5e7eb; font-weight: 600; color: #1f2937; font-size: 0.85rem; display: grid; grid-template-columns: 2fr 2fr 1fr 1fr 1fr; gap: 0.75rem; align-items: center;'>
                                <span>Title</span>
                                <span>Journal</span>
                                <span>Submission Date</span>
                                <span>Status</span>
                                <span>Actions</span>
                            </div>";
                            while ($article = $allArticlesResult->fetch_assoc()) {
                                echo "<div class='article-card' style='background-color: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid #e5e7eb; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);'>";
                                echo "<div class='article-card__field'>
                                        <span class='article-card__label'>Title</span>
                                        <span class='article-card__value'>" . e($article['title']) . "</span>
                                    </div>";
                                echo "<div class='article-card__field'>
                                        <span class='article-card__label'>Journal</span>
                                        <span class='article-card__value'>" . e($article['journal']) . "</span>
                                    </div>";
                                echo "<div class='article-card__field'>
                                        <span class='article-card__label'>Submission Date</span>
                                        <span class='article-card__value'>" . date("M d, Y", strtotime($article['submission_date'])) . "</span>
                                    </div>";
                                echo "<div class='article-card__field'>
                                        <span class='article-card__label'>Status</span>
                                        <span>" . formatStatusBadge($article['status']) . "</span>
                                    </div>";
                                echo "<div class='article-card__field action-buttons'>
                                        <span class='article-card__label'>Actions</span>
                                        <a href='manage_articles.php?edit=" . $article['id'] . "' class='btn btn-outline btn-small'>Edit</a>
                                    </div>";
                                echo "</div>";
                            }
                        }
                        $allArticlesResult->close();
                        ?>
                    </div>
                </div>
            </section>




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
    function showSection(sectionId) {
        const sections = document.querySelectorAll('.content-section');
        sections.forEach(section => section.classList.remove('active'));

        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.add('active');
        }

        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => link.classList.remove('active'));

        const targetLink = document.querySelector(`.nav-link[data-section="${sectionId}"]`);
        if (targetLink) {
            targetLink.classList.add('active');
        }

        closeSidebar();
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');

        document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
    }

    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const sectionId = this.getAttribute('data-section');
                if (sectionId) {
                    e.preventDefault();
                    showSection(sectionId);
                }
            });
        });

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

        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });

        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });

        document.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            const swipeThreshold = 50;
            const sidebar = document.getElementById('sidebar');

            if (touchStartX < touchEndX - swipeThreshold && window.innerWidth <= 768 && !sidebar
                .classList.contains('open')) {
                toggleSidebar();
            }

            if (touchStartX > touchEndX + swipeThreshold && window.innerWidth <= 768 && sidebar
                .classList.contains('open')) {
                closeSidebar();
            }
        });

        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);

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
    });
    </script>
</body>

</html>
<?php
$stmtArticles->close();
$stmtAllArticles->close();
$conn->close();
?>
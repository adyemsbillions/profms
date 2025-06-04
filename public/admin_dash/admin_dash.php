<?php
session_start();
include('stats.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
?>
<?php
// DB connection setup (adjust as needed)
include('../userdash/db.php');

// Query the total users count
$total_users = 0;
$result = $conn->query("SELECT COUNT(id) AS total FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    $total_users = (int)$row['total'];
}
$sql = "SELECT COUNT(id) AS total FROM articles WHERE status NOT IN ('approved', 'published')";
$result = $conn->query($sql);
$total_review_articles = 0;
if ($result) {
    $row = $result->fetch_assoc();
    $total_review_articles = (int)$row['total'];
}

$conn->close();
?>

<?php
require_once 'stats.php'; // Include stats for users, articles, inquiries, approved, rejected

// Initialize database connection for trends
$host = "localhost";
$db   = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("DB Connection failed: " . $conn->connect_error);
    $submission_trends = [];
    $approved_trends = [];
} else {
    // Fetch submission trends data (grouped by year)
    $submission_trends = [];
    try {
        $stmt_trends = $conn->prepare("SELECT YEAR(submission_date) AS submission_year, COUNT(id) AS article_count FROM articles WHERE submission_date IS NOT NULL GROUP BY submission_year ORDER BY submission_year");
        if ($stmt_trends) {
            $stmt_trends->execute();
            $result_trends = $stmt_trends->get_result();
            $submission_trends = $result_trends->fetch_all(MYSQLI_ASSOC);
            $stmt_trends->close();
        } else {
            error_log("Prepare failed for submission trends: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Submission trends error: " . $e->getMessage());
    }

    // Fetch approved articles trends data (grouped by year)
    $approved_trends = [];
    try {
        $stmt_approved = $conn->prepare("SELECT YEAR(submission_date) AS submission_year, COUNT(id) AS article_count FROM articles WHERE submission_date IS NOT NULL AND status = 'approved' GROUP BY submission_year ORDER BY submission_year");
        if ($stmt_approved) {
            $stmt_approved->execute();
            $result_approved = $stmt_approved->get_result();
            $approved_trends = $result_approved->fetch_all(MYSQLI_ASSOC);
            $stmt_approved->close();
        } else {
            error_log("Prepare failed for approved trends: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Approved trends error: " . $e->getMessage());
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Journal Platform</title>
    <style>
        /* Base Styles */
        :root {
            --primary-color: #1e3a8a;
            --primary-light: #3b82f6;
            --secondary-color: #059669;
            --secondary-light: #10b981;
            --accent-color: #f59e0b;
            --accent-light: #fbbf24;
            --danger-color: #dc2626;
            --danger-light: #ef4444;
            --warning-color: #d97706;
            --warning-light: #f59e0b;
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

        .admin-badge {
            background: var(--danger-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--danger-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
        }

        .admin-details h3 {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .admin-details p {
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
            background: var(--danger-color);
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
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

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
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
            position: relative;
            overflow: hidden;
        }

        .stat-card.secondary {
            border-left-color: var(--secondary-color);
        }

        .stat-card.accent {
            border-left-color: var(--accent-color);
        }

        .stat-card.danger {
            border-left-color: var(--danger-color);
        }

        .stat-card.warning {
            border-left-color: var(--warning-color);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-light);
            color: white;
        }

        .stat-icon.secondary {
            background: var(--secondary-color);
        }

        .stat-icon.accent {
            background: var(--accent-color);
        }

        .stat-icon.danger {
            background: var(--danger-color);
        }

        .stat-icon.warning {
            background: var(--warning-color);
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

        .stat-change {
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-change.positive {
            color: var(--secondary-color);
        }

        .stat-change.negative {
            color: var(--danger-color);
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: var(--danger-light);
        }

        .btn-warning {
            background: var(--warning-color);
            color: white;
        }

        .btn-warning:hover {
            background: var(--warning-light);
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

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding: 0.5rem 0.75rem 0.5rem 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.85rem;
            width: 200px;
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            fill: var(--text-light);
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

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
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

        .status-suspended {
            background: #fde68a;
            color: #92400e;
        }

        /* Charts and Analytics */
        .chart-container {
            background: var(--bg-color);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .chart-placeholder {
            height: 300px;
            background: var(--bg-lighter);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-style: italic;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: var(--bg-color);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }

        .card-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .card-content {
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }

        .alert-info {
            background: #eff6ff;
            border-left-color: var(--primary-color);
            color: var(--primary-color);
        }

        .alert-success {
            background: #f0fdf4;
            border-left-color: var(--secondary-color);
            color: var(--secondary-color);
        }

        .alert-warning {
            background: #fffbeb;
            border-left-color: var(--warning-color);
            color: var(--warning-color);
        }

        .alert-danger {
            background: #fef2f2;
            border-left-color: var(--danger-color);
            color: var(--danger-color);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--bg-color);
            padding: 2rem;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            .cards-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .form-row {
                grid-template-columns: 1fr;
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
                flex-direction: column;
                align-items: flex-start;
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

            .cards-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .info-card {
                padding: 1rem;
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

            .search-input {
                width: 100%;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .table-actions {
                width: 100%;
                justify-content: space-between;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
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

            .info-card {
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

            .modal-content {
                padding: 1rem;
                width: 95%;
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
            }
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            margin-bottom: 1rem;
        }

        .chart-title {
            color: var(--primary-color);
            font-weight: 600;
        }

        canvas {
            max-width: 100%;
            height: auto;
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 1rem;
                font-size: 1rem;
            }

            .chart-container {
                padding: 1rem;
            }

            /* Stack charts vertically on small screens */
            .chart-grid {
                grid-template-columns: 1fr !important;
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
                <div class="logo">Admin Panel</div>
                <div class="admin-badge">Administrator</div>
                <div class="admin-info">
                    <div class="admin-avatar">AD</div>
                    <div class="admin-details">

                        <p><?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                    </div>
                </div>
            </div>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a class="nav-link active" onclick="showSection('dashboard')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                        </svg>
                        Dashboard Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_users.php">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zm4 18v-6h2.5l-2.54-7.63A1.5 1.5 0 0 0 18.54 7H17c-.8 0-1.54.37-2 1l-3 4v6h2v7h3v-7h2z" />
                        </svg>
                        Authors Management
                        <span class="notification-count"><?php echo $total_users; ?></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="manage_articles.php">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19Z" />
                        </svg>
                        Journal Management
                        <span class="notification-count"><?php echo $total_articles; ?></span>
                    </a>
                </li>


                <li class="nav-item">
                    <a class="nav-link" href="manage_articles_status.php?status=submitted">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                        </svg>
                        Article Review
                        <span class="notification-count"><?php echo $total_review_articles; ?></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" onclick="showSection('analytics')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M22,21H2V3H4V19H6V17H10V19H12V16H16V19H18V17H22V21Z" />
                        </svg>
                        Analytics & Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="showSection('revenue')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M7,15H9C9,16.08 10.37,17 12,17C13.63,17 15,16.08 15,15C15,13.9 13.96,13.5 11.76,12.97C9.64,12.44 7,11.78 7,9C7,7.21 8.47,5.69 10.5,5.18V3H13.5V5.18C15.53,5.69 17,7.21 17,9H15C15,7.92 13.63,7 12,7C10.37,7 9,7.92 9,9C9,10.1 10.04,10.5 12.24,11.03C14.36,11.56 17,12.22 17,15C17,16.79 15.53,18.31 13.5,18.82V21H10.5V18.82C8.47,18.31 7,16.79 7,15Z" />
                        </svg>
                        Revenue & Billing
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="support_inquiries.php">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M12,2A3,3 0 0,1 15,5V11A3,3 0 0,1 12,14A3,3 0 0,1 9,11V5A3,3 0 0,1 12,2M19,11C19,14.53 16.39,17.44 13,17.93V21H11V17.93C7.61,17.44 5,14.53 5,11H7A5,5 0 0,0 12,16A5,5 0 0,0 17,11H19Z" />
                        </svg>
                        Support Tickets
                        <span class="notification-count"><?php echo $total_inquiries; ?></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" onclick="showSection('settings')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path
                                d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z" />
                        </svg>
                        System Settings
                    </a>
                </li>
                <li class="nav-item"
                    style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <a class="nav-link" onclick="logout()">
                        <svg class="nav-icon" viewBox="0 0 24 24">
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
            <!-- Dashboard Overview Section -->
            <section id="dashboard" class="content-section active">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard Overview</h1>
                        <p class="page-subtitle">System-wide statistics and recent activities</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-outline btn-small">Export Report</button>
                        <button class="btn btn-primary btn-small">System Health</button>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo htmlspecialchars($total_users); ?></div>
                        <div class="stat-label">Total Authors</div>
                        <div class="stat-change positive">↗ All time authors</div>
                    </div>
                    <div class="stat-card secondary">
                        <div class="stat-header">
                            <div class="stat-icon secondary">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo htmlspecialchars($total_articles); ?></div>
                        <div class="stat-label">All Articles</div>
                        <div class="stat-change positive">↗Total articles submited for all journals</div>
                    </div>
                    <div class="stat-card accent">
                        <div class="stat-header">
                            <div class="stat-icon accent">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo htmlspecialchars($total_rejected); ?></div>
                        <div class="stat-label">Articles been rejected</div>
                        <div class="stat-change positive">↗ All time</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-header">
                            <div class="stat-icon warning">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,7H13V9H11V7M11,11H13V17H11V11Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo htmlspecialchars($total_inquiries); ?></div>
                        <div class="stat-label">Pending Support Request</div>
                        <div class="stat-change negative">↗ Suport request Not Attended To</div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-header">
                            <div class="stat-icon danger">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12,2A3,3 0 0,1 15,5V11A3,3 0 0,1 12,14A3,3 0 0,1 9,11V5A3,3 0 0,1 12,2M19,11C19,14.53 16.39,17.44 13,17.93V21H11V17.93C7.61,17.44 5,14.53 5,11H7A5,5 0 0,0 12,16A5,5 0 0,0 17,11H19Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo htmlspecialchars($total_approved); ?></div>
                        <div class="stat-label">Total Approved articles</div>
                        <div class="stat-change negative">↗ All Time</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M7,15H9C9,16.08 10.37,17 12,17C13.63,17 15,16.08 15,15C15,13.9 13.96,13.5 11.76,12.97C9.64,12.44 7,11.78 7,9C7,7.21 8.47,5.69 10.5,5.18V3H13.5V5.18C15.53,5.69 17,7.21 17,9H15C15,7.92 13.63,7 12,7C10.37,7 9,7.92 9,9C9,10.1 10.04,10.5 12.24,11.03C14.36,11.56 17,12.22 17,15C17,16.79 15.53,18.31 13.5,18.82V21H10.5V18.82C8.47,18.31 7,16.79 7,15Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="stat-number">N47,892</div>
                        <div class="stat-label">Monthly Revenue</div>
                        <div class="stat-change positive">↗ +15% vs last month</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div class="chart-container">
                        <h3 class="chart-title">Overview Stats [better view on desktop mode]</h3>
                        <canvas id="overviewStatsChart"></canvas>
                    </div>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const overviewData = {
                            labels: ['Total Users', 'Total Articles', 'Total Inquiries', 'Total Rejected Articles',
                                'Total Approved Articles'
                            ],
                            datasets: [{
                                label: 'Count',
                                data: [10, 20, 5, 3, 12], // static test data
                                backgroundColor: ['#1e3a8a', '#059669', '#dc2626', '#f59e0b', '#3b82f6'],
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        };

                        const ctx = document.getElementById('overviewStatsChart').getContext('2d');

                        new Chart(ctx, {
                            type: 'bar',
                            data: overviewData,
                            options: {
                                responsive: true,
                                aspectRatio: window.innerWidth < 769 ? 0.5 : 2,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1
                                        },
                                        title: {
                                            display: true,
                                            text: 'Count'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Categories'
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    title: {
                                        display: true,
                                        text: 'Overview Stats'
                                    }
                                }
                            }
                        });
                    </script>

                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Recent Activities</h3>
                        <div class="table-actions">
                            <button class="btn btn-outline btn-small">View All</button>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>New article submission: "AI in Healthcare"</td>
                                <td>Dr. John Smith</td>
                                <td>Submission</td>
                                <td>2 hours ago</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>User registration: jane.doe@university.edu</td>
                                <td>Jane Doe</td>
                                <td>Registration</td>
                                <td>4 hours ago</td>
                                <td><span class="status-badge status-active">Active</span></td>
                            </tr>
                            <tr>
                                <td>Article approved: "Machine Learning Trends"</td>
                                <td>Dr. Mike Johnson</td>
                                <td>Review</td>
                                <td>6 hours ago</td>
                                <td><span class="status-badge status-approved">Approved</span></td>
                            </tr>
                            <tr>
                                <td>Support ticket created: Payment issue</td>
                                <td>Sarah Wilson</td>
                                <td>Support</td>
                                <td>8 hours ago</td>
                                <td><span class="status-badge status-pending">Open</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>


            <!-- Journal Management Section -->
            <section id="journals" class="content-section">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Journal Management</h1>
                        <p class="page-subtitle">Create and manage publication journals</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-outline btn-small">Export Data</button>
                        <button class="btn btn-primary btn-small" onclick="openModal('addJournalModal')">Create New
                            Journal</button>
                    </div>
                </div>

                <div class="cards-grid">
                    <div class="info-card">
                        <div class="card-header">
                            <h3 class="card-title">AI Medical Journal</h3>
                            <span class="status-badge status-active">Active</span>
                        </div>
                        <div class="card-content">
                            <p><strong>Editor:</strong> Dr. Sarah Johnson</p>
                            <p><strong>Impact Factor:</strong> 4.2</p>
                            <p><strong>Articles:</strong> 156 published</p>
                            <p><strong>Subscribers:</strong> 2,847</p>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-outline btn-small">Edit</button>
                            <button class="btn btn-secondary btn-small">View Articles</button>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="card-header">
                            <h3 class="card-title">Tech Ethics Review</h3>
                            <span class="status-badge status-active">Active</span>
                        </div>
                        <div class="card-content">
                            <p><strong>Editor:</strong> Dr. Michael Chen</p>
                            <p><strong>Impact Factor:</strong> 3.8</p>
                            <p><strong>Articles:</strong> 89 published</p>
                            <p><strong>Subscribers:</strong> 1,523</p>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-outline btn-small">Edit</button>
                            <button class="btn btn-secondary btn-small">View Articles</button>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="card-header">
                            <h3 class="card-title">Crypto Research</h3>
                            <span class="status-badge status-pending">Under Review</span>
                        </div>
                        <div class="card-content">
                            <p><strong>Editor:</strong> Dr. Alex Rodriguez</p>
                            <p><strong>Impact Factor:</strong> 3.5</p>
                            <p><strong>Articles:</strong> 67 published</p>
                            <p><strong>Subscribers:</strong> 892</p>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-outline btn-small">Edit</button>
                            <button class="btn btn-primary btn-small">Approve</button>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="card-header">
                            <h3 class="card-title">Data Science Today</h3>
                            <span class="status-badge status-inactive">Inactive</span>
                        </div>
                        <div class="card-content">
                            <p><strong>Editor:</strong> Dr. Lisa Wang</p>
                            <p><strong>Impact Factor:</strong> 4.0</p>
                            <p><strong>Articles:</strong> 234 published</p>
                            <p><strong>Subscribers:</strong> 3,156</p>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-outline btn-small">Edit</button>
                            <button class="btn btn-secondary btn-small">Reactivate</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Article Review Section -->
            <section id="articles" class="content-section">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Article Review Queue</h1>
                        <p class="page-subtitle">Manage article submissions and reviews</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-outline btn-small">Bulk Actions</button>
                        <button class="btn btn-primary btn-small">Assign Reviewers</button>
                    </div>
                </div>

                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="stat-card warning">
                        <div class="stat-number">47</div>
                        <div class="stat-label">Pending Review</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">23</div>
                        <div class="stat-label">Under Review</div>
                    </div>
                    <div class="stat-card secondary">
                        <div class="stat-number">156</div>
                        <div class="stat-label">Approved</div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Rejected</div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Article Queue</h3>
                        <div class="table-actions">
                            <div class="search-box">
                                <svg class="search-icon" viewBox="0 0 24 24">
                                    <path
                                        d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z" />
                                </svg>
                                <input type="text" class="search-input" placeholder="Search articles...">
                            </div>
                            <select class="form-select" style="width: auto;">
                                <option>All Status</option>
                                <option>Pending</option>
                                <option>Under Review</option>
                                <option>Approved</option>
                                <option>Rejected</option>
                            </select>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Article Title</th>
                                <th>Author</th>
                                <th>Journal</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Reviewer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Machine Learning in Healthcare Diagnostics</strong><br>
                                    <small style="color: var(--text-light);">AI applications for medical diagnosis and
                                        treatment</small>
                                </td>
                                <td>Dr. John Smith</td>
                                <td>AI Medical Journal</td>
                                <td>Dec 1, 2024</td>
                                <td><span class="status-badge status-pending">Pending Review</span></td>
                                <td>Not Assigned</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-small">View</button>
                                        <button class="btn btn-primary btn-small">Assign</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Blockchain Security Protocols</strong><br>
                                    <small style="color: var(--text-light);">Advanced cryptographic methods for
                                        blockchain</small>
                                </td>
                                <td>Dr. Sarah Wilson</td>
                                <td>Crypto Research</td>
                                <td>Nov 28, 2024</td>
                                <td><span class="status-badge status-approved">Under Review</span></td>
                                <td>Dr. Mike Chen</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-small">View</button>
                                        <button class="btn btn-secondary btn-small">Comments</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Ethics in AI Development</strong><br>
                                    <small style="color: var(--text-light);">Moral considerations in artificial
                                        intelligence</small>
                                </td>
                                <td>Dr. Emily Rodriguez</td>
                                <td>Tech Ethics Review</td>
                                <td>Nov 25, 2024</td>
                                <td><span class="status-badge status-approved">Approved</span></td>
                                <td>Dr. Lisa Wang</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-small">View</button>
                                        <button class="btn btn-secondary btn-small">Publish</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Quantum Computing Applications</strong><br>
                                    <small style="color: var(--text-light);">Future of quantum algorithms in
                                        computing</small>
                                </td>
                                <td>Dr. Robert Kim</td>
                                <td>Tech Research Quarterly</td>
                                <td>Nov 20, 2024</td>
                                <td><span class="status-badge status-rejected">Rejected</span></td>
                                <td>Dr. Alex Johnson</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-small">View</button>
                                        <button class="btn btn-danger btn-small">Feedback</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Analytics & Reports Section -->
            <section id="analytics" class="content-section">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Analytics & Reports</h1>
                        <p class="page-subtitle">Platform performance and usage statistics</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-outline btn-small">Download Report</button>
                        <button class="btn btn-primary btn-small">Schedule Report</button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">User Growth</h3>
                            <select class="form-select" style="width: auto;">
                                <option>Last 6 months</option>
                                <option>Last year</option>
                                <option>All time</option>
                            </select>
                        </div>
                        <div class="chart-placeholder">
                            📈 User registration and growth chart would be displayed here
                        </div>
                    </div>

                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Revenue Trends</h3>
                            <select class="form-select" style="width: auto;">
                                <option>Monthly</option>
                                <option>Quarterly</option>
                                <option>Yearly</option>
                            </select>
                        </div>
                        <div class="chart-placeholder">
                            💰 Revenue and subscription trends chart would be displayed here
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Article Publication Rate</h3>
                        </div>
                        <div class="chart-placeholder">
                            📊 Article submission and publication rate chart
                        </div>
                    </div>

                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Journal Performance</h3>
                        </div>
                        <div class="chart-placeholder">
                            📋 Journal-wise performance metrics chart
                        </div>
                    </div>
                </div>

                <div class="cards-grid">
                    <div class="info-card">
                        <div class="card-header">
                            <h3 class="card-title">📊 Key Metrics</h3>
                        </div>
                        <div class="card-content">
                            <p><strong>Average Review Time:</strong> 14 days</p>
                            <p><strong>Acceptance Rate:</strong> 68%</p>
                            <p><strong>User Retention:</strong> 85%</p>
                            <p><strong>Platform Uptime:</strong> 99.9%</p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="card-header">
                            <h3 class="card-title">🎯 Performance Goals</h3>
                        </div>
                        <div class="card-content">
                            <p><strong>Monthly Submissions:</strong> 1,500 (Goal: 1,200) ✅</p>
                            <p><strong>New Users:</strong> 250 (Goal: 300) ⚠️</p>
                            <p><strong>Revenue Target:</strong> $45K (Goal: $50K) ⚠️</p>
                            <p><strong>User Satisfaction:</strong> 4.8/5 (Goal: 4.5) ✅</p>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="card-header">
                            <h3 class="card-title">🔍 Top Performing Content</h3>
                        </div>
                        <div class="card-content">
                            <p><strong>Most Cited:</strong> "AI in Medical Diagnosis"</p>
                            <p><strong>Most Downloaded:</strong> "Blockchain Security"</p>
                            <p><strong>Trending Topic:</strong> Machine Learning</p>
                            <p><strong>Popular Journal:</strong> AI Medical Journal</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Revenue & Billing Section -->
            <section id="revenue" class="content-section">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Revenue & Billing</h1>
                        <p class="page-subtitle">Financial overview and subscription management</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-outline btn-small">Export Financial Data</button>
                        <button class="btn btn-primary btn-small">Generate Invoice</button>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">$47,892</div>
                        <div class="stat-label">Monthly Revenue</div>
                        <div class="stat-change positive">↗ +15% vs last month</div>
                    </div>
                    <div class="stat-card secondary">
                        <div class="stat-number">$142,567</div>
                        <div class="stat-label">Quarterly Revenue</div>
                        <div class="stat-change positive">↗ +8% vs last quarter</div>
                    </div>
                    <div class="stat-card accent">
                        <div class="stat-number">2,847</div>
                        <div class="stat-label">Active Subscriptions</div>
                        <div class="stat-change positive">↗ +12% growth</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number">$3,245</div>
                        <div class="stat-label">Outstanding Payments</div>
                        <div class="stat-change negative">↗ 23 overdue invoices</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Revenue Overview</h3>
                            <select class="form-select" style="width: auto;">
                                <option>Last 12 months</option>
                                <option>Last 6 months</option>
                                <option>Last 3 months</option>
                            </select>
                        </div>
                        <div class="chart-placeholder">
                            💹 Monthly revenue breakdown chart would be displayed here
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="card-header">
                            <h3 class="card-title">💳 Payment Methods</h3>
                        </div>
                        <div class="card-content">
                            <p><strong>Credit Cards:</strong> 78%</p>
                            <p><strong>Bank Transfer:</strong> 15%</p>
                            <p><strong>PayPal:</strong> 5%</p>
                            <p><strong>Other:</strong> 2%</p>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-outline btn-small">Payment Settings</button>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Recent Transactions</h3>
                        <div class="table-actions">
                            <div class="search-box">
                                <svg class="search-icon" viewBox="0 0 24 24">
                                    <path
                                        d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z" />
                                </svg>
                                <input type="text" class="search-input" placeholder="Search transactions...">
                            </div>
                            <button class="btn btn-outline btn-small">View All</button>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#TXN-2024-001</td>
                                <td>Dr. John Smith</td>
                                <td>Subscription</td>
                                <td>$29.99</td>
                                <td><span class="status-badge status-approved">Completed</span></td>
                                <td>Dec 1, 2024</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-small">View</button>
                                        <button class="btn btn-secondary btn-small">Invoice</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>#TXN-2024-002</td>
                                <td>Dr. Sarah Wilson</td>
                                <td>Article Fee</td>
                                <td>$199.00</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>Nov 30, 2024</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-small">View</button>
                                        <button class="btn btn-warning btn-small">Follow Up</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>#TXN-2024-003</td>
                                <td>Dr. Mike Johnson</td>
                                <td>Subscription</td>
                                <td>$49.99</td>
                                <td><span class="status-badge status-rejected">Failed</span></td>
                                <td>Nov 29, 2024</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-small">View</button>
                                        <button class="btn btn-danger btn-small">Retry</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Support Tickets Section -->

            <!-- System Settings Section -->
            <section id="settings" class="content-section">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">System Settings</h1>
                        <p class="page-subtitle">Configure platform settings and preferences</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-outline btn-small">Backup Settings</button>
                        <button class="btn btn-primary btn-small">Save Changes</button>
                    </div>
                </div>

                <div class="cards-grid">
                    <div class="form-container">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">🔧 General Settings</h3>
                        <form>
                            <div class="form-group">
                                <label class="form-label">Platform Name</label>
                                <input type="text" class="form-input" value="Journal Platform">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Admin Email</label>
                                <input type="email" class="form-input" value="admin@journalplatform.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Default Language</label>
                                <select class="form-select">
                                    <option>English</option>
                                    <option>Spanish</option>
                                    <option>French</option>
                                    <option>German</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Time Zone</label>
                                <select class="form-select">
                                    <option>UTC-8 (Pacific)</option>
                                    <option>UTC-5 (Eastern)</option>
                                    <option>UTC+0 (GMT)</option>
                                    <option>UTC+1 (CET)</option>
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="form-container">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">📧 Email Settings</h3>
                        <form>
                            <div class="form-group">
                                <label class="form-label">SMTP Server</label>
                                <input type="text" class="form-input" value="smtp.journalplatform.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SMTP Port</label>
                                <input type="number" class="form-input" value="587">
                            </div>
                            <div class="form-group">
                                <label class="form-label">From Email</label>
                                <input type="email" class="form-input" value="noreply@journalplatform.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Templates</label>
                                <select class="form-select">
                                    <option>Default Template</option>
                                    <option>Professional Template</option>
                                    <option>Minimal Template</option>
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="form-container">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">🔒 Security Settings</h3>
                        <form>
                            <div class="form-group">
                                <label class="form-label">Password Policy</label>
                                <select class="form-select">
                                    <option>Strong (12+ chars, mixed case, numbers, symbols)</option>
                                    <option>Medium (8+ chars, mixed case, numbers)</option>
                                    <option>Basic (6+ chars)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Session Timeout (minutes)</label>
                                <input type="number" class="form-input" value="60">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Two-Factor Authentication</label>
                                <select class="form-select">
                                    <option>Required for Admins</option>
                                    <option>Optional for All Users</option>
                                    <option>Disabled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Login Attempts Limit</label>
                                <input type="number" class="form-input" value="5">
                            </div>
                        </form>
                    </div>

                    <div class="form-container">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">💰 Payment Settings</h3>
                        <form>
                            <div class="form-group">
                                <label class="form-label">Payment Gateway</label>
                                <select class="form-select">
                                    <option>Stripe</option>
                                    <option>PayPal</option>
                                    <option>Square</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Default Currency</label>
                                <select class="form-select">
                                    <option>USD</option>
                                    <option>EUR</option>
                                    <option>GBP</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Subscription Price</label>
                                <input type="number" class="form-input" value="29.99">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Article Processing Fee</label>
                                <input type="number" class="form-input" value="199.00">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>💡 Note:</strong> Changes to system settings will take effect immediately. Make sure to test
                    thoroughly before applying changes to production.
                </div>
            </section>
        </main>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New User</h2>
                <button class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
            </div>
            <form>
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-input" placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-input" placeholder="Enter email address">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select class="form-select">
                            <option>Author</option>
                            <option>Reviewer</option>
                            <option>Editor</option>
                            <option>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option>Active</option>
                            <option>Inactive</option>
                            <option>Suspended</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Institution</label>
                    <input type="text" class="form-input" placeholder="Enter institution name">
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('addUserModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Journal Modal -->
    <div id="addJournalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Create New Journal</h2>
                <button class="modal-close" onclick="closeModal('addJournalModal')">&times;</button>
            </div>
            <form>
                <div class="form-group">
                    <label class="form-label">Journal Name</label>
                    <input type="text" class="form-input" placeholder="Enter journal name">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" placeholder="Enter journal description"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Editor</label>
                        <select class="form-select">
                            <option>Select Editor</option>
                            <option>Dr. Sarah Johnson</option>
                            <option>Dr. Michael Chen</option>
                            <option>Dr. Lisa Wang</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-select">
                            <option>Technology</option>
                            <option>Medicine</option>
                            <option>Science</option>
                            <option>Engineering</option>
                        </select>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Create Journal</button>
                    <button type="button" class="btn btn-outline"
                        onclick="closeModal('addJournalModal')">Cancel</button>
                </div>
            </form>
        </div>
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

        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = '';
        }

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

        // Initialize functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers for action buttons
            const actionButtons = document.querySelectorAll('.table .btn');
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const action = this.textContent.trim();
                    alert(`${action} functionality would be implemented here.`);
                });
            });

            // Add search functionality
            const searchInputs = document.querySelectorAll('.search-input');
            searchInputs.forEach(input => {
                input.addEventListener('input', function() {
                    // Search functionality would be implemented here
                    console.log('Searching for:', this.value);
                });
            });

            // Add form submission handlers
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Form submitted successfully!');
                    // Close modal if form is in a modal
                    const modal = this.closest('.modal');
                    if (modal) {
                        closeModal(modal.id);
                    }
                });
            });

            // Close modals when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal')) {
                    closeModal(e.target.id);
                }
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
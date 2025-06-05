<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// DB connection
$host = "localhost";
$db   = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("DB Connection failed: " . $conn->connect_error);
    die("Database connection failed.");
}

// Fetch article details
$article = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $stmt = $conn->prepare("
            SELECT a.id, a.title, a.abstract, a.keywords, a.submission_date, a.published_date, a.status, a.doi, 
                   a.file_path, a.journal, a.image_path, CONCAT(u.first_name, ' ', u.last_name) AS author_name
            FROM articles a
            LEFT JOIN users u ON a.submitted_by = u.id
            WHERE a.id = ?
        ");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $article = $result->fetch_assoc();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Fetch article error: " . $e->getMessage());
        echo "Error fetching article: " . htmlspecialchars($e->getMessage());
        exit;
    }
}

// Check access permissions
if (!$article) {
    header('HTTP/1.1 404 Not Found');
    echo "Article not found.";
    $conn->close();
    exit;
}
if ($article['status'] === 'approved' && !isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied: This article is only available to administrators.";
    $conn->close();
    exit;
}

$conn->close();

// Format published date
$published_date = ($article['status'] === 'published')
    ? (!empty($article['published_date']) ? date('F Y', strtotime($article['published_date'])) : 'Publication Date TBD')
    : ($article['status'] === 'approved' ? 'Approved, Awaiting Publication' : 'N/A');

// Clean and normalize file path
$filePath = ltrim($article['file_path'], '/\\');
// Log file path for debugging
error_log("File path for article {$article['id']}: {$filePath}");
error_log("Full file path: " . __DIR__ . '/' . $filePath);
error_log("File exists: " . (file_exists(__DIR__ . '/' . $filePath) ? 'Yes' : 'No'));

// Determine download label based on file extension
$fileExtension = strtolower(pathinfo($article['file_path'], PATHINFO_EXTENSION));
$downloadLabel = ($fileExtension === 'pdf') ? 'Download PDF' : 'Download Document';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article Details - SAHEL Analyst</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    :root {
        --primary-color: #1e3a8a;
        --secondary-color: #059669;
        --danger-color: #dc2626;
        --bg-light: #f8fafc;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--bg-light);
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    header {
        background: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 10px 0;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .logo-icon i {
        font-size: 2rem;
        color: var(--primary-color);
    }

    .logo-text h1 {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        margin: 0;
        color: var(--primary-color);
    }

    .logo-text p {
        margin: 0;
        font-size: 0.9rem;
        color: #6c757d;
    }

    nav ul {
        list-style: none;
        padding: 0;
        display: flex;
        gap: 20px;
        margin: 0;
    }

    nav ul li a {
        text-decoration: none;
        color: #1a2a44;
        font-weight: 500;
    }

    nav ul li a.btn {
        padding: 8px 16px;
        border-radius: 4px;
    }

    .btn-primary {
        background: var(--primary-color);
        color: #fff;
        border: none;
    }

    .btn-outline {
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        background: transparent;
    }

    .article-details {
        padding: 40px 0;
    }

    .article-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .article-image {
        width: 100%;
        height: 300px;
        object-fit: cover;
    }

    .article-image-placeholder {
        width: 100%;
        height: 300px;
        background-color: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        font-size: 1.2rem;
    }

    .article-content {
        padding: 30px;
    }

    .article-content h2 {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: #1a2a44;
        margin-bottom: 20px;
    }

    .article-meta {
        font-size: 0.95rem;
        color: #6c757d;
        margin-bottom: 20px;
    }

    .article-meta span {
        margin-right: 20px;
    }

    .article-meta i {
        margin-right: 5px;
    }

    .article-info {
        margin-bottom: 20px;
    }

    .article-info p {
        font-size: 1rem;
        color: #555;
        line-height: 1.6;
    }

    .article-info strong {
        color: var(--primary-color);
    }

    .document-viewer {
        height: 600px;
        margin-top: 20px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        overflow: hidden;
    }

    .document-viewer iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .btn-back,
    .btn-download {
        border-radius: 8px;
        font-weight: 500;
        padding: 10px 20px;
        margin-top: 20px;
    }

    .btn-back {
        background: var(--secondary-color);
        color: #fff;
    }

    .btn-download {
        background: var(--primary-color);
        color: #fff;
    }

    footer {
        background: #1a2a44;
        color: #fff;
        padding: 40px 0;
        margin-top: 40px;
    }

    .footer-content {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
    }

    .footer-main h2 {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
    }

    .social-links a {
        color: #fff;
        font-size: 1.2rem;
        margin-right: 10px;
    }

    .footer-links h3 {
        font-size: 1.2rem;
        margin-bottom: 10px;
    }

    .footer-links ul {
        list-style: none;
        padding: 0;
    }

    .footer-links ul li a {
        color: #fff;
        text-decoration: none;
        font-size: 0.95rem;
    }

    .contact-info .contact-item {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .footer-bottom {
        border-top: 1px solid #fff;
        margin-top: 20px;
        padding-top: 20px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
        }

        .article-content h2 {
            font-size: 1.5rem;
        }

        .article-image,
        .article-image-placeholder {
            height: 200px;
        }

        .document-viewer {
            height: 400px;
        }
    }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="logo-text">
                    <h1>SAHEL Analyst</h1>
                    <p>Faculty of Management Science</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="index.html#about">About</a></li>
                    <li><a href="index.html#features">Research Areas</a></li>
                    <li><a href="index.html#publications">Publications</a></li>
                    <li><a href="index.html#submission">Submit</a></li>
                    <li><a href="login.php" class="btn btn-outline">Login</a></li>
                    <li><a href="signup.php" class="btn btn-primary">Join Us</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="article-details">
        <div class="container">
            <a href="search.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Back</a>
            <div class="article-card mt-4">
                <?php if ($article['image_path'] && file_exists(__DIR__ . '/Uploads/images/' . basename($article['image_path']))) : ?>
                <img src="Uploads/images/<?php echo htmlspecialchars(basename($article['image_path'])); ?>"
                    alt="Article Image" class="article-image">
                <?php else : ?>
                <div class="article-image-placeholder">No Image</div>
                <?php endif; ?>
                <div class="article-content">
                    <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                    <div class="article-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($published_date); ?></span>
                        <span><i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?></span>
                        <span><i class="fas fa-book"></i> <?php echo htmlspecialchars($article['journal']); ?></span>
                    </div>
                    <div class="article-info">
                        <p><strong>Abstract:</strong> <?php echo htmlspecialchars($article['abstract']); ?></p>
                        <p><strong>Keywords:</strong> <?php echo htmlspecialchars($article['keywords'] ?? 'N/A'); ?></p>
                        <p><strong>DOI:</strong> <?php echo htmlspecialchars($article['doi'] ?? 'N/A'); ?></p>
                        <p><strong>Submission Date:</strong>
                            <?php echo htmlspecialchars($article['submission_date'] ?? 'N/A'); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($article['status']); ?></p>
                        <p><strong>Article ID:</strong> <?php echo htmlspecialchars($article['id']); ?></p>
                    </div>
                    <?php if (strtolower($fileExtension) === 'pdf') : ?>
                    <div class="document-viewer">
                        <iframe src="<?php echo htmlspecialchars($filePath); ?>" title="Document Viewer"></iframe>
                    </div>
                    <?php else : ?>
                    <div class="alert alert-info">
                        This file type cannot be viewed inline.
                        <a href="<?php echo htmlspecialchars($filePath); ?>" class="btn btn-primary btn-sm"
                            download="<?php echo htmlspecialchars(basename($article['file_path'])); ?>">Download
                            Document</a>
                    </div>
                    <?php endif; ?>
                    <a href="<?php echo htmlspecialchars($filePath); ?>" class="btn btn-download"
                        download="<?php echo htmlspecialchars(basename($article['file_path'])); ?>">
                        <i class="fas fa-download"></i> <?php echo $downloadLabel; ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-main">
                    <h2>SAHEL Analyst</h2>
                    <p>Advancing management science research through rigorous peer-reviewed publications and fostering
                        academic excellence.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-researchgate"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3>Journal</h3>
                    <ul>
                        <li><a href="index.html#about">About Us</a></li>
                        <li><a href="index.html#features">Research Areas</a></li>
                        <li><a href="index.html#publications">Current Issue</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Information</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <p>Faculty of Management Science</p>
                                <p>University of Maiduguri</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <p>editor@fmsjournal.edu.ng</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 SAHEL Analyst - Faculty of Management Science. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>
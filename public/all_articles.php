<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Database connection configuration
$host = "localhost";
$db   = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("DB Connection failed: " . $conn->connect_error);
    die("Database connection failed.");
}

// Pagination settings
$articles_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Count total articles for pagination
$count_sql = "SELECT COUNT(*) AS total FROM articles WHERE status IN ('approved', 'published')";
$count_result = $conn->query($count_sql);
$total_articles = $count_result ? $count_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_articles / $articles_per_page);

// Fetch articles for the current page
$sql = "SELECT a.id, a.title, a.abstract, a.keywords, a.submission_date, a.published_date, a.journal, a.image_path, a.doi, a.file_path, a.status,
               CONCAT(u.first_name, ' ', u.last_name) AS author_name
        FROM articles a
        LEFT JOIN users u ON a.submitted_by = u.id
        WHERE a.status IN ('approved', 'published')
        ORDER BY COALESCE(a.published_date, a.submission_date) DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    die("Database query failed.");
}
$stmt->bind_param("ii", $articles_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Articles - SAHEL Analyst</title>
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

    .btn-small {
        padding: 6px 12px;
        font-size: 0.875rem;
    }

    .publications {
        padding: 40px 0;
    }

    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        color: #1a2a44;
        text-align: center;
        margin-bottom: 10px;
    }

    .section-description {
        text-align: center;
        color: #6c757d;
        margin-bottom: 40px;
        font-size: 1.1rem;
    }

    .publications-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
    }

    .publication-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .publication-card.featured {
        border: 2px solid var(--primary-color);
    }

    .publication-image {
        position: relative;
        height: 200px;
    }

    .publication-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .publication-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: var(--primary-color);
        color: #fff;
        padding: 5px 10px;
        font-size: 0.8rem;
        border-radius: 4px;
    }

    .publication-category {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        padding: 5px 10px;
        font-size: 0.8rem;
        border-radius: 4px;
    }

    .publication-content {
        padding: 20px;
    }

    .publication-meta {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .publication-meta span {
        margin-right: 15px;
    }

    .publication-meta i {
        margin-right: 5px;
    }

    .publication-content h3 {
        font-size: 1.25rem;
        color: #1a2a44;
        margin-bottom: 10px;
    }

    .authors {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .author {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .author-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        object-fit: cover;
    }

    .abstract {
        font-size: 0.95rem;
        color: #555;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .publication-actions {
        display: flex;
        gap: 10px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 40px;
    }

    .pagination a {
        color: var(--primary-color);
        padding: 10px 15px;
        margin: 0 5px;
        border: 1px solid var(--primary-color);
        border-radius: 4px;
        text-decoration: none;
        transition: background 0.3s;
    }

    .pagination a:hover,
    .pagination a.active {
        background: var(--primary-color);
        color: #fff;
    }

    .pagination a.disabled {
        color: #6c757d;
        border-color: #6c757d;
        pointer-events: none;
    }

    footer {
        background: #1a2a44;
        color: #fff;
        padding: 40px 0;
        margin-top: 40px;
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        .publications-grid {
            grid-template-columns: 1fr;
        }

        .section-title {
            font-size: 2rem;
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="index.php#features">Research Areas</a></li>
                    <li><a href="index.php#publications">Publications</a></li>
                    <li><a href="index.php#submission">Submit</a></li>
                    <li><a href="login.php" class="btn btn-outline">Login</a></li>
                    <li><a href="signup.php" class="btn btn-primary">Join Us</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="publications">
        <div class="container">
            <div class="publications-header">
                <h2 class="section-title">All Articles</h2>
                <p class="section-description">Browse our complete archive of approved and published research articles
                </p>
            </div>

            <div class="publications-grid">
                <?php
                if ($result && $result->num_rows > 0) {
                    $is_first = true;
                    while ($row = $result->fetch_assoc()) {
                        $imagePath = !empty($row['image_path']) && file_exists(__DIR__ . '/Uploads/images/' . basename($row['image_path']))
                            ? 'Uploads/images/' . htmlspecialchars(basename($row['image_path']))
                            : 'https://plus.unsplash.com/premium_photo-1669652639356-f5cb1a086976?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0';
                        $publishedDate = !empty($row['published_date']) ? date('F Y', strtotime($row['published_date'])) : 'Not published';
                        $authorName = !empty($row['author_name']) ? htmlspecialchars($row['author_name']) : 'Unknown Author';
                        $category = strtolower(htmlspecialchars($row['journal']));
                        $isFeatured = $is_first ? ' featured' : '';
                        $badge = $is_first ? '<div class="publication-badge">Featured</div>' : '';
                        $fileExtension = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
                        $downloadLabel = ($fileExtension === 'pdf') ? 'Download PDF' : 'Download Document';
                        $filePath = ltrim($row['file_path'], '/\\');

                        // Log file path for debugging
                        error_log("File path for article {$row['id']}: {$filePath}");
                        error_log("Full file path: " . __DIR__ . '/' . $filePath);
                        error_log("File exists: " . (file_exists(__DIR__ . '/' . $filePath) ? 'Yes' : 'No'));

                        echo '<article class="publication-card' . $isFeatured . '" data-category="' . $category . '">';
                        echo '<div class="publication-image">';
                        echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($row['title']) . '">';
                        echo $badge;
                        echo '<div class="publication-category">' . htmlspecialchars($row['journal']) . '</div>';
                        echo '</div>';
                        echo '<div class="publication-content">';
                        echo '<div class="publication-meta">';
                        echo '<span class="publication-date"><i class="fas fa-calendar"></i> ' . $publishedDate . '</span>';
                        echo '<span class="publication-views"><i class="fas fa-eye"></i> N/A views</span>';
                        echo '</div>';
                        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                        echo '<div class="authors">';
                        echo '<div class="author">';
                        echo '<img src="https://plus.unsplash.com/premium_photo-1667251760208-5149aa5a2f48?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.1.0" alt="' . $authorName . '" class="author-avatar">';
                        echo '<span>' . $authorName . '</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '<p class="abstract">' . htmlspecialchars(substr($row['abstract'], 0, 150)) . '...</p>';
                        echo '<div class="publication-actions">';
                        echo '<a href="' . htmlspecialchars($filePath) . '" class="btn btn-outline btn-small" download="' . htmlspecialchars(basename($row['file_path'])) . '"><i class="fas fa-download"></i> ' . $downloadLabel . '</a>';
                        echo '<a href="view_details.php?id=' . htmlspecialchars($row['id']) . '" class="btn btn-primary btn-small"><i class="fas fa-external-link-alt"></i> Read Full Article</a>';
                        echo '</div>';
                        echo '</div>';
                        echo '</article>';

                        $is_first = false;
                    }
                } else {
                    echo '<p>No articles found.</p>';
                }
                ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php
                    // Previous page link
                    $prev_page = $current_page - 1;
                    echo '<a href="?page=' . $prev_page . '" class="' . ($current_page == 1 ? 'disabled' : '') . '"><i class="fas fa-chevron-left"></i> Previous</a>';

                    // Page numbers
                    for ($i = 1; $i <= $total_pages; $i++) {
                        echo '<a href="?page=' . $i . '" class="' . ($current_page == $i ? 'active' : '') . '">' . $i . '</a>';
                    }

                    // Next page link
                    $next_page = $current_page + 1;
                    echo '<a href="?page=' . $next_page . '" class="' . ($current_page == $total_pages ? 'disabled' : '') . '">Next <i class="fas fa-chevron-right"></i></a>';
                    ?>
            </div>
            <?php endif; ?>
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
                        <li><a href="index.php#about">About Us</a></li>
                        <li><a href="index.php#features">Research Areas</a></li>
                        <li><a href="index.php#publications">Current Issue</a></li>
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

<?php
// Close statement and connection
$stmt->close();
$conn->close();
?>
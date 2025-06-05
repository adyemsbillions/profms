<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FMS - Faculty of Management Science Journal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
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

            <div class="search-container">
                <div class="search-box">
                    <form action="search.php" method="POST">
                        <input type="text" name="search" placeholder="Search articles, authors, keywords..."
                            id="searchInput">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div id="searchResults"></div>

            <script>
            function performSearch() {
                const query = document.getElementById('searchInput').value;
                fetch('search.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'search=' + encodeURIComponent(query)
                    })
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('searchResults').innerHTML = data;
                    })
                    .catch(error => console.error('Error:', error));
            }
            </script>
            <nav>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="all_articles.php">All articles</a></li>
                    <li><a href="#publications">Publications</a></li>
                    <li><a href="#submission">Submit</a></li>
                    <li><a href="login.php" class="btn btn-outline">Login</a></li>
                    <li><a href="signup.php" class="btn btn-primary">Join Us</a></li>
                </ul>
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <section id="home" class="hero">
        <div class="hero-background">
            <div class="hero-overlay"></div>
            <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80"
                alt="Academic Research Background" class="hero-bg-image">
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-award"></i>
                    <span>Peer-Reviewed Excellence</span>
                </div>
                <h2>Advancing Management Science Research</h2>
                <p>A premier academic publication by the Faculty of Management Science at the University of Maiduguri,
                    fostering innovative research in business, finance, and administration.</p>
                <?php
                // Database connection configuration
                $host = "localhost";
                $db   = "fms";
                $user = "root";
                $pass = "";

                $conn = new mysqli($host, $user, $pass, $db);
                if ($conn->connect_error) {
                    error_log("DB Connection failed: " . $conn->connect_error);
                    $total_articles = 0;
                    $total_authors = 0;
                    $total_approved = 0;
                } else {
                    // Query for total articles (all articles, regardless of status)
                    $sql_articles = "SELECT COUNT(*) AS total_articles FROM articles";
                    $result_articles = $conn->query($sql_articles);
                    $total_articles = $result_articles && $result_articles->num_rows > 0
                        ? $result_articles->fetch_assoc()['total_articles']
                        : 0;
                    error_log("Total articles: $total_articles");

                    // Query for total authors (all users)
                    $sql_authors = "SELECT COUNT(*) AS total_authors FROM users";
                    $result_authors = $conn->query($sql_authors);
                    $total_authors = $result_authors && $result_authors->num_rows > 0
                        ? $result_authors->fetch_assoc()['total_authors']
                        : 0;
                    error_log("Total authors: $total_authors");

                    // Query for approved/published articles
                    $sql_approved = "SELECT COUNT(*) AS total_approved FROM articles WHERE status IN ('approved', 'published')";
                    $result_approved = $conn->query($sql_approved);
                    $total_approved = $result_approved && $result_approved->num_rows > 0
                        ? $result_approved->fetch_assoc()['total_approved']
                        : 0;
                    error_log("Total approved/published: $total_approved");

                    // Close connection
                    $conn->close();
                }
                ?>

                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo htmlspecialchars($total_articles); ?></span>
                        <span class="stat-label">Published Articles</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo htmlspecialchars($total_authors); ?></span>
                        <span class="stat-label">Authors</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo htmlspecialchars($total_approved); ?></span>
                        <span class="stat-label">Approved</span>
                    </div>
                </div>
                <div class="hero-buttons">
                    <a href="#submission" class="btn btn-primary btn-large">
                        <i class="fas fa-paper-plane"></i>
                        Submit Your Research
                    </a>
                    <a href="#publications" class="btn btn-secondary btn-large">
                        <i class="fas fa-book-open"></i>
                        Browse Articles
                    </a>
                </div>
            </div>
        </div>
        <div class="hero-scroll-indicator">
            <div class="scroll-arrow">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </section>

    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2 class="section-title">About SAHEL Analyst</h2>
                    <p class="section-description">The Faculty of Management Science Journal is a distinguished
                        peer-reviewed publication dedicated to advancing research in management, accounting, finance,
                        and administration. Our mission is to bridge theoretical knowledge with practical applications,
                        fostering innovation and excellence in business research.</p>

                    <div class="about-features">
                        <div class="feature-highlight">
                            <i class="fas fa-globe-americas"></i>
                            <div>
                                <h4>Global Reach</h4>
                                <p>Connecting researchers worldwide with cutting-edge management science insights</p>
                            </div>
                        </div>
                        <div class="feature-highlight">
                            <i class="fas fa-microscope"></i>
                            <div>
                                <h4>Rigorous Review</h4>
                                <p>Double-blind peer review ensuring the highest academic standards</p>
                            </div>
                        </div>
                        <div class="feature-highlight">
                            <i class="fas fa-rocket"></i>
                            <div>
                                <h4>Innovation Focus</h4>
                                <p>Promoting innovative research that shapes the future of management</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
                        alt="Research and Innovation" class="about-img">
                    <div class="image-overlay">
                        <div class="play-button">
                            <i class="fas fa-play"></i>
                        </div>
                        <p>Watch: Our Research Impact</p>
                    </div>
                </div>
            </div>

            <div class="journal-metrics">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Publication Frequency</h3>
                        <p>Quarterly Issues</p>
                        <span class="metric-detail">4 issues per year</span>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="metric-info">
                        <h3>ISSN Numbers</h3>
                        <p>Online: 1595-420X</p>
                        <span class="metric-detail">Print: 3027-2483</span>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="metric-info">
                        <h3>Peer Review</h3>
                        <p>Double-blind Process</p>
                        <span class="metric-detail">Expert reviewers</span>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div class="metric-info">
                        <h3>DOI Assignment</h3>
                        <p>Digital Object Identifier</p>
                        <span class="metric-detail">For every article</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
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

    // Fetch 5 random published or approved articles
    $sql = "SELECT a.id, a.title, a.abstract, a.keywords, a.submission_date, a.published_date, a.journal, a.image_path, a.doi, a.file_path, a.status,
               CONCAT(u.first_name, ' ', u.last_name) AS author_name
        FROM articles a
        LEFT JOIN users u ON a.submitted_by = u.id
        WHERE a.status IN ('approved', 'published')
        ORDER BY RAND()
        LIMIT 5";

    $result = $conn->query($sql);
    ?>

    <section id="features" class="research-areas">
        <div class="container">
            <h2 class="section-title">Featured Articles</h2>
            <p class="section-description">Explore a selection of our latest research publications</p>

            <div class="areas-grid">
                <?php
                if ($result && $result->num_rows > 0) {
                    $is_first = true;
                    while ($row = $result->fetch_assoc()) {
                        $imagePath = !empty($row['image_path']) && file_exists(__DIR__ . '/Uploads/images/' . basename($row['image_path']))
                            ? 'Uploads/images/' . htmlspecialchars(basename($row['image_path']))
                            : 'https://plus.unsplash.com/premium_photo-1669652639356-f5cb1a086976?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0';
                        $authorName = !empty($row['author_name']) ? htmlspecialchars($row['author_name']) : 'Unknown Author';
                        $isFeatured = $is_first ? ' featured' : '';
                        $fileExtension = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
                        $downloadLabel = ($fileExtension === 'pdf') ? 'Download PDF' : 'Download Document';
                        $filePath = ltrim($row['file_path'], '/\\');

                        // Log file path for debugging
                        error_log("File path for article {$row['id']}: {$filePath}");
                        error_log("Full file path: " . __DIR__ . '/' . $filePath);
                        error_log("File exists: " . (file_exists(__DIR__ . '/' . $filePath) ? 'Yes' : 'No'));

                        echo '<div class="area-card' . $isFeatured . '">';
                        echo '<div class="area-image">';
                        echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($row['title']) . '">';
                        echo '<div class="area-overlay">';
                        echo '<i class="fas fa-book"></i>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="area-content">';
                        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                        echo '<p>' . htmlspecialchars(substr($row['abstract'], 0, 100)) . '...</p>';
                        echo '<div class="area-tags">';
                        echo '<span class="tag">' . htmlspecialchars($row['journal']) . '</span>';
                        echo '<span class="tag">' . $authorName . '</span>';
                        echo '</div>';
                        echo '<div class="area-actions">';
                        echo '<a href="' . htmlspecialchars($filePath) . '" class="btn btn-outline btn-small" download="' . htmlspecialchars(basename($row['file_path'])) . '"><i class="fas fa-download"></i> ' . $downloadLabel . '</a>';
                        echo '<a href="view_details.php?id=' . htmlspecialchars($row['id']) . '" class="btn btn-primary btn-small"><i class="fas fa-external-link-alt"></i> Read Full Article</a>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';

                        $is_first = false;
                    }
                } else {
                    echo '<p>No articles found.</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <?php
    // Close database connection
    $conn->close();
    ?>
    <?php
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

    // Fetch the last 4 published or approved articles
    $sql = "SELECT a.id, a.title, a.abstract, a.keywords, a.submission_date, a.published_date, a.journal, a.image_path, a.doi, a.file_path, a.status,
               CONCAT(u.first_name, ' ', u.last_name) AS author_name
        FROM articles a
        LEFT JOIN users u ON a.submitted_by = u.id
        WHERE a.status IN ('approved', 'published')
        ORDER BY COALESCE(a.published_date, a.submission_date) DESC
        LIMIT 4";

    $result = $conn->query($sql);
    ?>

    <section id="publications" class="publications">
        <div class="container">
            <div class="publications-header">
                <h2 class="section-title">Latest Publications</h2>
                <p class="section-description">Discover groundbreaking research from our latest (2025)</p>
                <!-- <div class="publication-filters">
                <button class="filter-btn active" data-filter="all">All Articles</button>
                <button class="filter-btn" data-filter="finance">Finance</button>
                <button class="filter-btn" data-filter="accounting">Accounting</button>
                <button class="filter-btn" data-filter="management">Management</button>
                <button class="filter-btn" data-filter="technology">Technology</button>
            </div> -->
            </div>

            <div class="publications-grid">
                <?php
                if ($result && $result->num_rows > 0) {
                    $is_first = true;
                    while ($row = $result->fetch_assoc()) {
                        $imagePath = !empty($row['image_path']) && file_exists(__DIR__ . '/Uploads/images/' . basename($row['image_path']))
                            ? 'Uploads/images/' . htmlspecialchars(basename($row['image_path']))
                            : 'https://plus.unsplash.com/premium_photo-1669652639356-f5cb1a086976?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
                        $publishedDate = !empty($row['published_date']) ? date('F Y', strtotime($row['published_date'])) : 'Not published';
                        $authorName = !empty($row['author_name']) ? htmlspecialchars($row['author_name']) : 'Unknown Author';
                        $category = strtolower(htmlspecialchars($row['journal']));
                        $isFeatured = $is_first ? ' featured' : '';
                        $badge = $is_first ? '<div class="publication-badge">Featured</div>' : '';
                        $fileExtension = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
                        $downloadLabel = ($fileExtension === 'pdf') ? 'Download PDF' : 'Download Document';

                        // Clean and normalize file path
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
                        echo '<img src="https://plus.unsplash.com/premium_photo-1667251760208-5149aa5a2f48?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="' . $authorName . '" class="author-avatar">';
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
                    echo '<p>No recent publications found.</p>';
                }
                ?>
            </div>

            <div class="view-all-section">
                <a href="view-all.php" class="btn btn-secondary btn-large">
                    <i class="fas fa-archive"></i>
                    View All Publications
                </a>
                <p>Browse our complete archive of research articles</p>
            </div>
        </div>
    </section>

    <?php
    // Close database connection
    $conn->close();
    ?>

    <section id="submission" class="submission">
        <div class="container">
            <div class="submission-content">
                <div class="submission-info">
                    <h2 class="section-title">Submit Your Research</h2>
                    <p class="section-description">Join our community of researchers and contribute to advancing
                        management science knowledge</p>

                    <div class="submission-process">
                        <div class="process-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Prepare Manuscript</h4>
                                <p>Follow our submission guidelines and formatting requirements</p>
                            </div>
                        </div>
                        <div class="process-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Submit Online</h4>
                                <p>Upload your manuscript through our online submission system</p>
                            </div>
                        </div>
                        <div class="process-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Peer Review</h4>
                                <p>Expert reviewers evaluate your research using double-blind review</p>
                            </div>
                        </div>
                        <div class="process-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4>Publication</h4>
                                <p>Accepted articles are published in our quarterly issues</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="submission-guidelines">
                    <div class="guidelines-card">
                        <h3>Submission Requirements</h3>
                        <div class="requirement-list">
                            <div class="requirement-item">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <h4>Originality</h4>
                                    <p>Manuscripts must be original and not under consideration elsewhere</p>
                                </div>
                            </div>
                            <div class="requirement-item">
                                <i class="fas fa-file-alt"></i>
                                <div>
                                    <h4>Conference Papers</h4>
                                    <p>Rewritten conference papers accepted with proper permissions</p>
                                </div>
                            </div>
                            <div class="requirement-item">
                                <i class="fas fa-percentage"></i>
                                <div>
                                    <h4>Similarity Index</h4>
                                    <p>Maximum 20% similarity index for acceptance</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pricing-card">
                        <h3>Publication Fees</h3>
                        <div class="pricing-list">
                            <div class="pricing-item">
                                <div class="price-info">
                                    <span class="price-label">Review Fee</span>
                                    <span class="price-amount">₦5,000</span>
                                </div>
                                <span class="price-note">Non-refundable</span>
                            </div>
                            <div class="pricing-item">
                                <div class="price-info">
                                    <span class="price-label">Publication Fee</span>
                                    <span class="price-amount">₦40,000</span>
                                </div>
                                <span class="price-note">Upon acceptance</span>
                            </div>
                            <div class="pricing-item">
                                <div class="price-info">
                                    <span class="price-label">Hardcopy (Optional)</span>
                                    <span class="price-amount">₦10,000</span>
                                </div>
                                <span class="price-note">Physical copy</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="submission-cta">
                <div class="cta-content">
                    <h3>Ready to Submit Your Research?</h3>
                    <p>Join thousands of researchers who have published with SAHEL Analyst</p>
                    <div class="cta-buttons">
                        <a href="signup.php" class="btn btn-primary btn-large">
                            <i class="fas fa-user-plus"></i>
                            Create Account
                        </a>
                        <a href="#" class="btn btn-secondary btn-large">
                            <i class="fas fa-download"></i>
                            Download Guidelines
                        </a>
                    </div>
                </div>
                <div class="cta-image">
                    <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                        alt="Research Submission">
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-main">
                    <div class="footer-logo">
                        <div class="logo-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="logo-text">
                            <h2>SAHEL Analyst</h2>
                            <p>Faculty of Management Science</p>
                        </div>
                    </div>
                    <p class="footer-description">Advancing management science research through rigorous peer-reviewed
                        publications and fostering academic excellence in business education.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-researchgate"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-google-scholar"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-links">
                    <div class="link-group">
                        <h3>Journal</h3>
                        <ul>
                            <li><a href="#about">About Us</a></li>
                            <li><a href="all_articles.php">All articles</a></li>
                            <li><a href="#publications">Current Issue</a></li>
                            <li><a href="#">Archives</a></li>
                            <li><a href="#">Editorial Board</a></li>
                        </ul>
                    </div>
                    <div class="link-group">
                        <h3>Authors</h3>
                        <ul>
                            <li><a href="#submission">Submit Article</a></li>
                            <li><a href="#">Author Guidelines</a></li>
                            <li><a href="#">Peer Review</a></li>
                            <li><a href="#">Publication Ethics</a></li>
                            <li><a href="#">Copyright</a></li>
                        </ul>
                    </div>
                    <div class="link-group">
                        <h3>Resources</h3>
                        <ul>
                            <li><a href="#">Research Tools</a></li>
                            <li><a href="#">Citation Guide</a></li>
                            <li><a href="#">Templates</a></li>
                            <li><a href="#">FAQ</a></li>
                            <li><a href="#">Contact Support</a></li>
                        </ul>
                    </div>
                </div>

                <div class="footer-contact">
                    <h3>Contact Information</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <p>Faculty of Management Science</p>
                                <p>University of Maiduguri</p>
                                <p>Borno State, Nigeria</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <p>editor@fmsjournal.edu.ng</p>
                                <p>info@fmsjournal.edu.ng</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <p>+234 (0) 76 123 4567</p>
                                <p>+234 (0) 80 123 4567</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2025 SAHEL Analyst - Faculty of Management Science. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                        <a href="#">Accessibility</a>
                        <a href="#">Site Map</a>
                    </div>
                </div>

            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>

</html>
<?php
// Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fms";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search query from POST request
$searchQuery = isset($_POST['search']) ? trim($_POST['search']) : '';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - FMS Journal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .search-results {
        padding: 40px 0;
    }

    .search-results h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        color: #1a2a44;
        margin-bottom: 20px;
        word-wrap: break-word;
        max-width: 100%;
    }

    .search-results p {
        font-size: 1.1rem;
        color: #555;
        margin-bottom: 30px;
    }

    .publications-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .publication-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .publication-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .publication-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .publication-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .publication-category {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #007bff;
        color: #fff;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .publication-content {
        padding: 20px;
    }

    .publication-meta {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .publication-meta i {
        margin-right: 5px;
    }

    .publication-content h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        color: #1a2a44;
        margin: 0 0 10px;
        line-height: 1.4;
    }

    .authors {
        font-size: 0.95rem;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .abstract {
        font-size: 0.95rem;
        color: #555;
        margin-bottom: 15px;
        line-height: 1.6;
    }

    .publication-actions {
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .btn-primary {
        background: #007bff;
        color: #fff;
    }

    .btn-primary:hover {
        background: #0056b3;
    }

    .btn-outline {
        border: 1px solid #007bff;
        color: #007bff;
        background: transparent;
    }

    .btn-outline:hover {
        background: #007bff;
        color: #fff;
    }

    @media (max-width: 768px) {
        .publications-grid {
            grid-template-columns: 1fr;
        }

        .search-results h3 {
            font-size: 1.5rem;
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
                    <h1>FMS Journal</h1>
                    <p>Faculty of Management Science</p>
                </div>
            </div>
            <div class="search-container">
                <div class="search-box">
                    <form action="search.php" method="POST">
                        <input type="text" name="search" placeholder="Search articles, authors, keywords, DOI, etc."
                            value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
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
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <section class="search-results">
        <div class="container">
            <?php
            if (!empty($searchQuery)) {
                // Prepare search query (sanitize to prevent SQL injection)
                $searchQuery = $conn->real_escape_string($searchQuery);

                // Truncate search query for display
                $displayQuery = strlen($searchQuery) > 50 ? substr($searchQuery, 0, 47) . '...' : $searchQuery;

                // SQL query to search articles across all relevant columns, including author name
                $sql = "SELECT a.id, a.title, a.abstract, a.keywords, a.submission_date, a.published_date, a.journal, a.image_path, a.doi, a.file_path, a.status,
                               CONCAT(u.first_name, ' ', u.last_name) AS author_name
                        FROM articles a
                        LEFT JOIN users u ON a.submitted_by = u.id
                        WHERE (CAST(a.id AS CHAR) LIKE '%$searchQuery%'
                            OR a.title LIKE '%$searchQuery%'
                            OR a.abstract LIKE '%$searchQuery%'
                            OR a.keywords LIKE '%$searchQuery%'
                            OR a.doi LIKE '%$searchQuery%'
                            OR a.journal LIKE '%$searchQuery%'
                            OR a.file_path LIKE '%$searchQuery%'
                            OR a.image_path LIKE '%$searchQuery%'
                            OR a.status LIKE '%$searchQuery%'
                            OR CONCAT(u.first_name, ' ', u.last_name) LIKE '%$searchQuery%')
                            AND a.status IN ('approved', 'published')
                        ORDER BY a.published_date DESC
                        LIMIT 10";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    echo '<h3>Search Results for "' . htmlspecialchars($displayQuery) . '"</h3>';
                    echo '<div class="publications-grid">';

                    while ($row = $result->fetch_assoc()) {
                        $imagePath = !empty($row['image_path']) ? $row['image_path'] : 'https://images.unsplash.com/photo-1677442136019-21780ecad995?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';
                        $publishedDate = !empty($row['published_date']) ? date('F Y', strtotime($row['published_date'])) : 'Not published';
                        $authorName = !empty($row['author_name']) ? $row['author_name'] : 'Unknown Author';

                        echo '<article class="publication-card" data-category="' . strtolower($row['journal']) . '">';
                        echo '<div class="publication-image">';
                        echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row['title']) . '">';
                        echo '<div class="publication-category">' . htmlspecialchars($row['journal']) . '</div>';
                        echo '</div>';
                        echo '<div class="publication-content">';
                        echo '<div class="publication-meta">';
                        echo '<span class="publication-date"><i class="fas fa-calendar"></i> ' . $publishedDate . '</span>';
                        echo '</div>';
                        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                        echo '<div class="authors">';
                        echo '<span>' . htmlspecialchars($authorName) . '</span>';
                        echo '</div>';
                        echo '<p class="abstract">' . htmlspecialchars(substr($row['abstract'], 0, 150)) . '...</p>';
                        echo '<div class="publication-actions">';
                        echo '<a href="' . htmlspecialchars($row['file_path']) . '" class="btn btn-outline btn-small"><i class="fas fa-download"></i> Download PDF</a>';
                        echo '<a href="view_details.php?id=' . htmlspecialchars($row['id']) . '" class="btn btn-primary btn-small"><i class="fas fa-external-link-alt"></i> Read Full Article</a>';
                        echo '</div>';
                        echo '</div>';
                        echo '</article>';
                    }

                    echo '</div>';
                } else {
                    echo '<p>No results found for "' . htmlspecialchars($displayQuery) . '". Try different keywords.</p>';
                }
            } else {
                echo '<p>Please enter a search query.</p>';
            }

            // Close connection
            $conn->close();
            ?>
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
                            <h2>FMS Journal</h2>
                            <p>Faculty of Management Science</p>
                        </div>
                    </div>
                    <p class="footer-description">Advancing management science research through rigorous peer-reviewed
                        publications and fostering academic excellence in business education.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-researchgate"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-google-scholar"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <div class="link-group">
                        <h3>Journal</h3>
                        <ul>
                            <li><a href="index.html#about">About Us</a></li>
                            <li><a href="index.html#features">Research Areas</a></li>
                            <li><a href="index.html#publications">Current Issue</a></li>
                            <li><a href="#">Archives</a></li>
                            <li><a href="#">Editorial Board</a></li>
                        </ul>
                    </div>
                    <div class="link-group">
                        <h3>Authors</h3>
                        <ul>
                            <li><a href="index.html#submission">Submit Article</a></li>
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
                    <p>© 2025 FMS Journal - Faculty of Management Science. All rights reserved.</p>
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
</body>

</html>
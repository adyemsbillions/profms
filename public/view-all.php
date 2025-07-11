<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Publications - FMS Journal</title>
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
                    <h1><a href="index.php">FMS Journal</a></h1>
                    <p>Faculty of Management Science</p>
                </div>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <input type="text" placeholder="Search articles, authors, keywords..." id="searchInput">
                    <button type="button" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="index.php#features">Research Areas</a></li>
                    <li><a href="view-all.php" class="active">Publications</a></li>
                    <li><a href="index.php#submission">Submit</a></li>
                    <li><a href="login.php" class="btn btn-outline">Login</a></li>
                    <li><a href="signup.php" class="btn btn-primary">Join Us</a></li>
                </ul>
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <i class="fas fa-chevron-right"></i>
                <span>All Publications</span>
            </div>
            <h1>All Publications</h1>
            <p>Browse our complete archive of peer-reviewed research articles</p>
        </div>
    </section>

    <section class="publications-archive">
        <div class="container">
            <div class="archive-controls">
                <div class="filter-section">
                    <h3>Filter Publications</h3>
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="categoryFilter">Category</label>
                            <select id="categoryFilter" class="filter-select">
                                <option value="all">All Categories</option>
                                <option value="finance">Finance</option>
                                <option value="accounting">Accounting</option>
                                <option value="management">Management</option>
                                <option value="technology">Technology</option>
                                <option value="economics">Economics</option>
                                <option value="governance">Corporate Governance</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="yearFilter">Publication Year</label>
                            <select id="yearFilter" class="filter-select">
                                <option value="all">All Years</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                                <option value="2021">2021</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="sortFilter">Sort By</label>
                            <select id="sortFilter" class="filter-select">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="title">Title A-Z</option>
                                <option value="views">Most Viewed</option>
                                <option value="citations">Most Cited</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button class="btn btn-primary" id="applyFilters">
                                <i class="fas fa-filter"></i>
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <div class="view-options">
                    <div class="results-info">
                        <span id="resultsCount">Showing 24 of 156 articles</span>
                    </div>
                    <div class="view-toggle">
                        <button class="view-btn active" data-view="grid">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="publications-container">
                <div class="publications-grid" id="publicationsGrid">
                    <!-- Volume 2, Issue 1 (2025) -->
                    <div class="volume-header">
                        <h2>Volume 2, Issue 1 (2025)</h2>
                        <span class="issue-date">Published: March 2025</span>
                    </div>

                    <article class="publication-card" data-category="technology" data-year="2025" data-views="2300">
                        <div class="publication-image">
                            <img src="https://images.unsplash.com/photo-1677442136019-21780ecad995?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                                alt="AI in Marketing Research">
                            <div class="publication-category">Technology</div>
                        </div>
                        <div class="publication-content">
                            <div class="publication-meta">
                                <span class="publication-date">
                                    <i class="fas fa-calendar"></i>
                                    March 15, 2025
                                </span>
                                <span class="publication-views">
                                    <i class="fas fa-eye"></i>
                                    2.3k views
                                </span>
                                <span class="publication-citations">
                                    <i class="fas fa-quote-right"></i>
                                    12 citations
                                </span>
                            </div>
                            <h3>The Impact of Artificial Intelligence on Marketing Strategies in Nigerian Businesses
                            </h3>
                            <div class="authors">
                                <span>Dr. Ahmed Mohammed, Prof. Kemi Johnson</span>
                            </div>
                            <p class="abstract">This comprehensive study examines how artificial intelligence
                                technologies are revolutionizing marketing strategies across various sectors in
                                Nigeria...</p>
                            <div class="publication-actions">
                                <a href="article-details.php?id=1" class="btn btn-outline btn-small">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn btn-primary btn-small">
                                    <i class="fas fa-download"></i>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="publication-card" data-category="finance" data-year="2025" data-views="1800">
                        <div class="publication-image">
                            <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                                alt="Banking Systems Research">
                            <div class="publication-category">Finance</div>
                        </div>
                        <div class="publication-content">
                            <div class="publication-meta">
                                <span class="publication-date">
                                    <i class="fas fa-calendar"></i>
                                    March 10, 2025
                                </span>
                                <span class="publication-views">
                                    <i class="fas fa-eye"></i>
                                    1.8k views
                                </span>
                                <span class="publication-citations">
                                    <i class="fas fa-quote-right"></i>
                                    8 citations
                                </span>
                            </div>
                            <h3>Integrated Financial Accounting Systems and Operational Performance of Deposit Money
                                Banks</h3>
                            <div class="authors">
                                <span>Dr. Emmanuel Okafor, Dr. Sarah Williams</span>
                            </div>
                            <p class="abstract">An empirical analysis of how integrated financial accounting systems
                                enhance operational efficiency and performance metrics...</p>
                            <div class="publication-actions">
                                <a href="article-details.php?id=2" class="btn btn-outline btn-small">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn btn-primary btn-small">
                                    <i class="fas fa-download"></i>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="publication-card" data-category="accounting" data-year="2025" data-views="1500">
                        <div class="publication-image">
                            <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                                alt="Cloud Accounting Research">
                            <div class="publication-category">Accounting</div>
                        </div>
                        <div class="publication-content">
                            <div class="publication-meta">
                                <span class="publication-date">
                                    <i class="fas fa-calendar"></i>
                                    March 5, 2025
                                </span>
                                <span class="publication-views">
                                    <i class="fas fa-eye"></i>
                                    1.5k views
                                </span>
                                <span class="publication-citations">
                                    <i class="fas fa-quote-right"></i>
                                    15 citations
                                </span>
                            </div>
                            <h3>Cloud Accounting and the Qualitative Characteristics of Financial Reporting</h3>
                            <div class="authors">
                                <span>Dr. Sarah Ibrahim, Prof. Michael Chen</span>
                            </div>
                            <p class="abstract">This research explores how cloud-based accounting technologies influence
                                the qualitative characteristics of financial reporting...</p>
                            <div class="publication-actions">
                                <a href="article-details.php?id=3" class="btn btn-outline btn-small">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn btn-primary btn-small">
                                    <i class="fas fa-download"></i>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="publication-card" data-category="management" data-year="2025" data-views="1200">
                        <div class="publication-image">
                            <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                                alt="HR Management Research">
                            <div class="publication-category">Management</div>
                        </div>
                        <div class="publication-content">
                            <div class="publication-meta">
                                <span class="publication-date">
                                    <i class="fas fa-calendar"></i>
                                    March 1, 2025
                                </span>
                                <span class="publication-views">
                                    <i class="fas fa-eye"></i>
                                    1.2k views
                                </span>
                                <span class="publication-citations">
                                    <i class="fas fa-quote-right"></i>
                                    6 citations
                                </span>
                            </div>
                            <h3>Effect of Human Resource Management Practices on Employee Performance in Private
                                Universities</h3>
                            <div class="authors">
                                <span>Prof. Fatima Adebayo, Dr. James Thompson</span>
                            </div>
                            <p class="abstract">A comprehensive study examining the relationship between strategic human
                                resource management practices and employee performance...</p>
                            <div class="publication-actions">
                                <a href="article-details.php?id=4" class="btn btn-outline btn-small">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn btn-primary btn-small">
                                    <i class="fas fa-download"></i>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- Volume 1, Issue 4 (2024) -->
                    <div class="volume-header">
                        <h2>Volume 1, Issue 4 (2024)</h2>
                        <span class="issue-date">Published: December 2024</span>
                    </div>

                    <article class="publication-card" data-category="governance" data-year="2024" data-views="2100">
                        <div class="publication-image">
                            <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                                alt="Corporate Governance">
                            <div class="publication-category">Governance</div>
                        </div>
                        <div class="publication-content">
                            <div class="publication-meta">
                                <span class="publication-date">
                                    <i class="fas fa-calendar"></i>
                                    December 20, 2024
                                </span>
                                <span class="publication-views">
                                    <i class="fas fa-eye"></i>
                                    2.1k views
                                </span>
                                <span class="publication-citations">
                                    <i class="fas fa-quote-right"></i>
                                    18 citations
                                </span>
                            </div>
                            <h3>Corporate Governance Mechanisms and Firm Performance in Emerging Markets</h3>
                            <div class="authors">
                                <span>Prof. David Okonkwo, Dr. Lisa Anderson</span>
                            </div>
                            <p class="abstract">This study investigates the relationship between corporate governance
                                mechanisms and firm performance in emerging African markets...</p>
                            <div class="publication-actions">
                                <a href="article-details.php?id=5" class="btn btn-outline btn-small">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn btn-primary btn-small">
                                    <i class="fas fa-download"></i>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </article>

                    <article class="publication-card" data-category="economics" data-year="2024" data-views="1900">
                        <div class="publication-image">
                            <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80"
                                alt="Economic Analysis">
                            <div class="publication-category">Economics</div>
                        </div>
                        <div class="publication-content">
                            <div class="publication-meta">
                                <span class="publication-date">
                                    <i class="fas fa-calendar"></i>
                                    December 15, 2024
                                </span>
                                <span class="publication-views">
                                    <i class="fas fa-eye"></i>
                                    1.9k views
                                </span>
                                <span class="publication-citations">
                                    <i class="fas fa-quote-right"></i>
                                    22 citations
                                </span>
                            </div>
                            <h3>Macroeconomic Factors and Stock Market Performance in West Africa</h3>
                            <div class="authors">
                                <span>Dr. Amina Hassan, Prof. Robert Clarke</span>
                            </div>
                            <p class="abstract">An empirical examination of the relationship between macroeconomic
                                variables and stock market performance across West African countries...</p>
                            <div class="publication-actions">
                                <a href="article-details.php?id=6" class="btn btn-outline btn-small">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                <a href="#" class="btn btn-primary btn-small">
                                    <i class="fas fa-download"></i>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="pagination">
                    <button class="pagination-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </button>
                    <div class="pagination-numbers">
                        <button class="pagination-number active">1</button>
                        <button class="pagination-number">2</button>
                        <button class="pagination-number">3</button>
                        <span class="pagination-dots">...</span>
                        <button class="pagination-number">8</button>
                    </div>
                    <button class="pagination-btn">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </button>
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
                            <h2>FMS Journal</h2>
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
                            <li><a href="index.php#about">About Us</a></li>
                            <li><a href="index.php#features">Research Areas</a></li>
                            <li><a href="view-all.php">Current Issue</a></li>
                            <li><a href="view-all.php">Archives</a></li>
                            <li><a href="#">Editorial Board</a></li>
                        </ul>
                    </div>
                    <div class="link-group">
                        <h3>Authors</h3>
                        <ul>
                            <li><a href="index.php#submission">Submit Article</a></li>
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
                    <p>&copy; 2025 FMS Journal - Faculty of Management Science. All rights reserved.</p>
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
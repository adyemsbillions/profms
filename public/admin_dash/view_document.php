<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../index.php');
    exit;
}

// Set error reporting (disable display in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

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

// Get article ID from URL
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($article_id <= 0) {
    die("Invalid article ID.");
}

// Fetch article and submitter name
try {
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.abstract, a.keywords, a.submission_date, a.last_update, a.status, a.doi, 
               a.published_date, a.file_path, a.journal, a.submitted_by, 
               CONCAT(u.first_name, ' ', u.last_name) AS submitter_name
        FROM articles a
        LEFT JOIN users u ON a.submitted_by = u.id
        WHERE a.id = ?
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result->fetch_assoc();
    $stmt->close();

    if (!$article) {
        die("Article not found.");
    }

    // Validate file existence
    $file_path = $article['file_path'];
    // Base path for files relative to project root
    $base_path = realpath(__DIR__ . '/../../uploads');
    $absolute_path = realpath($base_path . '/' . ltrim($file_path, '/'));
    if (!$absolute_path || !file_exists($absolute_path)) {
        error_log("File not found: " . $file_path);
        $file_error = "Document file is missing or inaccessible.";
    } else {
        // Ensure file is within uploads directory
        if (strpos($absolute_path, $base_path) !== 0) {
            error_log("Invalid file path: " . $file_path);
            $file_error = "Invalid document path.";
        }
    }
} catch (Exception $e) {
    error_log("Fetch article error: " . $e->getMessage());
    die("Error fetching article details.");
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document - Journal Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #059669;
            --danger-color: #dc2626;
            --bg-light: #f8fafc;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Arial, sans-serif;
            padding-top: 20px;
        }

        .article-details {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 20px;
        }

        .article-info {
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .article-info strong {
            color: var(--primary-color);
        }

        .document-viewer {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            height: 600px;
        }

        .document-viewer iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        @media (max-width: 576px) {

            .article-details,
            .document-viewer {
                padding: 1rem;
            }

            .document-viewer {
                height: 400px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="my-4 text-center" style="color: var(--primary-color);">View Document</h1>
        <a href="manage_articles.php" class="btn btn-outline-primary mb-4">Back to Articles</a>
        <div class="article-details">
            <h2><?php echo htmlspecialchars($article['title']); ?></h2>
            <p class="article-info"><strong>Journal:</strong> <?php echo htmlspecialchars($article['journal']); ?></p>
            <p class="article-info"><strong>Status:</strong> <?php echo htmlspecialchars($article['status']); ?></p>
            <p class="article-info"><strong>Submitted By:</strong>
                <?php echo htmlspecialchars($article['submitter_name'] ?? 'Unknown'); ?></p>
            <p class="article-info"><strong>Submission Date:</strong>
                <?php echo htmlspecialchars($article['submission_date'] ?? 'N/A'); ?></p>
            <p class="article-info"><strong>DOI:</strong> <?php echo htmlspecialchars($article['doi'] ?? 'N/A'); ?></p>
            <p class="article-info"><strong>File Path:</strong> <?php echo htmlspecialchars($article['file_path']); ?>
            </p>
        </div>
        <div class="document-viewer">
            <?php if (isset($file_error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($file_error); ?></div>
            <?php elseif (pathinfo($file_path, PATHINFO_EXTENSION) === 'pdf'): ?>
                <iframe src="view_document.php?file=<?php echo urlencode($file_path); ?>" title="Document Viewer"></iframe>
            <?php else: ?>
                <div class="alert alert-info">
                    This file type cannot be viewed inline.
                    <a href="view_document.php?file=<?php echo urlencode($file_path); ?>" class="btn btn-primary btn-sm"
                        download>Download Document</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
// Handle file streaming for secure access
if (isset($_GET['file'])) {
    $file_path = urldecode($_GET['file']);
    $base_path = realpath(__DIR__ . '/../../uploads');
    $absolute_path = realpath($base_path . '/' . ltrim($file_path, '/'));

    if ($absolute_path && file_exists($absolute_path) && strpos($absolute_path, $base_path) === 0) {
        $mime_type = mime_content_type($absolute_path);
        header('Content-Type: ' . $mime_type);
        if (pathinfo($file_path, PATHINFO_EXTENSION) !== 'pdf') {
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        }
        readfile($absolute_path);
        exit;
    } else {
        error_log("Invalid or missing file: " . $file_path);
        header('HTTP/1.1 404 Not Found');
        exit;
    }
}
?>
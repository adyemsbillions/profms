<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "localhost";
$db   = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("DB Connection failed: " . $conn->connect_error);
    die("Database connection failed.");
}

// Fetch publications
$publications = [];
try {
    $result = $conn->query("SELECT id, title, image_path, file_path, created_at FROM publications ORDER BY created_at DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $publications[] = $row;
        }
        $result->free();
    } else {
        throw new Exception("Query failed: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error fetching publications: " . $e->getMessage());
    $error = "Failed to load publications. Please try again later.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publications - FMS Journal</title>
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

    .publications-section {
        padding: 40px 0;
    }

    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: #1a2a44;
        margin-bottom: 20px;
    }

    .publication-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }

    .publication-card img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .publication-card h3 {
        font-size: 1.25rem;
        color: #1a2a44;
        margin-bottom: 10px;
    }

    .publication-card p {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .publication-card a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
    }

    .publication-card a:hover {
        text-decoration: underline;
    }

    .alert {
        margin-bottom: 20px;
    }

    .no-publications {
        font-size: 1rem;
        color: #6c757d;
        text-align: center;
    }

    @media (max-width: 768px) {
        .section-title {
            font-size: 1.5rem;
        }

        .publication-card h3 {
            font-size: 1rem;
        }
    }
    </style>
</head>

<body>
    <section class="publications-section">
        <div class="container">
            <h2 class="section-title">Publications</h2>
            <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (empty($publications)): ?>
            <p class="no-publications">No publications found.</p>
            <?php else: ?>
            <?php foreach ($publications as $publication): ?>
            <div class="publication-card">
                <?php if ($publication['image_path']): ?>
                <img src="<?php echo htmlspecialchars($publication['image_path']); ?>"
                    alt="<?php echo htmlspecialchars($publication['title']); ?>">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($publication['title']); ?></h3>
                <p>Published on: <?php echo date('F j, Y', strtotime($publication['created_at'])); ?></p>
                <a href="<?php echo htmlspecialchars($publication['file_path']); ?>" target="_blank">
                    <i class="fas fa-file-download"></i> Download Document
                </a>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>
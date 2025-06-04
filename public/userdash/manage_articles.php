<?php
session_start();
require('db.php'); // adjust path as needed

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Helper function to safely output HTML
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Handle article update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_article'])) {
    $article_id = intval($_POST['article_id']);
    $title = trim($_POST['title']);
    $abstract = trim($_POST['abstract']);
    $journal = $_POST['journal'];
    $keywords = trim($_POST['keywords']);

    // Simple validation
    $errors = [];
    if ($title === '') $errors[] = "Title is required.";
    if ($abstract === '') $errors[] = "Abstract is required.";
    if ($journal === '') $errors[] = "Journal selection is required.";

    // Handle publication image upload (optional)
    $image_path_db = null;
    if (isset($_FILES['publication_image']) && $_FILES['publication_image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['publication_image'];
        $allowed_image_ext = ['jpg', 'jpeg', 'png'];
        $image_ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        if (!in_array($image_ext, $allowed_image_ext)) {
            $errors[] = "Invalid image file type. Only JPG, JPEG, PNG allowed.";
        }
        if ($image['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errors[] = "Image file size exceeds 5MB.";
        }
    }

    if (empty($errors)) {
        // Create uploads directory for images if not exists
        $image_upload_dir = __DIR__ . '/../uploads/images/';
        if (!is_dir($image_upload_dir)) {
            mkdir($image_upload_dir, 0755, true);
        }

        // Handle image file upload
        if (isset($_FILES['publication_image']) && $_FILES['publication_image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['publication_image'];
            $new_image_filename = uniqid('image_', true) . '.' . $image_ext;
            $image_destination = $image_upload_dir . $new_image_filename;

            if (move_uploaded_file($image['tmp_name'], $image_destination)) {
                $image_path_db = 'Uploads/images/' . $new_image_filename;
            } else {
                $errors[] = "Failed to upload image file.";
            }
        }

        if (empty($errors)) {
            // Fetch existing image path to preserve it if no new image is uploaded
            $stmt = $conn->prepare("SELECT image_path FROM articles WHERE id = ? AND submitted_by = ?");
            $stmt->bind_param("ii", $article_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing_article = $result->fetch_assoc();
            $stmt->close();

            // Use existing image path if no new image was uploaded
            $image_path_db = $image_path_db ?? ($existing_article['image_path'] ?? null);

            // Update article
            $stmt = $conn->prepare("UPDATE articles SET title=?, abstract=?, journal=?, keywords=?, image_path=?, last_update=NOW() WHERE id=? AND submitted_by=?");
            $stmt->bind_param("sssssii", $title, $abstract, $journal, $keywords, $image_path_db, $article_id, $user_id);
            $stmt->execute();
            if ($stmt->affected_rows >= 0) {
                $success_msg = "Article updated successfully.";
            } else {
                $errors[] = "Failed to update article.";
            }
            $stmt->close();
        }
    }
}

// Fetch article to edit if requested
$edit_article = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ? AND submitted_by = ?");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_article = $result->fetch_assoc();
    $stmt->close();

    if (!$edit_article) {
        echo "<p class='error-message'>Article not found or you don't have permission to edit it.</p>";
    }
}

// Fetch all articles for display
$stmt = $conn->prepare("SELECT id, title, abstract, journal, submission_date, status, image_path FROM articles WHERE submitted_by = ? ORDER BY submission_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$articles_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage My Articles</title>
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
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: var(--bg-lighter);
        color: var(--text-color);
        line-height: 1.5;
        padding: 1rem;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
    }

    h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        text-align: left;
    }

    h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .form-card,
    .article-card {
        background-color: var(--bg-color);
        padding: 1.25rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .form-group {
        margin-bottom: 1rem;
    }

    label {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text-color);
        display: block;
        margin-bottom: 0.5rem;
    }

    input[type="text"],
    input[type="file"],
    select,
    textarea {
        width: 100%;
        padding: 0.65rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.9rem;
        background-color: var(--bg-light);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    input[type="text"]:focus,
    input[type="file"]:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: var(--primary-light);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    input[type="file"] {
        padding: 0.5rem;
        background: transparent;
        border: none;
    }

    textarea {
        resize: vertical;
        min-height: 80px;
    }

    .status-readonly {
        font-size: 0.9rem;
        color: var(--text-light);
        background-color: var(--bg-lighter);
        padding: 0.65rem;
        border-radius: 6px;
        border: 1px solid var(--border-color);
    }

    .image-preview {
        max-width: 150px;
        max-height: 150px;
        margin-top: 0.5rem;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid var(--border-light);
    }

    .image-placeholder {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-top: 0.5rem;
    }

    .article-card__image {
        max-width: 100px;
        max-height: 100px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid var(--border-light);
    }

    .btn {
        padding: 0.65rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.1s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        touch-action: manipulation;
    }

    .btn-primary {
        background-color: var(--secondary-color);
        color: var(--bg-color);
        border: none;
    }

    .btn-primary:hover {
        background-color: var(--secondary-light);
        transform: translateY(-1px);
    }

    .btn-outline {
        background-color: transparent;
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
    }

    .btn-outline:hover {
        background-color: var(--primary-light);
        color: var(--bg-color);
        transform: translateY(-1px);
    }

    .error,
    .success {
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 1rem;
        font-size: 0.85rem;
        border: 1px solid;
    }

    .error {
        background-color: rgba(220, 38, 38, 0.05);
        color: var(--error-color);
        border-color: rgba(220, 38, 38, 0.2);
    }

    .success {
        background-color: rgba(5, 150, 105, 0.05);
        color: var(--success-color);
        border-color: rgba(5, 150, 105, 0.2);
    }

    .error ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .error-message {
        color: var(--error-color);
        font-size: 0.85rem;
        text-align: center;
        margin-bottom: 1rem;
    }

    .article-card {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .article-card__field {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .article-card__label {
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-light);
    }

    .article-card__value {
        font-size: 0.9rem;
        color: var(--text-color);
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: capitalize;
        color: var(--bg-color);
        display: inline-flex;
        align-items: center;
    }

    .status-draft {
        background-color: var(--text-lighter);
    }

    .status-submitted,
    .status-under_review {
        background-color: var(--accent-color);
    }

    .status-revision {
        background-color: var(--primary-light);
    }

    .status-approved,
    .status-published {
        background-color: var(--secondary-color);
    }

    .status-rejected {
        background-color: var(--error-color);
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    @media (min-width: 640px) {
        body {
            padding: 1.5rem;
        }

        .container {
            padding: 1.5rem;
        }

        .form-card,
        .article-card {
            padding: 1.5rem;
        }

        .article-card {
            display: grid;
            grid-template-columns: 1fr 2fr 2fr 1fr 1fr 1fr;
            align-items: center;
            gap: 1rem;
        }

        .article-card__field {
            flex-direction: column;
        }

        .article-card__label {
            display: none;
        }

        .article-card__value {
            font-size: 0.9rem;
        }

        .article-card--header {
            background-color: var(--bg-lighter);
            font-weight: 600;
            color: var(--text-color);
        }
    }

    @media (max-width: 640px) {
        h1 {
            font-size: 1.5rem;
        }

        h2 {
            font-size: 1.1rem;
        }

        .btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
        }

        .form-group {
            margin-bottom: 0.75rem;
        }

        input[type="text"],
        input[type="file"],
        select,
        textarea,
        .status-readonly {
            font-size: 0.85rem;
            padding: 0.5rem;
        }

        .image-preview,
        .article-card__image {
            max-width: 80px;
            max-height: 80px;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>Manage My Articles</h1>

        <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
        <div class="success"><?= e($success_msg) ?></div>
        <?php endif; ?>

        <?php if ($edit_article): ?>
        <div class="form-card">
            <h2>Edit Article: <?= e($edit_article['title']) ?></h2>
            <form method="POST" action="manage_articles.php" enctype="multipart/form-data">
                <input type="hidden" name="article_id" value="<?= e($edit_article['id']) ?>">
                <input type="hidden" name="update_article" value="1">

                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required value="<?= e($edit_article['title']) ?>">
                </div>

                <div class="form-group">
                    <label for="abstract">Abstract *</label>
                    <textarea id="abstract" name="abstract" required><?= e($edit_article['abstract']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="journal">Select Journal *</label>
                    <select id="journal" name="journal" required>
                        <?php
                            $journals = [
                                'Sahel Analyst: Journal of Management Sciences',
                                'Journal of Resources & Economic Development (JRED)',
                                'African Journal of Management'
                            ];
                            foreach ($journals as $journal) {
                                $selected = ($edit_article['journal'] === $journal) ? 'selected' : '';
                                echo "<option value=\"" . e($journal) . "\" $selected>" . e($journal) . "</option>";
                            }
                            ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="keywords">Keywords</label>
                    <input type="text" id="keywords" name="keywords" value="<?= e($edit_article['keywords']) ?>">
                </div>

                <div class="form-group">
                    <label for="publication_image">Publication Image</label>
                    <input type="file" id="publication_image" name="publication_image" accept=".jpg,.jpeg,.png">
                    <p style="font-size: 0.85rem; color: var(--text-light); margin-top: 0.5rem;">JPG, JPEG, PNG (Max
                        5MB)</p>
                    <?php if ($edit_article['image_path'] && file_exists(__DIR__ . '/../' . $edit_article['image_path'])): ?>
                    <img src="../<?= e($edit_article['image_path']) ?>" alt="Current Publication Image"
                        class="image-preview">
                    <?php else: ?>
                    <p class="image-placeholder">No image uploaded</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <div class="status-readonly"><?= ucfirst(str_replace('_', ' ', e($edit_article['status']))) ?></div>
                </div>

                <div class="form-group action-buttons">
                    <button type="submit" class="btn btn-primary">Update Article</button>
                    <a href="manage_articles.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="article-list">
            <?php if ($articles_result->num_rows === 0): ?>
            <div class="article-card">
                <p>No articles found.</p>
            </div>
            <?php else: ?>
            <div class="article-card article-card--header">
                <div class="article-card__field">
                    <span class="article-card__value">Image</span>
                </div>
                <div class="article-card__field">
                    <span class="article-card__value">Title</span>
                </div>
                <div class="article-card__field">
                    <span class="article-card__value">Journal</span>
                </div>
                <div class="article-card__field">
                    <span class="article-card__value">Submission Date</span>
                </div>
                <div class="article-card__field">
                    <span class="article-card__value">Status</span>
                </div>
                <div class="article-card__field">
                    <span class="article-card__value">Actions</span>
                </div>
            </div>
            <?php
                    function statusBadgeClass($status)
                    {
                        $map = [
                            'draft' => 'status-draft',
                            'submitted' => 'status-submitted',
                            'under_review' => 'status-under_review',
                            'revision' => 'status-revision',
                            'approved' => 'status-approved',
                            'published' => 'status-published',
                            'rejected' => 'status-rejected',
                        ];
                        return $map[$status] ?? 'status-submitted';
                    }
                    while ($article = $articles_result->fetch_assoc()):
                    ?>
            <div class="article-card">
                <div class="article-card__field">
                    <span class="article-card__label">Image</span>
                    <?php if ($article['image_path'] && file_exists(__DIR__ . '/../' . $article['image_path'])): ?>
                    <img src="../<?= e($article['image_path']) ?>" alt="Article Image" class="article-card__image">
                    <?php else: ?>
                    <span class="article-card__value">No Image</span>
                    <?php endif; ?>
                </div>
                <div class="article-card__field">
                    <span class="article-card__label">Title</span>
                    <span class="article-card__value"><?= e($article['title']) ?></span>
                </div>
                <div class="article-card__field">
                    <span class="article-card__label">Journal</span>
                    <span class="article-card__value"><?= e($article['journal']) ?></span>
                </div>
                <div class="article-card__field">
                    <span class="article-card__label">Submission Date</span>
                    <span
                        class="article-card__value"><?= date("M d, Y", strtotime($article['submission_date'])) ?></span>
                </div>
                <div class="article-card__field">
                    <span class="article-card__label">Status</span>
                    <span
                        class="status-badge <?= statusBadgeClass($article['status']) ?>"><?= ucfirst(str_replace('_', ' ', $article['status'])) ?></span>
                </div>
                <div class="article-card__field action-buttons">
                    <span class="article-card__label">Actions</span>
                    <a href="manage_articles.php?edit=<?= $article['id'] ?>" class="btn btn-outline">Edit</a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>
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

    // Simple validation (expand as needed)
    $errors = [];
    if ($title === '') $errors[] = "Title is required.";
    if ($abstract === '') $errors[] = "Abstract is required.";
    if ($journal === '') $errors[] = "Journal selection is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE articles SET title=?, abstract=?, journal=?, keywords=?, last_update=NOW() WHERE id=? AND submitted_by=?");
        $stmt->bind_param("ssssii", $title, $abstract, $journal, $keywords, $article_id, $user_id);
        $stmt->execute();
        if ($stmt->affected_rows >= 0) {
            $success_msg = "Article updated successfully.";
        } else {
            $errors[] = "Failed to update article.";
        }
        $stmt->close();
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
$stmt = $conn->prepare("SELECT id, title, abstract, journal, submission_date, status FROM articles WHERE submitted_by = ? ORDER BY submission_date DESC");
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
            padding: 1.5rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 1.5rem;
            background-color: var(--bg-color);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
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
        .table-card {
            background-color: var(--bg-light);
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-color);
            display: block;
            margin-bottom: 0.5rem;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.9rem;
            background-color: var(--bg-color);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .status-readonly {
            font-size: 0.9rem;
            color: var(--text-light);
            background-color: var(--bg-lighter);
            padding: 0.75rem;
            border-radius: 6px;
            border: 1px solid var(--border-color);
        }

        .btn {
            padding: 0.75rem 1.25rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
        }

        .error {
            background-color: rgba(220, 38, 38, 0.05);
            color: var(--error-color);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .success {
            background-color: rgba(5, 150, 105, 0.05);
            color: var(--success-color);
            border: 1px solid rgba(5, 150, 105, 0.2);
        }

        .error ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .error-message {
            color: var(--error-color);
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 1.25rem;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9rem;
        }

        th,
        td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }

        th {
            background-color: var(--bg-lighter);
            font-weight: 600;
            color: var(--text-color);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
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

        .action-buttons .btn {
            padding: 0.5rem 1rem;
        }

        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            th,
            td {
                padding: 0.5rem;
                font-size: 0.85rem;
            }

            .btn {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
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
                <form method="POST" action="manage_articles.php">
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
                        <label>Status</label>
                        <div class="status-readonly"><?= ucfirst(str_replace('_', ' ', e($edit_article['status']))) ?></div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Article</button>
                        <a href="manage_articles.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Journal</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($articles_result->num_rows === 0): ?>
                            <tr>
                                <td colspan="5">No articles found.</td>
                            </tr>
                        <?php else: ?>
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
                                <tr>
                                    <td><?= e($article['title']) ?></td>
                                    <td><?= e($article['journal']) ?></td>
                                    <td><?= date("M d, Y", strtotime($article['submission_date'])) ?></td>
                                    <td><span
                                            class="status-badge <?= statusBadgeClass($article['status']) ?>"><?= ucfirst(str_replace('_', ' ', $article['status'])) ?></span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="manage_articles.php?edit=<?= $article['id'] ?>" class="btn btn-outline">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Set error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Connect to DB
$host = "localhost";
$db = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("DB Connection failed: " . $conn->connect_error);
    die("Database connection failed.");
}

// Get article ID from GET
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($article_id <= 0) {
    die("Invalid article ID.");
}

// Fetch article details
try {
    $stmt = $conn->prepare("SELECT id, title, journal, status, submitted_by, submission_date, last_update FROM articles WHERE id = ?");
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
} catch (Exception $e) {
    error_log("Fetch article error: " . $e->getMessage());
    die("Error fetching article.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        $title = trim($_POST['title'] ?? '');
        $journal = trim($_POST['journal'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $allowed_statuses = ['draft', 'submitted', 'under_review', 'revision', 'approved', 'published', 'rejected'];

        // Validation
        if (empty($title) || empty($journal) || !in_array($status, $allowed_statuses)) {
            throw new Exception('Required fields are missing or invalid.');
        }

        // Update article
        $last_update = date('Y-m-d H:i:s'); // Current timestamp
        $stmt = $conn->prepare("UPDATE articles SET title = ?, journal = ?, status = ?, last_update = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssssi", $title, $journal, $status, $last_update, $article_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $stmt->close();
        echo json_encode(['success' => 'Article updated successfully']);
    } catch (Exception $e) {
        error_log("Update article error: " . $e->getMessage());
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    $conn->close();
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article - Journal Platform</title>
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

    .container {
        max-width: 800px;
    }

    h1 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .form-label {
        font-weight: 500;
        color: var(--primary-color);
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        transition: border-color 0.2s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.25);
    }

    .form-control[readonly] {
        background-color: #e9ecef;
    }

    .btn-primary {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1rem;
    }

    .btn-primary:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }

    .btn-outline-primary {
        border-color: var(--primary-color);
        color: var(--primary-color);
        border-radius: 8px;
    }

    .alert {
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>Edit Article</h1>
        <a href="manage_articles.php?status=<?php echo htmlspecialchars($article['status']); ?>"
            class="btn btn-outline-primary mb-4">Back to Articles</a>
        <div id="alertContainer"></div>
        <form id="editForm">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($article['id']); ?>">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title"
                    value="<?php echo htmlspecialchars($article['title']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="journal" class="form-label">Journal</label>
                <input type="text" class="form-control" id="journal" name="journal"
                    value="<?php echo htmlspecialchars($article['journal']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="draft" <?php echo $article['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="submitted" <?php echo $article['status'] == 'submitted' ? 'selected' : ''; ?>>
                        Submitted</option>
                    <option value="under_review" <?php echo $article['status'] == 'under_review' ? 'selected' : ''; ?>>
                        Under Review</option>
                    <option value="revision" <?php echo $article['status'] == 'revision' ? 'selected' : ''; ?>>Revision
                    </option>
                    <option value="approved" <?php echo $article['status'] == 'approved' ? 'selected' : ''; ?>>Approved
                    </option>
                    <option value="published" <?php echo $article['status'] == 'published' ? 'selected' : ''; ?>>
                        Published</option>
                    <option value="rejected" <?php echo $article['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <label for="submitted_by" class="form-label">Submitted By (User ID)</label>
                <input type="text" class="form-control" id="submitted_by"
                    value="<?php echo htmlspecialchars($article['submitted_by']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="submission_date" class="form-label">Submission Date</label>
                <input type="text" class="form-control" id="submission_date"
                    value="<?php echo htmlspecialchars($article['submission_date']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="last_update" class="form-label">Last Update</label>
                <input type="text" class="form-control" id="last_update"
                    value="<?php echo htmlspecialchars($article['last_update']); ?>" readonly>
            </div>
            <button type="submit" class="btn btn-primary" id="saveChangesBtn">Save Changes</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showAlert(message, type = 'danger') {
        const alertContainer = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
        alertContainer.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const saveBtn = document.getElementById('saveChangesBtn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        const formData = new FormData(this);

        fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showAlert(data.success, 'success');
                    setTimeout(() => window.location.href =
                        'manage_articles.php?status=<?php echo htmlspecialchars($article['status']); ?>',
                        1000);
                } else {
                    showAlert(data.error || 'Failed to update article.', 'danger');
                }
            })
            .catch(error => {
                console.error('Edit error:', error);
                showAlert('An error occurred while updating the article.', 'danger');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Changes';
            });
    });
    </script>
</body>

</html>
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

// Handle file streaming for documents
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['file'])) {
    $base_path = realpath(__DIR__ . '/../../public/uploads');
    $file_path = urldecode($_GET['file']);
    $relative_path = preg_replace('#^uploads[/\\\\]#i', '', $file_path);
    $absolute_path = realpath($base_path . '/' . $relative_path);

    error_log("File path requested: $file_path");
    error_log("Relative path: $relative_path");
    error_log("Base path: $base_path");
    error_log("Absolute path: $absolute_path");

    if ($absolute_path && file_exists($absolute_path) && strpos($absolute_path, $base_path) === 0) {
        $mime_type = mime_content_type($absolute_path);
        header('Content-Type: ' . $mime_type);
        if (pathinfo($absolute_path, PATHINFO_EXTENSION) !== 'pdf') {
            header('Content-Disposition: attachment; filename="' . basename($absolute_path) . '"');
        }
        readfile($absolute_path);
        exit;
    } else {
        error_log("Invalid or missing file: $absolute_path");
        header('HTTP/1.1 404 Not Found');
        echo "File not found.";
        exit;
    }
}

// Handle AJAX requests for edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    try {
        if ($_POST['action'] === 'edit') {
            $id = intval($_POST['id']);
            $title = trim($_POST['title'] ?? '');
            $abstract = trim($_POST['abstract'] ?? '');
            $keywords = trim($_POST['keywords'] ?? '') ?: NULL;
            $status = trim($_POST['status'] ?? '');
            $doi = trim($_POST['doi'] ?? '') ?: NULL;
            $published_date = trim($_POST['published_date'] ?? '') ?: NULL;
            $file_path = trim($_POST['file_path'] ?? '');
            $journal = trim($_POST['journal'] ?? '');

            // Validation
            if (empty($title) || empty($abstract) || empty($file_path) || empty($journal)) {
                throw new Exception('Required fields are missing.');
            }
            $valid_statuses = ['draft', 'submitted', 'under_review', 'revision', 'approved', 'published', 'rejected'];
            if (!in_array($status, $valid_statuses)) {
                throw new Exception('Invalid status.');
            }
            $valid_journals = [
                'Sahel Analyst: Journal of Management Sciences',
                'Journal of Resources & Economic Development (JRED)',
                'African Journal of Management'
            ];
            if (!in_array($journal, $valid_journals)) {
                throw new Exception('Invalid journal.');
            }
            // Validate and convert published_date
            if ($published_date) {
                // Convert YYYY-MM-DDThh:mm:ss to YYYY-MM-DD HH:MM:SS
                $date = DateTime::createFromFormat('Y-m-d\TH:i:s', $published_date);
                if (!$date) {
                    // Try without seconds
                    $date = DateTime::createFromFormat('Y-m-d\TH:i', $published_date);
                    if ($date) {
                        $published_date = $date->format('Y-m-d H:i:00');
                    } else {
                        throw new Exception('Invalid published date format.');
                    }
                } else {
                    $published_date = $date->format('Y-m-d H:i:s');
                }
            }

            // Handle publication image upload
            $image_path = null;
            if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] === UPLOAD_ERR_OK) {
                $image = $_FILES['image_path'];
                $allowed_ext = ['jpg', 'jpeg', 'png'];
                $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_ext)) {
                    throw new Exception('Invalid image file type. Only JPG, JPEG, PNG allowed.');
                }
                if ($image['size'] > 5 * 1024 * 1024) { // 5MB limit
                    throw new Exception('Image file size exceeds 5MB.');
                }

                $upload_dir = __DIR__ . '/../../public/uploads/images/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $new_filename = uniqid('image_', true) . '.' . $ext;
                $destination = $upload_dir . $new_filename;
                if (!move_uploaded_file($image['tmp_name'], $destination)) {
                    throw new Exception('Failed to upload image.');
                }
                $image_path = 'uploads/images/' . $new_filename;

                // Delete old image if it exists
                $stmt = $conn->prepare("SELECT image_path FROM articles WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                if ($result['image_path'] && file_exists(__DIR__ . '/../../public/' . $result['image_path'])) {
                    unlink(__DIR__ . '/../../public/' . $result['image_path']);
                }
                $stmt->close();
            }

            // Fetch existing image path if no new image is uploaded
            if (!$image_path) {
                $stmt = $conn->prepare("SELECT image_path FROM articles WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $image_path = $result['image_path'] ?? null;
                $stmt->close();
            }

            // Update article
            $stmt = $conn->prepare("UPDATE articles SET title = ?, abstract = ?, keywords = ?, status = ?, doi = ?, published_date = ?, file_path = ?, journal = ?, image_path = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sssssssssi", $title, $abstract, $keywords, $status, $doi, $published_date, $file_path, $journal, $image_path, $id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $stmt->close();
            echo json_encode(['success' => 'Article updated successfully']);
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            // Delete image if it exists
            $stmt = $conn->prepare("SELECT image_path FROM articles WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['image_path'] && file_exists(__DIR__ . '/../../public/' . $result['image_path'])) {
                unlink(__DIR__ . '/../../public/' . $result['image_path']);
            }
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $stmt->close();
            echo json_encode(['success' => 'Article deleted successfully']);
        } else {
            throw new Exception('Invalid action.');
        }
    } catch (Exception $e) {
        error_log("Manage articles error: " . $e->getMessage());
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    $conn->close();
    exit;
}

// Fetch all articles with submitter names
try {
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.abstract, a.keywords, a.submission_date, a.last_update, a.status, a.doi, 
               a.published_date, a.file_path, a.journal, a.submitted_by, a.image_path,
               CONCAT(u.first_name, ' ', u.last_name) AS submitter_name
        FROM articles a
        LEFT JOIN users u ON a.submitted_by = u.id
        ORDER BY a.submission_date DESC
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Fetch articles error: " . $e->getMessage());
    $articles = [];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Articles - Journal Platform</title>
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

    .article-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 12px;
        overflow: hidden;
        background: white;
    }

    .article-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: var(--primary-color);
        color: white;
        font-weight: 600;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .article-image {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid white;
    }

    .article-image-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        background-color: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        font-size: 0.9rem;
        border: 2px solid white;
    }

    .card-body {
        padding: 1.5rem;
    }

    .article-info {
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
    }

    .article-info strong {
        color: var(--primary-color);
    }

    .btn-edit,
    .btn-delete,
    .btn-view {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1rem;
    }

    .btn-edit {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-delete {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }

    .btn-view {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background: var(--primary-color);
        color: white;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    .modal-body {
        padding: 1.5rem;
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

    .image-preview {
        max-width: 100px;
        max-height: 100px;
        border-radius: 8px;
        object-fit: cover;
        margin-top: 0.5rem;
        border: 1px solid #d1d5db;
    }

    .alert {
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .btn-primary:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }

    .document-viewer {
        height: 500px;
        overflow: hidden;
    }

    .document-viewer iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    @media (max-width: 576px) {
        .article-card {
            margin-bottom: 1.5rem;
        }

        .card-body {
            padding: 1rem;
        }

        .btn-edit,
        .btn-delete,
        .btn-view {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .document-viewer {
            height: 300px;
        }

        .article-image,
        .article-image-placeholder {
            width: 40px;
            height: 40px;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="my-4 text-center" style="color: var(--primary-color);">Manage Articles</h1>
        <a href="admin_dash.php" class="btn btn-outline-primary mb-4">Back to Dashboard</a>
        <div id="alertContainer"></div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($articles as $article) : ?>
            <div class="col">
                <div class="card article-card">
                    <div class="card-header">
                        <?php if ($article['image_path'] && file_exists(__DIR__ . '/../../public/' . $article['image_path'])) : ?>
                        <img src="../../public/<?= htmlspecialchars($article['image_path']) ?>" alt="Publication Image"
                            class="article-image">
                        <?php else : ?>
                        <div class="article-image-placeholder">No Image</div>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($article['title']); ?></span>
                    </div>
                    <div class="card-body">
                        <p class="article-info"><strong>Journal:</strong>
                            <?php echo htmlspecialchars($article['journal']); ?></p>
                        <p class="article-info"><strong>Status:</strong>
                            <?php echo htmlspecialchars($article['status']); ?></p>
                        <p class="article-info"><strong>Submitted By:</strong>
                            <?php echo htmlspecialchars($article['submitter_name'] ?? 'Unknown'); ?></p>
                        <p class="article-info"><strong>Submission Date:</strong>
                            <?php echo htmlspecialchars($article['submission_date'] ?? 'N/A'); ?></p>
                        <p class="article-info"><strong>DOI:</strong>
                            <?php echo htmlspecialchars($article['doi'] ?? 'N/A'); ?></p>
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button class="btn btn-edit btn-sm"
                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($article, JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">Edit</button>
                            <button class="btn btn-delete btn-sm"
                                onclick="deleteArticle(<?php echo $article['id']; ?>)">Delete</button>
                            <button class="btn btn-view btn-sm"
                                onclick="openViewModal('<?php echo htmlspecialchars($article['file_path'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8'); ?>')">View
                                Document</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Article</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" enctype="multipart/form-data">
                        <input type="hidden" id="editId" name="id">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAbstract" class="form-label">Abstract</label>
                            <textarea class="form-control" id="editAbstract" name="abstract" rows="4"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editKeywords" class="form-label">Keywords</label>
                            <input type="text" class="form-control" id="editKeywords" name="keywords">
                        </div>
                        <div class="mb-3">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-select" id="editStatus" name="status" required>
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="under_review">Under Review</option>
                                <option value="revision">Revision</option>
                                <option value="approved">Approved</option>
                                <option value="published">Published</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editDoi" class="form-label">DOI</label>
                            <input type="text" class="form-control" id="editDoi" name="doi">
                        </div>
                        <div class="mb-3">
                            <label for="editPublishedDate" class="form-label">Published Date</label>
                            <input type="datetime-local" class="form-control" id="editPublishedDate"
                                name="published_date">
                            <small class="form-text text-muted">Select date and time or leave blank if not
                                published.</small>
                        </div>
                        <div class="mb-3">
                            <label for="editFilePath" class="form-label">File Path</label>
                            <input type="text" class="form-control" id="editFilePath" name="file_path" required>
                        </div>
                        <div class="mb-3">
                            <label for="editImagePath" class="form-label">Publication Image</label>
                            <input type="file" class="form-control" id="editImagePath" name="image_path"
                                accept=".jpg,.jpeg,.png">
                            <small class="form-text text-muted">JPG, JPEG, PNG (Max 5MB)</small>
                            <div id="imagePreview" class="mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label for="editJournal" class="form-label">Journal</label>
                            <select class="form-select" id="editJournal" name="journal" required>
                                <option value="Sahel Analyst: Journal of Management Sciences">Sahel Analyst: Journal of
                                    Management Sciences</option>
                                <option value="Journal of Resources & Economic Development (JRED)">Journal of Resources
                                    & Economic Development (JRED)</option>
                                <option value="African Journal of Management">African Journal of Management</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" id="saveChangesBtn">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Document Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">View Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="documentViewer" class="document-viewer"></div>
                </div>
            </div>
        </div>
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

    function openEditModal(article) {
        try {
            document.getElementById('editId').value = article.id || '';
            document.getElementById('editTitle').value = article.title || '';
            document.getElementById('editAbstract').value = article.abstract || '';
            document.getElementById('editKeywords').value = article.keywords || '';
            document.getElementById('editStatus').value = article.status || 'draft';
            document.getElementById('editDoi').value = article.doi || '';
            // Convert YYYY-MM-DD HH:MM:SS to YYYY-MM-DDThh:mm for datetime-local
            if (article.published_date) {
                const date = new Date(article.published_date);
                const formattedDate = date.toISOString().slice(0, 16); // YYYY-MM-DDThh:mm
                document.getElementById('editPublishedDate').value = formattedDate;
            } else {
                document.getElementById('editPublishedDate').value = '';
            }
            document.getElementById('editFilePath').value = article.file_path || '';
            document.getElementById('editJournal').value = article.journal ||
                'Sahel Analyst: Journal of Management Sciences';

            // Display current image
            const preview = document.getElementById('imagePreview');
            if (article.image_path && article.image_path !== '') {
                preview.innerHTML =
                    `<img src="../../public/${article.image_path}" alt="Publication Image" class="image-preview">`;
            } else {
                preview.innerHTML = '<p class="text-muted">No image uploaded</p>';
            }

            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        } catch (e) {
            console.error('Error opening edit modal:', e);
            showAlert('Failed to open edit modal.', 'danger');
        }
    }

    function openViewModal(filePath, title) {
        try {
            const viewer = document.getElementById('documentViewer');
            const extension = filePath.split('.').pop().toLowerCase();
            viewer.innerHTML = '';

            document.getElementById('viewModalLabel').textContent = `View Document: ${title}`;

            if (extension === 'pdf') {
                const iframe = document.createElement('iframe');
                iframe.src = `manage_articles.php?file=${encodeURIComponent(filePath)}`;
                iframe.title = 'Document Viewer';
                viewer.appendChild(iframe);
            } else {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-info';
                alertDiv.innerHTML = `
                        This file type cannot be viewed inline.
                        <a href="manage_articles.php?file=${encodeURIComponent(filePath)}" class="btn btn-primary btn-sm" download>Download Document</a>
                    `;
                viewer.appendChild(alertDiv);
            }

            const modal = new bootstrap.Modal(document.getElementById('viewModal'));
            modal.show();
        } catch (e) {
            console.error('Error opening view modal:', e);
            showAlert('Failed to open document viewer.', 'danger');
        }
    }

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const saveBtn = document.getElementById('saveChangesBtn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        const formData = new FormData(this);
        formData.append('action', 'edit');

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
                    setTimeout(() => location.reload(), 1000);
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

    function deleteArticle(id) {
        if (!confirm('Are you sure you want to delete this article? This action cannot be undone.')) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.success, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.error || 'Failed to delete article.', 'danger');
                }
            })
            .catch(() => showAlert('An error occurred while deleting the article.', 'danger'));
    }
    </script>
</body>

</html>
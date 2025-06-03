<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
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

// Handle AJAX requests for edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    try {
        if ($_POST['action'] === 'edit') {
            $id = intval($_POST['id']);
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '') ?: NULL;
            $country = trim($_POST['country'] ?? '');
            $institution = trim($_POST['institution'] ?? '');
            $position = trim($_POST['position'] ?? '');
            $research_interests = trim($_POST['research_interests'] ?? '') ?: NULL;
            $wants_newsletter = isset($_POST['wants_newsletter']) && $_POST['wants_newsletter'] === 'on' ? 1 : 0;
            $is_reviewer = isset($_POST['is_reviewer']) && $_POST['is_reviewer'] === 'on' ? 1 : 0;
            $is_approved = isset($_POST['is_approved']) && $_POST['is_approved'] === 'on' ? 1 : 0;
            $is_paid = isset($_POST['is_paid']) && $_POST['is_paid'] === 'on' ? 1 : 0;
            $orcid = trim($_POST['orcid'] ?? '') ?: NULL;
            $linkedin = trim($_POST['linkedin'] ?? '') ?: NULL;

            // Validation
            if (empty($first_name) || empty($last_name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($country) || empty($institution) || empty($position)) {
                throw new Exception('Required fields are missing or invalid.');
            }

            // Check for duplicate email (excluding current user)
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception('Email is already in use by another user.');
            }
            $stmt->close();

            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, country = ?, institution = ?, position = ?, research_interests = ?, wants_newsletter = ?, is_reviewer = ?, is_approved = ?, is_paid = ?, orcid = ?, linkedin = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ssssssssiiiiisi", $first_name, $last_name, $email, $phone, $country, $institution, $position, $research_interests, $wants_newsletter, $is_reviewer, $is_approved, $is_paid, $orcid, $linkedin, $id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $stmt->close();
            echo json_encode(['success' => 'User updated successfully']);
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $stmt->close();
            echo json_encode(['success' => 'User deleted successfully']);
        } else {
            throw new Exception('Invalid action.');
        }
    } catch (Exception $e) {
        error_log("Manage users error: " . $e->getMessage());
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
    $conn->close();
    exit;
}

// Fetch all users
try {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, country, institution, position, research_interests, wants_newsletter, is_reviewer, is_approved, is_paid, orcid, linkedin, registration_date, last_login FROM users ORDER BY registration_date DESC");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Fetch users error: " . $e->getMessage());
    $users = [];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Journal Platform</title>
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

        .user-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            background: white;
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .user-info {
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .user-info strong {
            color: var(--primary-color);
        }

        .btn-edit,
        .btn-delete {
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

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
        }

        .form-control,
        .form-check-input {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.25);
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .btn-primary:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        @media (max-width: 576px) {
            .user-card {
                margin-bottom: 1.5rem;
            }

            .card-body {
                padding: 1rem;
            }

            .btn-edit,
            .btn-delete {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="my-4 text-center" style="color: var(--primary-color);">Manage Users</h1>
        <a href="admin_dash.php" class="btn btn-outline-primary mb-4">Back to Dashboard</a>
        <div id="alertContainer"></div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($users as $user): ?>
                <div class="col">
                    <div class="card user-card">
                        <div class="card-header">
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        </div>
                        <div class="card-body">
                            <p class="user-info"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="user-info"><strong>Institution:</strong>
                                <?php echo htmlspecialchars($user['institution']); ?></p>
                            <p class="user-info"><strong>Position:</strong>
                                <?php echo htmlspecialchars($user['position']); ?></p>
                            <p class="user-info"><strong>Country:</strong> <?php echo htmlspecialchars($user['country']); ?>
                            </p>
                            <p class="user-info"><strong>Reviewer:</strong>
                                <?php echo $user['is_reviewer'] ? 'Yes' : 'No'; ?></p>
                            <p class="user-info"><strong>Approved:</strong>
                                <?php echo $user['is_approved'] ? 'Yes' : 'No'; ?></p>
                            <p class="user-info"><strong>Paid:</strong> <?php echo $user['is_paid'] ? 'Yes' : 'No'; ?></p>
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button class="btn btn-edit btn-sm"
                                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user, JSON_HEX_APOS | JSON_HEX_QUOT)); ?>)">Edit</button>
                                <button class="btn btn-delete btn-sm"
                                    onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
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
                    <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editId" name="id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editFirstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="editFirstName" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editLastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="editLastName" name="last_name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="editPhone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="editCountry" class="form-label">Country</label>
                            <input type="text" class="form-control" id="editCountry" name="country" required>
                        </div>
                        <div class="mb-3">
                            <label for="editInstitution" class="form-label">Institution</label>
                            <input type="text" class="form-control" id="editInstitution" name="institution" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPosition" class="form-label">Position</label>
                            <input type="text" class="form-control" id="editPosition" name="position" required>
                        </div>
                        <div class="mb-3">
                            <label for="editResearchInterests" class="form-label">Research Interests</label>
                            <textarea class="form-control" id="editResearchInterests" name="research_interests"
                                rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editOrcid" class="form-label">ORCID</label>
                            <input type="text" class="form-control" id="editOrcid" name="orcid">
                        </div>
                        <div class="mb-3">
                            <label for="editLinkedin" class="form-label">LinkedIn</label>
                            <input type="text" class="form-control" id="editLinkedin" name="linkedin">
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editWantsNewsletter"
                                        name="wants_newsletter">
                                    <label class="form-check-label" for="editWantsNewsletter">Wants Newsletter</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editIsReviewer"
                                        name="is_reviewer">
                                    <label class="form-check-label" for="editIsReviewer">Is Reviewer</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editIsApproved"
                                        name="is_approved">
                                    <label class="form-check-label" for="editIsApproved">Is Approved</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editIsPaid" name="is_paid">
                                    <label class="form-check-label" for="editIsPaid">Is Paid</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="saveChangesBtn">Save Changes</button>
                    </form>
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

        function openEditModal(user) {
            try {
                document.getElementById('editId').value = user.id || '';
                document.getElementById('editFirstName').value = user.first_name || '';
                document.getElementById('editLastName').value = user.last_name || '';
                document.getElementById('editEmail').value = user.email || '';
                document.getElementById('editPhone').value = user.phone || '';
                document.getElementById('editCountry').value = user.country || '';
                document.getElementById('editInstitution').value = user.institution || '';
                document.getElementById('editPosition').value = user.position || '';
                document.getElementById('editResearchInterests').value = user.research_interests || '';
                document.getElementById('editOrcid').value = user.orcid || '';
                document.getElementById('editLinkedin').value = user.linkedin || '';
                document.getElementById('editWantsNewsletter').checked = !!user.wants_newsletter;
                document.getElementById('editIsReviewer').checked = !!user.is_reviewer;
                document.getElementById('editIsApproved').checked = !!user.is_approved;
                document.getElementById('editIsPaid').checked = !!user.is_paid;

                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                modal.show();
            } catch (e) {
                console.error('Error opening edit modal:', e);
                showAlert('Failed to open edit modal.', 'danger');
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
                        showAlert(data.error || 'Failed to update user.', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Edit error:', error);
                    showAlert('An error occurred while updating the user.', 'danger');
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Changes';
                });
        });

        function deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;

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
                        showAlert(data.error || 'Failed to delete user.', 'danger');
                    }
                })
                .catch(() => showAlert('An error occurred while deleting the user.', 'danger'));
        }
    </script>
</body>

</html>
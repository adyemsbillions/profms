<?php
session_start();
require('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $fullname = trim($_POST['fullname'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $orcid = trim($_POST['orcid'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');

    // Split fullname into first_name and last_name
    $name_parts = explode(' ', $fullname, 2);
    $first_name = $name_parts[0] ?? '';
    $last_name = $name_parts[1] ?? '';

    // Basic validation
    $errors = [];
    if (empty($fullname)) {
        $errors[] = 'Full name is required.';
    }
    if (!empty($orcid) && !preg_match('/^\d{4}-\d{4}-\d{4}-\d{4}$/', $orcid)) {
        $errors[] = 'ORCID ID must be in the format XXXX-XXXX-XXXX-XXXX.';
    }
    if (!empty($linkedin) && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
        $errors[] = 'LinkedIn profile must be a valid URL.';
    }

    if (empty($errors)) {
        // Update user data in the database (excluding email)
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, institution = ?, position = ?, research_interests = ?, orcid = ?, linkedin = ? WHERE id = ?");
        $stmt->bind_param("sssssssi", $first_name, $last_name, $institution, $position, $bio, $orcid, $linkedin, $user_id);

        if ($stmt->execute()) {
            $success_message = 'Profile updated successfully!';
        } else {
            $error_message = 'Failed to update profile. Please try again.';
        }
        $stmt->close();
    } else {
        $error_message = implode(' ', $errors);
    }
}

// Fetch user data (after update to reflect changes)
$stmt = $conn->prepare("SELECT first_name, last_name, email, institution, position, research_interests, orcid, linkedin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Profile</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f7fc;
            color: #1f2937;
            line-height: 1.5;
            padding: 2rem;
        }

        /* Messages */
        .message {
            max-width: 800px;
            margin: 1rem auto;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
        }

        .message.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
        }

        /* Page header */
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* Form container */
        .form-container {
            background: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .form-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        /* Profile grid */
        .profile-grid {
            display: flex;
            gap: 2.5rem;
            align-items: flex-start;
        }

        .profile-avatar-section {
            flex: 0 0 160px;
            text-align: center;
        }

        .user-avatar.profile-avatar {
            width: 128px;
            height: 128px;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white;
            font-weight: 700;
            font-size: 3.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            user-select: none;
            transition: transform 0.3s ease;
        }

        .user-avatar.profile-avatar:hover {
            transform: scale(1.05);
        }

        /* Form fields */
        .profile-grid>div:nth-child(2) {
            flex: 1;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 0.875rem;
            font-size: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #f9fafb;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input[readonly] {
            background: #e5e7eb;
            cursor: not-allowed;
            color: #6b7280;
        }

        .form-input:focus:not([readonly]),
        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Buttons */
        .btn {
            padding: 0.875rem 1.75rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #1e3a8a;
            color: #1e3a8a;
        }

        .btn-outline:hover {
            background: #1e3a8a;
            color: white;
            transform: translateY(-1px);
        }

        .btn-primary {
            background: #1e3a8a;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .profile-grid {
                flex-direction: column;
                align-items: center;
            }

            .profile-avatar-section {
                flex: none;
                margin-bottom: 2rem;
            }

            .form-container {
                padding: 1.5rem;
            }

            .page-header {
                text-align: left;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .page-subtitle {
                font-size: 1rem;
            }

            .btn {
                padding: 0.75rem 1.25rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="page-header">
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your account information and settings</p>
    </div>

    <?php if ($success_message): ?>
        <div class="message success"><?= e($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="message error"><?= e($error_message) ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form id="profileForm" method="POST" action="">
            <div class="profile-grid">
                <div class="profile-avatar-section">
                    <div class="user-avatar profile-avatar">
                        <?= e(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                    </div>
                    <button type="button" class="btn btn-outline"
                        onclick="alert('Change Photo feature coming soon!')">Change Photo</button>
                </div>
                <div>
                    <div class="form-group">
                        <label class="form-label" for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" class="form-input"
                            value="<?= e($user['first_name'] . ' ' . $user['last_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input" value="<?= e($user['email']) ?>"
                            readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="institution">Institution</label>
                        <input type="text" id="institution" name="institution" class="form-input"
                            value="<?= e($user['institution'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="position">Designation</label>
                        <input type="text" id="position" name="position" class="form-input"
                            value="<?= e($user['position'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="bio">Bio</label>
                        <textarea id="bio" name="bio" class="form-textarea" rows="4"
                            placeholder="Tell us about yourself"><?= e($user['research_interests'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="orcid">ORCID ID</label>
                        <input type="text" id="orcid" name="orcid" class="form-input" placeholder="0000-0000-0000-0000"
                            value="<?= e($user['orcid'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="linkedin">LinkedIn Profile</label>
                        <input type="url" id="linkedin" name="linkedin" class="form-input"
                            placeholder="https://linkedin.com/in/username" value="<?= e($user['linkedin'] ?? '') ?>">
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <button type="button" class="btn btn-outline"
                            onclick="window.location.href='change_password.php'">Change Password</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>

</html>
<?php
// Connect to DB
$host = "localhost";
$db = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Get status filter from GET or default to 'draft'
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'draft';

// Validate $status_filter against allowed values
$allowed_statuses = ['draft', 'submitted', 'under_review', 'revision', 'approved', 'published', 'rejected'];
if (!in_array($status_filter, $allowed_statuses)) {
    die("Invalid status filter.");
}

// Prepare statement to fetch articles with this status
$stmt = $conn->prepare("SELECT id, title, submission_date, last_update, status, submitted_by, journal FROM articles WHERE status = ? ORDER BY last_update DESC");
$stmt->bind_param("s", $status_filter);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Manage Articles - Status: <?php echo htmlspecialchars($status_filter); ?></title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 2rem;
        background: #f9f9f9;
    }

    h1 {
        color: #1e3a8a;
        margin-bottom: 1rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    th,
    td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    th {
        background: #1e3a8a;
        color: white;
        font-weight: 600;
    }

    tr:hover {
        background: #f1f5f9;
    }

    a.edit-link {
        color: #059669;
        font-weight: 600;
        text-decoration: none;
    }

    a.edit-link:hover {
        text-decoration: underline;
    }

    .status-badge {
        padding: 3px 8px;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: capitalize;
    }

    .status-draft {
        background-color: #6b7280;
    }

    .status-submitted {
        background-color: #3b82f6;
    }

    .status-under_review {
        background-color: #f59e0b;
    }

    .status-revision {
        background-color: #f97316;
    }

    .status-approved {
        background-color: #059669;
    }

    .status-published {
        background-color: #047857;
    }

    .status-rejected {
        background-color: #dc2626;
    }

    /* Styling for the select element */
    select {
        font-family: 'Arial', sans-serif;
        font-size: 1rem;
        padding: 10px 15px;
        border: 2px solid #1e3a8a;
        /* Primary color */
        border-radius: 8px;
        background-color: #ffffff;
        /* White background */
        color: #333333;
        /* Text color */
        appearance: none;
        /* Remove default arrow for custom styling */
        cursor: pointer;
        width: 200px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    /* Styling for the select when it's focused (hover or selected) */
    select:focus {
        outline: none;
        border-color: #059669;
        /* Green border on focus */
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
        /* Light blue glow effect */
    }

    /* Custom arrow using CSS */
    select::-ms-expand {
        display: none;
        /* Remove default arrow in IE */
    }

    /* Styling for the dropdown options */
    select option {
        padding: 10px;
        font-size: 1rem;
        background-color: #ffffff;
        /* White background for options */
        color: #333333;
        /* Text color for options */
    }

    /* Styling for the label of the select (optional) */
    label {
        font-size: 1rem;
        font-weight: 600;
        margin-right: 10px;
        color: #333333;
    }

    /* Hover effect for the select */
    select:hover {
        border-color: #3b82f6;
        /* Blue border on hover */
    }
    </style>
</head>

<body>

    <h1>Articles with status: "<?php echo htmlspecialchars($status_filter); ?>"</h1>

    <!-- Filter Form for Status -->
    <form method="GET" action="">
        <label for="status">Filter by Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="draft" <?php echo ($status_filter == 'draft') ? 'selected' : ''; ?>>Draft</option>
            <option value="submitted" <?php echo ($status_filter == 'submitted') ? 'selected' : ''; ?>>Submitted
            </option>
            <option value="under_review" <?php echo ($status_filter == 'under_review') ? 'selected' : ''; ?>>Under
                Review</option>
            <option value="revision" <?php echo ($status_filter == 'revision') ? 'selected' : ''; ?>>Revision</option>
            <option value="approved" <?php echo ($status_filter == 'approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="published" <?php echo ($status_filter == 'published') ? 'selected' : ''; ?>>Published
            </option>
            <option value="rejected" <?php echo ($status_filter == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
        </select>
    </form>

    <?php if ($result && $result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Journal</th>
                <th>Submitted By (User ID)</th>
                <th>Submission Date</th>
                <th>Last Update</th>
                <th>Status</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['journal']); ?></td>
                <td><?php echo htmlspecialchars($row['submitted_by']); ?></td>
                <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                <td><?php echo htmlspecialchars($row['last_update']); ?></td>
                <td>
                    <span class="status-badge status-<?php echo str_replace('_', '-', $row['status']); ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </span>
                </td>
                <td>
                    <a class="edit-link" href="edit_article.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No articles found with status "<?php echo htmlspecialchars($status_filter); ?>".</p>
    <?php endif; ?>

    <?php
    $stmt->close();
    $conn->close();
    ?>

</body>

</html>
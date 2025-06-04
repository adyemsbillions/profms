<?php
// DB connection and query (same as before)
$host = "localhost";
$db = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
    SELECT 
        si.id, 
        si.user_id, 
        CONCAT(u.first_name, ' ', u.last_name) AS full_name, 
        si.subject, 
        si.message, 
        si.created_at 
    FROM support_inquiries si
    LEFT JOIN users u ON si.user_id = u.id
    ORDER BY si.created_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Support Inquiries</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 2rem;
        background-color: #f9f9f9;
    }

    h2 {
        color: #1e3a8a;
        margin-bottom: 1rem;
    }

    /* --- TABLE STYLES --- */
    .inquiries-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    .inquiries-table th,
    .inquiries-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .inquiries-table th {
        background-color: #1e3a8a;
        color: white;
        font-weight: 600;
    }

    .inquiries-table tr:hover {
        background-color: #f1f5f9;
    }

    .message-cell {
        max-width: 400px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    /* --- CARD STYLES (hidden on desktop) --- */
    .inquiries-cards {
        display: none;
        gap: 1rem;
    }

    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
    }

    .card h3 {
        margin-top: 0;
        color: #1e3a8a;
    }

    .card .subject {
        font-weight: 600;
        margin: 0.3rem 0;
    }

    .card .message {
        white-space: pre-wrap;
        word-wrap: break-word;
        margin-bottom: 0.7rem;
    }

    .card .footer {
        font-size: 0.85rem;
        color: #666;
    }

    /* --- RESPONSIVE SWITCH --- */
    @media (max-width: 767px) {
        .inquiries-table {
            display: none;
            /* hide table on mobile */
        }

        .inquiries-cards {
            display: flex;
            flex-direction: column;
        }
    }
    </style>
</head>

<body>

    <h2>Support Inquiries</h2>

    <?php if ($result && $result->num_rows > 0): ?>

    <!-- Table for Desktop -->
    <table class="inquiries-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User Name</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['full_name'] ?: 'Unknown User'); ?></td>
                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                <td class="message-cell"><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                <td><?php echo date("Y-m-d H:i:s", strtotime($row['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php
  // Reset result pointer and fetch again for cards
  $result->data_seek(0);
  ?>

    <!-- Cards for Mobile -->
    <div class="inquiries-cards">
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card">
            <h3><?php echo htmlspecialchars($row['full_name'] ?: 'Unknown User'); ?></h3>
            <div class="subject"><?php echo htmlspecialchars($row['subject']); ?></div>
            <div class="message"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
            <div class="footer"><?php echo date("Y-m-d H:i:s", strtotime($row['created_at'])); ?></div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php else: ?>
    <div class="no-data">No support inquiries found.</div>
    <?php endif; ?>

    <?php
$conn->close();
?>

</body>

</html>
<?php
// Start session
session_start();

// Ensure the user is logged in (admin check)
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// DB connection details
$host = "localhost";
$db = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set default status to 'all' if not specified
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Query based on the filter (if status is not 'all', filter by status)
if ($status_filter == 'all') {
    $sql = "SELECT * FROM payments ORDER BY payment_date DESC";
} else {
    $sql = "SELECT * FROM payments WHERE status = ? ORDER BY payment_date DESC";
}

// Prepare and execute query
$stmt = $conn->prepare($sql);
if ($status_filter != 'all') {
    $stmt->bind_param("s", $status_filter);
}
$stmt->execute();
$result = $stmt->get_result();

// Close the statement
$stmt->close();

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1e3a8a;
            text-align: center;
        }

        .filter {
            text-align: center;
            margin-bottom: 20px;
        }

        .filter select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #1e3a8a;
            color: white;
        }

        table tr:hover {
            background-color: #f1f5f9;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 14px;
            color: white;
            font-weight: bold;
        }

        .status-pending {
            background-color: #f59e0b;
        }

        .status-success {
            background-color: #059669;
        }

        .status-failed {
            background-color: #dc2626;
        }

        .amount {
            color: #1e3a8a;
            font-weight: bold;
        }

        .payment-date {
            color: #888;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Payments Overview</h1>

        <!-- Filter Section -->
        <div class="filter">
            <label for="status-filter">Filter by Status:</label>
            <select id="status-filter" onchange="window.location.href = '?status=' + this.value;">
                <option value="all" <?php if ($status_filter == 'all') echo 'selected'; ?>>All</option>
                <option value="pending" <?php if ($status_filter == 'pending') echo 'selected'; ?>>Pending</option>
                <option value="success" <?php if ($status_filter == 'success') echo 'selected'; ?>>Success</option>
                <option value="failed" <?php if ($status_filter == 'failed') echo 'selected'; ?>>Failed</option>
            </select>
        </div>

        <!-- Table displaying payment data -->
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Amount (NGN)</th>
                    <th>Status</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td class="amount"><?php echo number_format($row['amount'] / 100, 2); ?> NGN</td>
                            <td>
                                <?php
                                $status_class = '';
                                switch ($row['status']) {
                                    case 'pending':
                                        $status_class = 'status-pending';
                                        break;
                                    case 'success':
                                        $status_class = 'status-success';
                                        break;
                                    case 'failed':
                                        $status_class = 'status-failed';
                                        break;
                                }
                                ?>
                                <span
                                    class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                            </td>
                            <td class="payment-date"><?php echo $row['payment_date']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No payment records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>
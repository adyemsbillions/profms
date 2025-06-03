<?php
// session_start();
include('db.php'); // Your database connection

// Get the logged-in user's ID from session
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$userId = intval($_SESSION['user_id']); // sanitize

// Query counts
$sql = "
SELECT 
    COUNT(*) AS total_articles,
    SUM(status IN ('draft', 'submitted')) AS total_pending,
    SUM(status IN ('approved', 'published')) AS total_approved
FROM articles
WHERE submitted_by = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($total_articles, $total_pending, $total_approved);
$stmt->fetch();
$stmt->close();

// echo "You have uploaded:<br>";
// echo "Total articles: $total_articles<br>";
// echo "Pending articles (draft/submitted): $total_pending<br>";
// echo "Approved articles (approved/published): $total_approved<br>";
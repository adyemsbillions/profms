<?php
// No session_start() here!
// No require db.php here; $conn is already available from dashboard.php

if (!isset($_SESSION['user_id'])) {
    echo '<tr><td colspan="5" style="text-align:center;">Please log in to view articles.</td></tr>';
    return;
}

$user_id = intval($_SESSION['user_id']);

$sql = "SELECT id, title, abstract, journal, submission_date, status, file_path FROM articles WHERE submitted_by = ? ORDER BY submission_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

function formatStatusBadge($status)
{
    $statusClassMap = [
        'draft' => 'status-draft',
        'submitted' => 'status-pending',
        'under_review' => 'status-pending',
        'revision' => 'status-revision',
        'approved' => 'status-approved',
        'published' => 'status-approved',
        'rejected' => 'status-rejected',
    ];
    $class = $statusClassMap[$status] ?? 'status-pending';
    $label = ucfirst(str_replace('_', ' ', $status));
    return "<span class='status-badge {$class}'>{$label}</span>";
}

if ($result->num_rows === 0) {
    echo '<tr><td colspan="5" style="text-align:center;">No articles found.</td></tr>';
} else {
    while ($article = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td><strong>' . htmlspecialchars($article['title']) . '</strong><br><small style="color: var(--text-light);">' . nl2br(htmlspecialchars(substr($article['abstract'], 0, 80))) . '...</small></td>';
        echo '<td>' . htmlspecialchars($article['journal']) . '</td>';
        echo '<td>' . date("M d, Y", strtotime($article['submission_date'])) . '</td>';
        echo '<td>' . formatStatusBadge($article['status']) . '</td>';
        echo '<td><div class="action-buttons">';

        if (in_array($article['status'], ['draft', 'revision', 'rejected'])) {
            echo '<a href="edit_article.php?id=' . $article['id'] . '" class="btn btn-outline btn-small">Edit</a>';
            echo '<a href="withdraw_article.php?id=' . $article['id'] . '" class="btn btn-outline btn-small" onclick="return confirm(\'Are you sure you want to withdraw this article?\');">Withdraw</a>';
        } elseif (in_array($article['status'], ['submitted', 'under_review'])) {
            echo '<a href="view_article.php?id=' . $article['id'] . '" class="btn btn-outline btn-small">View</a>';
            echo '<a href="' . htmlspecialchars($article['file_path']) . '" download class="btn btn-outline btn-small">Download</a>';
        } elseif (in_array($article['status'], ['approved', 'published'])) {
            echo '<a href="view_article.php?id=' . $article['id'] . '" class="btn btn-outline btn-small">View</a>';
            echo '<a href="' . htmlspecialchars($article['file_path']) . '" download class="btn btn-outline btn-small">Download</a>';
        }

        echo '</div></td>';
        echo '</tr>';
    }
}

$stmt->close();

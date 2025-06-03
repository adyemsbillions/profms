<?php
require_once 'stats.php'; // Include the stats script
include('db.php');
// Fetch submission trends data (grouped by year)
$submission_trends = [];
try {
    $stmt_trends = $conn->prepare("SELECT YEAR(submission_date) AS submission_year, COUNT(id) AS article_count FROM articles WHERE submission_date IS NOT NULL GROUP BY submission_year ORDER BY submission_year");
    if ($stmt_trends) {
        $stmt_trends->execute();
        $result_trends = $stmt_trends->get_result();
        $submission_trends = $result_trends->fetch_all(MYSQLI_ASSOC);
        $stmt_trends->close();
    } else {
        error_log("Prepare failed for submission trends: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Submission trends error: " . $e->getMessage());
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Journal Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
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

        .dashboard-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            background: white;
            margin-bottom: 1.5rem;
        }

        .dashboard-card:hover {
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
            font-size: 1.2rem;
        }

        .card-body h5 {
            color: var(--primary-color);
            font-weight: 500;
        }

        .btn-primary {
            border-radius: 8px;
            font-weight: 500;
        }

        .chart-container {
            margin-top: 2rem;
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            margin-bottom: 1rem;
        }

        .chart-title {
            color: var(--primary-color);
            font-weight: 600;
        }

        canvas {
            max-width: 100%;
            height: auto;
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 1rem;
                font-size: 1rem;
            }

            .chart-container {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="my-4 text-center" style="color: var(--primary-color);">Admin Dashboard</h1>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <div class="col">
                <div class="card dashboard-card">
                    <div class="card-header">Total Users</div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($total_users); ?> Users</h5>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card dashboard-card">
                    <div class="card-header">Total Articles</div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($total_articles); ?> Articles</h5>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card dashboard-card">
                    <div class="card-header">Total Inquiries</div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($total_inquiries); ?> Inquiries</h5>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card dashboard-card">
                    <div class="card-header">Approved Articles</div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($total_approved); ?> Approved</h5>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card dashboard-card">
                    <div class="card-header">Rejected Articles</div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($total_rejected); ?> Rejected</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Article Submissions Over Time</h3>
            </div>
            <canvas id="submissionTrendChart"></canvas>
        </div>
        <a href="manage_articles.php" class="btn btn-primary mt-4">Manage Articles</a>
    </div>
    <script>
        const ctx = document.getElementById('submissionTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [
                    <?php echo implode(',', array_map(function ($row) {
                        return "'" . htmlspecialchars($row['submission_year']) . "'";
                    }, $submission_trends)); ?>
                ],
                datasets: [{
                    data: [
                        <?php echo implode(',', array_map(function ($row) {
                            return $row['article_count'];
                        }, $submission_trends)); ?>
                    ],
                    backgroundColor: [
                        '#1e3a8a', // --primary-color
                        '#059669', // --secondary-color
                        '#dc2626', // --danger-color
                        '#3b82f6',
                        '#f59e0b',
                        '#6b7280'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            },
                            color: '#1e3a8a'
                        }
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
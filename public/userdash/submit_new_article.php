<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Submit New Article</title>
    <style>
        :root {
            --primary-color: #1e3a8a;
            --text-light: #6b7280;
            --bg-lighter: #f3f4f6;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-lighter);
            margin: 2rem;
            color: #1f2937;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.3rem;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }

        .form-container {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(30, 58, 138, 0.3);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #3b82f6;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: #fff;
        }

        /* Submission guidelines */
        .submission-guidelines {
            background: var(--bg-lighter);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .submission-guidelines h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .submission-guidelines ul {
            margin-left: 1rem;
        }
    </style>
</head>

<body>
    <section id="submit" class="content-section">
        <div class="page-header">
            <h1 class="page-title">Submit New Article</h1>
            <p class="page-subtitle">Submit your manuscript for publication</p>
        </div>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" action="submit_article.php">
                <div class="form-group">
                    <label class="form-label" for="title">Article Title *</label>
                    <input type="text" id="title" name="title" class="form-input" placeholder="Enter your article title"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="abstract">Abstract *</label>
                    <textarea id="abstract" name="abstract" class="form-textarea"
                        placeholder="Provide a brief abstract of your article" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="journal">Select Journal *</label>
                    <select id="journal" name="journal" class="form-select" required>
                        <option value="">Choose a journal</option>
                        <option value="Sahel Analyst: Journal of Management Sciences">Sahel Analyst: Journal of
                            Management Sciences</option>
                        <option value="Journal of Resources & Economic Development (JRED)">Journal of Resources &
                            Economic Development (JRED)</option>
                        <option value="African Journal of Management">African Journal of Management</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="manuscript">Upload Manuscript *</label>
                    <input type="file" id="manuscript" name="manuscript" accept=".pdf,.doc,.docx" required>
                    <p style="font-size: 0.8rem; color: var(--text-light);">PDF, DOC, DOCX (Max 10MB)</p>
                </div>

                <div class="form-group">
                    <label class="form-label" for="keywords">Keywords</label>
                    <input type="text" id="keywords" name="keywords" class="form-input"
                        placeholder="Enter keywords separated by commas">
                </div>

                <div class="form-group">
                    <label class="form-label" for="author_names">Author Names *</label>
                    <textarea id="author_names" name="author_names" class="form-textarea"
                        placeholder="List all authors with their affiliations" required></textarea>
                </div>

                <div class="submission-guidelines">
                    <h4>📋 Submission Guidelines</h4>
                    <ul>
                        <li>Manuscripts should be original and not published elsewhere</li>
                        <li>Follow the journal's formatting guidelines</li>
                        <li>Include proper citations and references</li>
                        <li>Ensure ethical compliance and data privacy</li>
                    </ul>
                </div>

                <div class="btn-group">
                    <button type="submit" name="action" value="submit" class="btn btn-primary">Submit Article</button>
                    <button type="submit" name="action" value="draft" class="btn btn-outline">Save as Draft</button>
                </div>
            </form>
        </div>
    </section>
</body>

</html>
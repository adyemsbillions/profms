<?php
// submit_article_form.php
session_start();
// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit New Article - Sahel Analyst</title>
    <style>
        :root {
            --primary-color: #1e3a8a;
            --primary-light: #3b82f6;
            --secondary-color: #059669;
            --secondary-light: #10b981;
            --accent-color: #f59e0b;
            --accent-light: #fbbf24;
            --text-color: #1f2937;
            --text-light: #6b7280;
            --text-lighter: #9ca3af;
            --bg-color: #ffffff;
            --bg-light: #f9fafb;
            --bg-lighter: #f3f4f6;
            --border-color: #e5e7eb;
            --border-light: #f3f4f6;
            --success-color: #059669;
            --error-color: #dc2626;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
            padding: 2rem;
        }

        .page-header {
            background: var(--bg-color);
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .page-header:hover {
            transform: translateY(-4px);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 1rem;
            font-weight: 400;
        }

        .form-container {
            background: var(--bg-color);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            max-width: 900px;
            margin: 0 auto;
            transition: box-shadow 0.3s ease;
        }

        .form-container:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.95rem;
            background: var(--bg-lighter);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            background: var(--bg-color);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input[type="file"] {
            padding: 0.5rem;
            background: transparent;
            border: none;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-align: center;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .btn-group {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .form-message {
            display: none;
            padding: 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .form-message.success {
            display: block;
            background: var(--success-color);
            color: white;
            opacity: 1;
        }

        .form-message.error {
            display: block;
            background: var(--error-color);
            color: white;
            opacity: 1;
        }

        .submission-guidelines {
            background: var(--bg-lighter);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-color);
        }

        .submission-guidelines h4 {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .submission-guidelines ul {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-left: 1.5rem;
            list-style-type: disc;
        }

        .submission-guidelines ul li {
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1.5rem;
            }

            .page-header {
                padding: 1.5rem;
                margin-bottom: 2rem;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .form-container {
                padding: 2rem;
            }

            .btn-group {
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }

            .page-header {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .form-container {
                padding: 1.5rem;
            }

            .btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }
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
            <div id="formMessage" class="form-message"></div>
            <form id="submitForm" method="POST" enctype="multipart/form-data" action="submit_article.php">
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
                    <p style="font-size: 0.85rem; color: var(--text-light); margin-top: 0.5rem;">PDF, DOC, DOCX (Max
                        10MB)</p>
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
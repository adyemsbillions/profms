<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Developer - FMS Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    :root {
        --primary-color: #1e3a8a;
        --secondary-color: #059669;
        --danger-color: #dc2626;
        --bg-light: #f8fafc;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--bg-light);
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .about-section {
        padding: 40px 0;
    }

    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: #1a2a44;
        margin-bottom: 20px;
        text-align: center;
    }

    .developer-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 30px;
        text-align: center;
        max-width: 600px;
        margin: 0 auto;
    }

    .developer-card h3 {
        font-size: 1.5rem;
        color: #1a2a44;
        margin-bottom: 10px;
    }

    .developer-card p {
        font-size: 1rem;
        color: #6c757d;
        margin-bottom: 15px;
    }

    .developer-card .contact-info {
        font-size: 0.9rem;
        color: #1a2a44;
    }

    .developer-card .contact-info a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .developer-card .contact-info a:hover {
        text-decoration: underline;
    }

    .developer-card .social-links {
        margin-top: 15px;
    }

    .developer-card .social-links a {
        color: var(--primary-color);
        font-size: 1.5rem;
        margin: 0 10px;
        text-decoration: none;
    }

    .developer-card .social-links a:hover {
        color: var(--secondary-color);
    }

    @media (max-width: 768px) {
        .section-title {
            font-size: 1.5rem;
        }

        .developer-card h3 {
            font-size: 1.25rem;
        }

        .developer-card p {
            font-size: 0.9rem;
        }
    }
    </style>
</head>

<body>
    <section class="about-section">
        <div class="container">
            <h2 class="section-title">About the Developer</h2>
            <div class="developer-card">
                <h3>Adyems Bilions</h3>
                <p>Full-Stack Developer</p>
                <p>
                    Adyems Bilions is a skilled developer with expertise in PHP, Laravel TypeScript, React native,
                    Futter and JavaScript, dedicated to
                    creating impactful web platforms an mobile applications. He built the FMS Journal to streamline
                    academic publication for unimaid . Additionally, adyems Bilions developed <a
                        href="https://unimaidresources.com.ng" target="_blank">UNIMAID Resources</a>, a platform
                    enhancing the university experience for UNIMAID students with academic tools, campus updates, and
                    social features.
                </p>
                <div class="contact-info">
                    <p>Email: <a href="mailto:adyemsgodlove@gmail.com">mailto:adyemsgodlove@gmail.com</a></p>
                    <p>Website: <a href="https://adyems.unimaidresources.com.ng"
                            target="_blank">adyems.unimaidresources.com.ng</a></p>
                </div>
                <div class="social-links">
                    <a href="https://github.com/adyemsbillions" target="_blank" aria-label="GitHub Profile"><i
                            class="fab fa-github"></i></a>
                </div>
            </div>
        </div>
    </section>
</body>

</html>
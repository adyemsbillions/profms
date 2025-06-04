<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic styling for the page */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        h2 {
            color: #4CAF50;
            font-size: 24px;
            margin-bottom: 20px;
        }

        p {
            color: #333;
            font-size: 16px;
            margin-bottom: 20px;
        }

        a {
            color: #ffffff;
            background-color: #4CAF50;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            transition: background-color 0.3s;
        }

        a:hover {
            background-color: #45a049;
        }

        .alert {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Registration Successful!</h2>
        <p class="success-message">
            A confirmation link has been sent to your email. Please check your inbox to verify your
            account.
        </p>
        <p>Once verified, you will be able to log in and start using your account.</p>
        <p><a href="login.php">Go to Login</a></p>
    </div>
</body>

</html>
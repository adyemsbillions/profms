<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h2>Registration Successful!</h2>
        <p><?php session_start();
            echo $_SESSION['signup_success'] ?? 'Please check your email to verify your account.'; ?>
        </p>
        <p><a href="login.php">Go to Login</a></p>
    </div>
</body>

</html>
<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = "Please enter email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT id, name, password_hash FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Authentication successful
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Login</title>
</head>

<body>
    <h2>Admin Login</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (!empty($_SESSION['success'])) {
        echo "<p style='color:green;'>" . $_SESSION['success'] . "</p>";
        unset($_SESSION['success']);
    } ?>
    <form method="POST" action="">
        <label>Email:<br><input type="email" name="email" required></label><br>
        <label>Password:<br><input type="password" name="password" required></label><br>
        <button type="submit">Log In</button>
    </form>
</body>

</html>
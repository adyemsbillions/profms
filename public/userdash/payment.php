<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Connect to the database
$host = "localhost";
$db = "fms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details from the database
$sql = "SELECT first_name, last_name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email);

// Fetch the user's data
if ($stmt->fetch()) {
    $user_name = $first_name . ' ' . $last_name;  // Combine first and last name
} else {
    die("User details not found.");
}

$stmt->close();
$conn->close();

// Paystack public key
$paystack_public_key = "pk_live_312da64742ab9a78bc3725884d6e44d584bf7fc4"; // Your Paystack public key
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pay for Account Subscription</title>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7fafc;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        h2 {
            color: #1e3a8a;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .user-name {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }

        .pay-button {
            background-color: #059669;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .pay-button:hover {
            background-color: #046d56;
        }

        .pay-button:active {
            background-color: #044a42;
        }

        .pay-button:focus {
            outline: none;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Pay for Account Subscription</h2>
        <p class="user-name">Hello, <?php echo htmlspecialchars($user_name); ?>! Ready to make your payment?</p>
        <p class="user-name">Your email: <?php echo htmlspecialchars($email); ?></p>

        <!-- Button to trigger the Paystack payment -->
        <button id="paystackButton" class="pay-button" data-user-id="<?php echo $user_id; ?>">
            Pay N200
        </button>
    </div>

    <script>
        document.getElementById("paystackButton").addEventListener("click", function() {
            var userId = this.getAttribute('data-user-id'); // Get user ID from the button

            // Initialize the Paystack payment
            var handler = PaystackPop.setup({
                key: "<?php echo $paystack_public_key; ?>", // Paystack public key
                email: "<?php echo htmlspecialchars($email); ?>", // User's actual email
                amount: 20000, // Amount to be paid in kobo (N200 * 100 = 20000)
                currency: "NGN", // Currency set to Naira (NGN)
                ref: "subscription_<?php echo uniqid(); ?>", // Reference for the transaction
                callback: function(response) {
                    // After payment, response will return the payment details
                    window.location.href = "payment_success.php?reference=" + response.reference;
                },
                onClose: function() {
                    alert("Payment popup closed without completing the payment.");
                }
            });
            handler.openIframe(); // Open the Paystack payment modal
        });
    </script>

</body>

</html>
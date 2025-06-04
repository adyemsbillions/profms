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

// Paystack secret key (get this from your Paystack dashboard)
$paystack_secret_key = "sk_live_49a25b173eed564885cbb2a93f388034e8dff9db"; // Replace with your actual secret key

// Get the reference from the URL (provided by Paystack after payment)
$reference = isset($_GET['reference']) ? $_GET['reference'] : '';
if (empty($reference)) {
    die("Invalid request.");
}

// Verify payment by calling Paystack's API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . $reference);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $paystack_secret_key"
]);

$response = curl_exec($ch);
curl_close($ch);

// Decode the response from Paystack
$response_data = json_decode($response, true);

// Check if the response contains the 'data' key (payment details)
if (isset($response_data['data'])) {
    // Check if the payment was successful
    if ($response_data['data']['status'] == 'success') {
        $amount_paid = $response_data['data']['amount'];  // Amount paid in kobo
        $amount_paid_in_naira = $amount_paid / 100; // Convert amount to NGN (Naira)

        // Store payment details in the database
        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, status) VALUES (?, ?, ?)");
        $status = 'success';  // Payment status
        $stmt->bind_param("iis", $user_id, $amount_paid_in_naira, $status);

        if ($stmt->execute()) {
            // Show success message
            echo "<h2>Payment Successful!</h2>";
            echo "<p>Your payment of " . number_format($amount_paid_in_naira, 2) . " NGN has been successfully processed.</p>";
            echo "<p>Your account has been activated.</p>";
            echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
        } else {
            echo "Failed to record payment.";
        }

        $stmt->close();
    } else {
        echo "Payment failed. Please try again.";
    }
} else {
    echo "Payment verification failed. Please check the response.";
}

$conn->close();

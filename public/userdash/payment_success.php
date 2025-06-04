<?php
// Paystack secret key (get this from your Paystack dashboard)
$paystack_secret_key = "sk_live_49a25b173eed564885cbb2a93f388034e8dff9db";  // Replace with your actual secret key

// Get the reference from the URL (provided by Paystack after payment)
$reference = isset($_GET['reference']) ? $_GET['reference'] : '';
$user_id = 1;  // Replace with actual user ID from session ($_SESSION['user_id'])

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

// Check if the response contains 'data' key (payment details)
if (isset($response_data['data'])) {
    // Check if the payment was successful
    if ($response_data['data']['status'] == 'success') {
        $amount_paid = $response_data['data']['amount'];  // Amount paid in kobo

        // Store payment information in the database
        $conn = new mysqli('localhost', 'root', '', 'fms');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Insert payment record into the database (user_id, amount, status)
        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, status) VALUES (?, ?, ?)");
        $status = 'success';  // Payment status
        $stmt->bind_param("iis", $user_id, $amount_paid, $status);

        if ($stmt->execute()) {
            echo "Payment successful! Your account has been activated.<br>";
            echo "Amount Paid: " . number_format($amount_paid / 100, 2) . " NGN<br>";
        } else {
            echo "Failed to record payment.";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Payment Failed. Please try again.";
    }
} else {
    echo "Payment verification failed. Please check the response.";
}

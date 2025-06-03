<?php
// db.php - Database connection for FMS Journal

$host = 'localhost';       // usually 'localhost' if your DB is on same server
$dbname = 'fms';           // your database name
$user = 'root';    // replace with your DB username
$pass = ''; // replace with your DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,    // throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,            // use real prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Log the error message and show a generic error
    error_log('Database connection failed: ' . $e->getMessage());
    exit('Database connection error. Please try again later.');
}
<?php
// Database connection details
$host = 'localhost'; // Change this if your database is hosted elsewhere
$dbname = 'flight_booking'; // The database name you created
$username = 'root'; // Default username for phpMyAdmin
$password = ''; // Default password for phpMyAdmin

// Create a connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Database connected successfully!";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

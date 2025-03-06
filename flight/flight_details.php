<?php
session_start();
require '../database/database.php'; // Database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

$flight_id = $_GET['flight_id'] ?? null; // Get the flight ID from the query string
if (!$flight_id) {
    die('Flight ID is required.');
}

$message = '';
$success = false;

// Fetch flight details
try {
    $stmt = $conn->prepare("SELECT * FROM flights WHERE flight_id = :flight_id");
    $stmt->execute([':flight_id' => $flight_id]);
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$flight) {
        die('Flight not found.');
    }

    // Decode itinerary
    $itinerary = $flight['itinerary'] ? json_decode($flight['itinerary'], true) : [];
    if (!is_array($itinerary)) {
        $itinerary = []; // Default to an empty array if decoding fails
    }

    // Fetch pending passengers
    $stmt = $conn->prepare("
        SELECT u.name, u.email 
        FROM passenger_flights pf 
        JOIN users u ON pf.passenger_id = u.user_id 
        WHERE pf.flight_id = :flight_id AND pf.status = 'pending'
    ");
    $stmt->execute([':flight_id' => $flight_id]);
    $pending_passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch registered passengers
    $stmt = $conn->prepare("
        SELECT u.name, u.email 
        FROM passenger_flights pf 
        JOIN users u ON pf.passenger_id = u.user_id 
        WHERE pf.flight_id = :flight_id AND pf.status = 'completed'
    ");
    $stmt->execute([':flight_id' => $flight_id]);
    $registered_passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error fetching flight details: ' . $e->getMessage());
}

// Handle flight cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_flight'])) {
    try {
        $conn->beginTransaction();

        // Refund fees to all registered passengers
        $stmt = $conn->prepare("
            UPDATE users 
            SET account_balance = account_balance + :fees 
            WHERE user_id IN (
                SELECT passenger_id 
                FROM passenger_flights 
                WHERE flight_id = :flight_id AND status = 'completed'
            )
        ");
        $stmt->execute([':fees' => $flight['fees'], ':flight_id' => $flight_id]);

        // Delete passenger records
        $stmt = $conn->prepare("DELETE FROM passenger_flights WHERE flight_id = :flight_id");
        $stmt->execute([':flight_id' => $flight_id]);

        // Delete flight record
        $stmt = $conn->prepare("DELETE FROM flights WHERE flight_id = :flight_id");
        $stmt->execute([':flight_id' => $flight_id]);

        $conn->commit();
        $success = true;
        $message = 'Flight cancelled successfully. Fees refunded to passengers.';

        // Redirect to company home page
        header("Location: ../company/homepage.php");
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        $message = 'Error cancelling flight: ' . $e->getMessage();
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Details</title>
    <link rel="stylesheet" href="flight_details.css">
</head>
<body>
<header class="main-header">
        <nav>
            <ul class="nav-links">
                <li><a href="../company/homepage.php">Home</a></li>
                <li><a href="../company/edit_profile.php">Profile</a></li>
                <li><a href="../login/login.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <h1>Flight Details</h1>
        <?php if ($message): ?>
            <p class="<?= $success ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <p><strong>ID:</strong> <?= htmlspecialchars($flight['flight_id']) ?></p>
        <p><strong>Name:</strong> <?= htmlspecialchars($flight['name']) ?></p>
        <p><strong>Itinerary:</strong> 
            <?php 
            if (!empty($itinerary)) {
                echo implode(' → ', array_map('htmlspecialchars', $itinerary));
            } else {
                echo "No itinerary available";
            }
            ?>
        </p>
        <p><strong>Fees:</strong> <?= htmlspecialchars($flight['fees']) ?></p>
        <p><strong>Start Time:</strong> <?= htmlspecialchars($flight['start_time']) ?></p>
        <p><strong>End Time:</strong> <?= htmlspecialchars($flight['end_time']) ?></p>

        <h2>Pending Passengers</h2>
        <ul>
            <?php foreach ($pending_passengers as $passenger): ?>
                <li><?= htmlspecialchars($passenger['name']) ?> (<?= htmlspecialchars($passenger['email']) ?>)</li>
            <?php endforeach; ?>
        </ul>

        <h2>Registered Passengers</h2>
        <ul>
            <?php foreach ($registered_passengers as $passenger): ?>
                <li><?= htmlspecialchars($passenger['name']) ?> (<?= htmlspecialchars($passenger['email']) ?>)</li>
            <?php endforeach; ?>
        </ul>

        <form method="POST">
            <button type="submit" name="cancel_flight" onclick="return confirm('Are you sure you want to cancel this flight? This will refund all passengers.')">Cancel Flight</button>
        </form>
    </div>
</body>
</html>

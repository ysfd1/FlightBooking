<?php
session_start();
require '../database/database.php'; // Database connection

// Ensure the user is logged in and is a passenger
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'passenger') {
    header("Location: ../login/login.html"); // Redirect to login if unauthorized
    exit;
}

// Fetch passenger details
$passenger_id = $_SESSION['user_id'];
$query = "SELECT name, email, tel, photo FROM users WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->execute([':user_id' => $passenger_id]);
$passenger = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch completed and current flights for the passenger
$completed_flights_query = "
    SELECT f.* 
    FROM passenger_flights pf 
    JOIN flights f ON pf.flight_id = f.flight_id 
    WHERE pf.passenger_id = :passenger_id AND pf.status = 'completed'";

$current_flights_query = "
    SELECT f.* 
    FROM passenger_flights pf 
    JOIN flights f ON pf.flight_id = f.flight_id 
    WHERE pf.passenger_id = :passenger_id AND pf.status = 'current'";

$stmt = $conn->prepare($completed_flights_query);
$stmt->execute([':passenger_id' => $passenger_id]);
$completed_flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare($current_flights_query);
$stmt->execute([':passenger_id' => $passenger_id]);
$current_flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Home</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<header class="main-header">
        <nav>
            <ul class="nav-links">
                <li><a href="home.php">Home</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../login/login.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <!-- Passenger Details -->
        <div style="text-align: center;">
    <h1>Welcome, <?php echo htmlspecialchars($passenger['name']); ?></h1>
</div>
        <p><b>Email:</b> <?php echo htmlspecialchars($passenger['email']); ?></p>
        <p><b>Telephone: </b> <?php echo htmlspecialchars($passenger['tel']); ?></p>
        <img src="../uploads/<?php echo htmlspecialchars($passenger['photo']); ?>" alt="Profile Image" class="profile-image">

        <!-- Completed Flights -->
        <h2>Completed Flights</h2>
        <ul>
            <?php if (count($completed_flights) > 0): ?>
                <?php foreach ($completed_flights as $flight): ?>
                    <li><?php echo htmlspecialchars($flight['name']); ?> (<?php echo htmlspecialchars($flight['start_time']); ?> - <?php echo htmlspecialchars($flight['end_time']); ?>)</li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No completed flights.</li>
            <?php endif; ?>
        </ul>

        <!-- Current Flights -->
        <h2>Current Flights</h2>
        <ul>
            <?php if (count($current_flights) > 0): ?>
                <?php foreach ($current_flights as $flight): ?>
                    <li><?php echo htmlspecialchars($flight['name']); ?> (<?php echo htmlspecialchars($flight['start_time']); ?> - <?php echo htmlspecialchars($flight['end_time']); ?>)</li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No current flights.</li>
            <?php endif; ?>
        </ul>

        <!-- Navigation -->
        <a href="profile.php" class="btn">Profile</a>
        <a href="../flight/search_flight.php" class="btn">Search Flights</a>
    </div>
</body>
</html>

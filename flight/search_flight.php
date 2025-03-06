<?php
session_start();
require '../database/database.php'; // Database connection

// Ensure the user is logged in and is a passenger
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'passenger') {
    header("Location: ../login/login.html");
    exit;
}

// Search logic
$flights = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from = $_POST['from'];
    $to = $_POST['to'];

    // Query to check if 'from' and 'to' exist within the itinerary JSON array
    $query = "
        SELECT * FROM flights 
        WHERE JSON_CONTAINS(itinerary, JSON_QUOTE(:from)) 
        AND JSON_CONTAINS(itinerary, JSON_QUOTE(:to))
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':from' => $from,
        ':to' => $to
    ]);
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Flights</title>
    <link rel="stylesheet" href="add_flight.css">
</head>
<body>
    <div class="container">
        <h1>Search a Flight</h1>
        <form action="search_flight.php" method="post">
            <div class="form-group">
                <label for="from">From:</label>
                <input type="text" id="from" name="from" placeholder="Enter departure location" style="width: 90%;" required>
            </div>
            <div class="form-group">
                <label for="to">To:</label>
                <input type="text" id="to" name="to" placeholder="Enter destination" style="width: 90%;" required>
            </div>
            <button type="submit" class="btn">Search</button>
            <button type="reset" class="btn reset-btn">Reset</button>
        </form>
        <!-- List of Flights -->
        <h2>Available Flights</h2>
        <ul>
            <?php if (count($flights) > 0): ?>
                <?php foreach ($flights as $flight): ?>
                    <li>
                        <a href="flight_info.php?id=<?php echo $flight['flight_id']; ?>" class="flight-row">
                            <?php echo htmlspecialchars($flight['name']); ?> 
                            (<?php echo htmlspecialchars(implode(" → ", json_decode($flight['itinerary']))); ?>)
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No flights found.</li>
            <?php endif; ?>
        </ul>
        <button onclick="window.location.href='../passenger/home.php'" class="btn">Back to Home</button>

    </div>
</body>
</html>

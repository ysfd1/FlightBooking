<?php
session_start();
require '../database/database.php'; // Database connection

// Ensure the user is logged in and is a passenger
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'passenger') {
    header("Location: ../login/login.html");
    exit;
}

// Check if flight_id is provided in the URL
$flight_id = $_GET['id'] ?? null;
if (!$flight_id) {
    die('Flight ID is required.');
}

// Fetch flight details
try {
    $stmt = $conn->prepare("SELECT * FROM flights WHERE flight_id = :flight_id");
    $stmt->execute([':flight_id' => $flight_id]);
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$flight) {
        die('Flight not found.');
    }

    // Decode itinerary
    $itinerary = json_decode($flight['itinerary'], true) ?: [];
} catch (PDOException $e) {
    die('Error fetching flight details: ' . $e->getMessage());
}

$message = '';
$success = false;
$user_id = $_SESSION['user_id'];

// Fetch the user's account balance
try {
    $stmt = $conn->prepare("SELECT account_balance FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $account_balance = $user['account_balance'] ?? 0;
} catch (PDOException $e) {
    die('Error fetching account balance: ' . $e->getMessage());
}

// Handle flight booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_flight'])) {
    // ... (payment method handling)

    try {
        $conn->beginTransaction();

        // ... (account balance update)

        // Add the user to the flight's passengers (using INSERT IGNORE to prevent duplicates)
        $stmt = $conn->prepare("INSERT IGNORE INTO passenger_flights (flight_id, passenger_id, status) VALUES (:flight_id, :user_id, 'completed')");
        $stmt->execute([':flight_id' => $flight_id, ':user_id' => $user_id]);

        // Check if the insert was successful (affected rows > 0)
        if ($stmt->rowCount() > 0) {
            $conn->commit();
            $success = true;
            $message = 'Flight booked successfully!';
        } else {
            $conn->rollBack();
            $message = 'You have already booked this flight.'; // More user-friendly message
            $success = false;
        }


    } catch (Exception $e) {
        $conn->rollBack();
        $message = 'Error booking flight: ' . $e->getMessage();
        $success = false;
    }
}


// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $message_content = $_POST['message'] ?? '';
    if ($message_content) {
        try {
            $company_id = $flight['company_id'] ?? null;
            if (!$company_id) {
                throw new Exception('Company ID is missing for the selected flight.');
            }

            $stmt = $conn->prepare("
                INSERT INTO messages ( content, timestamp, flight_id, passenger_id, company_id) 
                VALUES ( :message, NOW(), :flight_id, :passenger_id, :company_id)
            ");
            $stmt->execute([    
                ':message' => $message_content,
                ':flight_id' => $flight_id, 
                ':passenger_id' => $user_id, 
                ':company_id' => $company_id
            ]);
            $message_sent = true;
        } catch (PDOException $e) {
            $message_sent = false;
            $error_message = 'Error sending message: ' . $e->getMessage();
        } catch (Exception $e) {
            $message_sent = false;
            $error_message = $e->getMessage();
        }
    } else {
        $message_sent = false;
        $error_message = 'Please enter a message.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Info</title>
    <link rel="stylesheet" href="../flight/flight_info.css">
    <script>
        function toggleChatbox() {
            const chatbox = document.getElementById('chatbox');
            chatbox.style.display = chatbox.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Flight Info</h1>
        <?php if ($message): ?>
            <p class="<?= $success ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <p><strong>ID:</strong> <?= htmlspecialchars($flight['flight_id']) ?></p>
        <p><strong>Name:</strong> <?= htmlspecialchars($flight['name']) ?></p>
        <p><strong>Itinerary:</strong> <?= htmlspecialchars(implode(" → ", $itinerary)) ?></p>
        <p><strong>Fees:</strong> $<?= htmlspecialchars($flight['fees']) ?></p>
        <p><strong>Start Time:</strong> <?= htmlspecialchars($flight['start_time']) ?></p>
        <p><strong>End Time:</strong> <?= htmlspecialchars($flight['end_time']) ?></p>

        <h2>Your Account Balance: $<?= htmlspecialchars($account_balance) ?></h2>

        <h2>Take this flight?</h2>
        <form method="POST">
            <label>
                <input type="radio" name="payment_method" value="account">
                Pay from Account Balance
            </label><br>
            <label>
                <input type="radio" name="payment_method" value="cash">
                Pay with Cash
            </label><br>
            <button type="submit" name="book_flight">Take it</button>
        </form>

        <button class="back-to-home-btn" onclick="window.location.href='../passenger/home.php'">Back to Home</button>
    </div>

    <!-- Chat Icon -->
    <div class="chat-icon" onclick="toggleChatbox()">
        <img src="../uploads/5962463.png" alt="Chat Icon">
    </div>

    <!-- Chatbox -->
    <div id="chatbox" class="chatbox">
        <div class="chatbox-header">
            <span>Chat with Us</span>
            <button onclick="toggleChatbox()">&times;</button>
        </div>
        <div class="chatbox-body">
            <form method="POST">
                <textarea name="message" placeholder="Write your message..." required></textarea><br>
                <button type="submit" name="send_message">Send</button>
            </form>
        </div>
    </div>
</body>

</html>

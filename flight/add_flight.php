<?php
session_start();
require '../database/database.php'; // Database connection
$company_id = $_SESSION['user_id']; // Ensure the user is logged in

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $name = $_POST['name'];
    $flight_id = $_POST['id'];
    $itinerary = $_POST['itinerary'];
    $itinerary_array = array_map('trim', explode(',', $itinerary));
    $fees = $_POST['fees'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    try {
        // Insert flight data into the database
        $sql = "INSERT INTO flights (flight_id, name, itinerary, registered_passengers, pending_passengers, fees, start_time, end_time, is_completed, company_id) 
                VALUES (:flight_id, :name, :itinerary, 0, 0, :fees, :start_time, :end_time, 0, :company_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':flight_id' => $flight_id,
            ':name' => $name,
            ':itinerary' => json_encode($itinerary_array),
            ':fees' => $fees,
            ':start_time' => $start_time,
            ':end_time' => $end_time,
            ':company_id' => $company_id,
        ]);
        
        // Success message
        $message = "Flight added successfully!";
        $success = true;
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Flight</title>
    <link rel="stylesheet" href="add_flight.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Background overlay */
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            width: 350px;
        }

        .modal-content h2 {
            margin-bottom: 15px;
        }

        .modal-content p {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }

        .modal-content button {
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-content button.success {
            background-color: #28a745; /* Success green */
        }

        .modal-content button.success:hover {
            background-color: #218838;
        }

        .modal-content button.error {
            background-color: #dc3545; /* Error red */
        }

        .modal-content button.error:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Flight</h1>
        <form method="POST">
            <label for="name">Flight Name:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="id">Flight ID:</label>
            <input type="text" id="id" name="id" required>
            
            <label for="itinerary">Itinerary (Comma-separated cities):</label>
            <input type="text" id="itinerary" name="itinerary" required>
            
            <label for="fees">Fees:</label>
            <input type="number" id="fees" name="fees" required>
            
            <label for="start_time">Start Time:</label>
            <input type="datetime-local" id="start_time" name="start_time" required>
            
            <label for="end_time">End Time:</label>
            <input type="datetime-local" id="end_time" name="end_time" required>
            
            <button type="submit" style = "Width: 97%; "  >Add Flight </button>
        </form>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="modal">
        <div class="modal-content">
            <h2>Success</h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            <button class="success" id="close-success">Close</button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal" class="modal">
        <div class="modal-content">
            <h2>Error</h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            <button class="error" id="close-error">Close</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successModal = document.getElementById('success-modal');
            const errorModal = document.getElementById('error-modal');
            const closeSuccess = document.getElementById('close-success');
            const closeError = document.getElementById('close-error');

            // Show the appropriate modal based on success or failure
            if ("<?php echo $success ? 'true' : 'false'; ?>" === "true") {
                successModal.style.display = 'flex';

                // Redirect after success
                setTimeout(() => {
                    window.location.href = '../company/homepage.php';
                }, 2000);
            } else if ("<?php echo $message; ?>" !== "") {
                errorModal.style.display = 'flex';
            }

            // Close modals
            closeSuccess.addEventListener('click', function () {
                successModal.style.display = 'none';
                window.location.href = '../company/homepage.php';
            });

            closeError.addEventListener('click', function () {
                errorModal.style.display = 'none';
            });
        });
    </script>
</body>
</html>

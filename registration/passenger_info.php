<?php
include('../database/database.php');
$message = '';

$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account_number = $_POST['account_number'];
    $photo = $_FILES['photo']['name'];
    $passport_img = $_FILES['passport_img']['name'];

    // Handle file uploads
    move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/$photo");
    move_uploaded_file($_FILES['passport_img']['tmp_name'], "../uploads/$passport_img");

    // Update the database with passenger details
    try {
        $sql = "UPDATE users SET photo = :photo, passport_img = :passport_img, account_balance = :account_balance WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':photo' => $photo,
            ':passport_img' => $passport_img,
            ':account_balance' => $account_number,
            ':email' => $email
        ]);
        $message = "Passenger registration completed!";
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const modal = document.getElementById('success-modal');
                    modal.style.display = 'block';
                });
              </script>";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Info</title>
    <link rel="stylesheet" href="../registration/style.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 300px;
        }

        .modal-content h2 {
            color: #000543;
            margin-bottom: 10px;
        }

        .modal-content p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }

        .modal-content button {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-content button:hover {
            background-color:rgb(32, 143, 57);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Passenger Information</h1>
        <form method="POST" enctype="multipart/form-data" class="card p-4">
            <div class="form-group">
                <label>Photo:</label>
                <input type="file" name="photo" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Passport Image:</label>
                <input type="file" name="passport_img" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Bank Account Number:</label>
                <input type="text" name="account_number" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-custom">Submit</button>
        </form>

        <!-- Popup Modal -->
        <div id="success-modal" class="modal">
            <div class="modal-content">
                <h2>Success</h2>
                <p><?php echo $message; ?></p>
                <button id="close-modal">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('success-modal');
            const closeButton = document.getElementById('close-modal');

            // Show the modal if the message is set
            if ("<?php echo $message; ?>" === "Passenger registration completed!") {
                modal.style.display = 'flex';
            }

            // Handle close button click
            closeButton.addEventListener('click', function () {
                modal.style.display = 'none';
                window.location.href = '../index.php'; // Redirect to index.php after closing the modal
            });
        });
    </script>
</body>
</html>

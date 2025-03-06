<?php
session_start();
include('../database/database.php'); // Include database connection
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check user credentials
    try {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = $user['type'];
            $_SESSION['user_name'] = $user['name'];

            $message = "Login successful!";
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const modal = document.getElementById('success-modal');
                        modal.style.display = 'flex';
                    });
                  </script>";

            $redirect_url = ($user['type'] === 'company') ? '../company/homepage.php' : '../passenger/home.php';
            echo "<script>
                    setTimeout(() => {
                        window.location.href = '$redirect_url';
                    }, 2000);
                  </script>";
        } else {
            $message = "Invalid email or password.";
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const modal = document.getElementById('error-modal');
                        modal.style.display = 'flex';
                    });
                  </script>";
        }
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
    <title>Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: #c9d6ff;
            background: linear-gradient(to right, #e2e2e2, #c9d6ff);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .main-container {
            display: flex;
            background-color: #fff;
            border-radius: 50px; /* Curve the entire container */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 800px;
            max-width: 100%;
            overflow: hidden;
        }

        .container {
            padding: 50px;
            flex: 1;
        }

        h1 {
            color: #000543;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input {
            background-color: #f0f0f0;
            border: none;
            padding: 12px 15px;
            font-size: 14px;
            border-radius: 30px;
            width: 100%;
            outline: none;
            transition: 0.3s ease;
        }

        input:focus {
            background-color: #e0e0e0;
        }

        button {
            background-color: #1c1c6c;
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            padding: 12px 20px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        button:hover {
            background-color: #000543;
        }

        .image-container {
            background-color:rgb(24, 24, 91);
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top-left-radius: 65px; 
            border-bottom-left-radius: 65px; 
        }

        .image-container img {
            max-width: 80%;
            height: auto;
            border-radius: 10px;
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
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
            background-color: #28a745;
        }

        .modal-content button.success:hover {
            background-color: #218838;
        }

        .modal-content button.error {
            background-color: #dc3545;
        }

        .modal-content button.error:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Left Section: Form -->
        <div class="container">
            <h1>Login</h1>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
            <!-- Success Modal -->
            <div id="success-modal" class="modal">
                <div class="modal-content">
                    <h2>Success</h2>
                    <p><?php echo $message; ?></p>
                    <button class="success" id="close-success">Close</button>
                </div>
            </div>

            <!-- Error Modal -->
            <div id="error-modal" class="modal">
                <div class="modal-content">
                    <h2>Error</h2>
                    <p><?php echo $message; ?></p>
                    <button class="error" id="close-error">Close</button>
                </div>
            </div>
        </div>

        <!-- Right Section: Image -->
        <div class="image-container">
            <img src="../uploads/plane.gif" alt="Login Image">
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successModal = document.getElementById('success-modal');
            const errorModal = document.getElementById('error-modal');
            const closeSuccess = document.getElementById('close-success');
            const closeError = document.getElementById('close-error');

            if ("<?php echo $message; ?>" === "Login successful!") {
                successModal.style.display = 'flex';
            }

            if ("<?php echo $message; ?>" === "Invalid email or password.") {
                errorModal.style.display = 'flex';
            }

            closeSuccess.addEventListener('click', function () {
                successModal.style.display = 'none';
            });

            closeError.addEventListener('click', function () {
                errorModal.style.display = 'none';
            });
        });
    </script>
</body>

</html>

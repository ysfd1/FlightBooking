<?php
include('../database/database.php'); // Include the database connection script
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $type = $_POST['type'];

    try {
        // Check if the email already exists
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            // Email already exists
            $message = "The email address is already registered. Please try again.";
        } else {
            // Insert the basic user details into the database
            $sql = "INSERT INTO users (user_id, name, email, password, tel, type) VALUES (UUID(), :name, :email, :password, :telephone, :type)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $password,
                ':telephone' => $telephone,
                ':type' => $type
            ]);

            // Redirect to appropriate additional info page
            if ($type === 'passenger') {
                header("Location: passenger_info.php?email=$email");
            } elseif ($type === 'company') {
                header("Location: company_info.php?email=$email");
            }
            exit;
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
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Register</h1>
        <form method="POST" class="card p-4">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Telephone:</label>
                <input type="text" name="telephone" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Type:</label><br>
                <input type="radio" name="type" value="passenger" required> Passenger
                <input type="radio" name="type" value="company"> Company
            </div>
            <button type="submit" class="btn btn-custom">Next</button>
        </form>
        <p><?php echo $message; ?></p>
    </div>
</body>
</html>
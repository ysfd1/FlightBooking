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

// Update user data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];
    $photo = $_FILES['photo'];

    if ($photo['name']) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($photo["name"]);
        move_uploaded_file($photo["tmp_name"], $target_file);
        $photo_name = basename($photo["name"]);
    } else {
        $photo_name = $passenger['photo'];
    }

    $update_query = "UPDATE users SET name = :name, email = :email, tel = :tel, photo = :photo WHERE user_id = :user_id";
    $stmt = $conn->prepare($update_query);
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':tel' => $tel,
        ':photo' => $photo_name,
        ':user_id' => $passenger_id
    ]);

    header("Location: profile.php"); // Refresh to reflect changes
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Profile</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <div class="container">
    <div style="text-align: center;">
    <h1>Your Profile</h1>
    </div>
        <form action="profile.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($passenger['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($passenger['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="tel">Telephone:</label>
                <input type="tel" id="tel" name="tel" value="<?php echo htmlspecialchars($passenger['tel']); ?>" required>
            </div>
            <div class="form-group">
                <label for="photo">Profile Photo:</label>
                <input type="file" id="photo" name="photo">
                <img src="../uploads/<?php echo htmlspecialchars($passenger['photo']); ?>" alt="Profile Image" class="profile-image">
            </div>
            <button type="submit" class="btn">Save Changes</button>
        </form>
        <form action="home.php" method="get" style="display: inline;">
    <button type="submit" class="btn">Back to Home</button>
</form>
    </div>
</body>
</html>

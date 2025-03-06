<?php
require '../database/database.php';
session_start();
$company_id = $_SESSION['user_id']; // Assuming user_id is stored in the session after login.

// Fetch existing company details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch existing company details again to ensure we retain the logo if not updated
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    $name = !empty($_POST['name']) ? $_POST['name'] : $company['name'];
    $bio = !empty($_POST['bio']) ? $_POST['bio'] : $company['bio'];
    $address = !empty($_POST['address']) ? $_POST['address'] : $company['address'];

    // Handle logo upload or retain current logo
    if (!empty($_FILES['logo_img']['name'])) {
        $logo_tmp = $_FILES['logo_img']['tmp_name'];
        $logo_name = uniqid() . '_' . $_FILES['logo_img']['name'];
        move_uploaded_file($logo_tmp, "../uploads/" . $logo_name);
    } else {
        $logo_name = $company['logo_img']; // Retain the old logo if no new one is uploaded
    }

    // Update company profile
    $update_query = "UPDATE users SET name = ?, bio = ?, address = ?, logo_img = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->execute([$name, $bio, $address, $logo_name, $company_id]);

    // Refresh session data if needed and redirect back to homepage
    $_SESSION['user_name'] = $name; // Update session variables if necessary
    header("Location: homepage.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="homepage.css">
</head>
<body>
    <header class="main-header">
        <nav>
            <ul class="nav-links">
                <li><a href="homepage.php">Home</a></li>
                <li><a href="edit_profile.php">Profile</a></li>
                <li><a href="../login/login.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <h1>Edit Profile</h1>
        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Company Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="bio">Company Bio:</label>
                <textarea id="bio" name="bio" rows="3" required><?php echo htmlspecialchars($company['bio'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($company['address'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="logo_img">Company Logo:</label>
                <input type="file" id="logo_img" name="logo_img" accept="image/*">
                <p>Current Logo:</p>
                <img src="../uploads/<?php echo $company['logo_img'] ?? 'default_logo.png'; ?>" alt="Company Logo" class="logo-preview" style="max-width: 150px; max-height: 150px;">
            </div>
            <button type="submit" class="btn">Save Changes</button>
        </form>
    </div>
</body>
</html>

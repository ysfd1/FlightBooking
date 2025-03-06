<?php 
require '../database/database.php';
session_start();
$company_id = $_SESSION['user_id']; // Assuming user_id is stored in the session after login.

// Fetch company details.
$company_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($company_query);
$stmt->execute([$company_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch flights.
$flights_query = "SELECT * FROM flights WHERE company_id = ?";
$stmt = $conn->prepare($flights_query);
$stmt->execute([$company_id]);
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unread messages.
$messages_query = "SELECT * FROM messages WHERE company_id = ? AND is_read = 0"; 
$stmt = $conn->prepare($messages_query);
$stmt->execute([$company_id]);
$unread_messages = $stmt->rowCount();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
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
        <div class="header">
            <img src="../uploads/<?php echo $company['logo_img']; ?>" alt="Company Logo" class="logo">
            <h1><?php echo $company['name']; ?></h1>
            <div class="messages-link">
                <a href="messages.php">
                    Messages
                    <?php if ($unread_messages > 0): ?>
                        <span class="badge"><?php echo $unread_messages; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <div class="profile">
            <h2>Company Profile</h2>
            <div class="profile-info">
                <p><strong>Name:</strong> <?php echo $company['name']; ?></p>
                <p><strong>Bio:</strong> <?php echo $company['bio']; ?></p>
                <p><strong>Address:</strong> <?php echo $company['address']; ?></p>
                <p><strong>Total Flights:</strong> <?php echo count($flights); ?></p>
            </div>
            <button onclick="location.href='edit_profile.php'" class="btn">Edit Profile</button>
        </div>

        <div class="content">
            <h2>Flights</h2>
            <div>
                <button onclick="location.href='../flight/add_flight.php'" class="btn-add">+ Add Flight</button>
            </div>
            
            <table class="flights-table">
                <thead>
                    <tr>
                        <th>Flight ID</th>
                        <th>Flight Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($flights as $flight): ?>
                     <tr>
                        <td><?php echo htmlspecialchars($flight['flight_id']); ?></td>
                        <td><?php echo htmlspecialchars($flight['name']); ?></td>
                        <td>
                            <a href="../flight/flight_details.php?flight_id=<?php echo urlencode($flight['flight_id']); ?>" class="btn-view">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

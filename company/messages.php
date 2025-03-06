<?php
require '../database/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$company_id = $_SESSION['user_id'];

// Check if the messages page is being accessed
$isMessagesPage = basename($_SERVER['PHP_SELF']) == 'messages.php';

if ($isMessagesPage) {
    // Mark messages as read ONLY if on the messages page
    try {
        $update_query = "UPDATE messages SET is_read = 1 WHERE company_id = ? AND is_read = 0";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([$company_id]);
    } catch (PDOException $e) {
        error_log("Error marking messages as read: " . $e->getMessage());
    }
}

// Fetch messages (this is done regardless of the page)
$messages_query = "SELECT * FROM messages WHERE company_id = ? ORDER BY timestamp DESC";
$stmt = $conn->prepare($messages_query);
$stmt->execute([$company_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unread messages (for the homepage notification)
$unread_messages_query = "SELECT COUNT(*) FROM messages WHERE company_id = ? AND is_read = 0";
$unread_stmt = $conn->prepare($unread_messages_query);
$unread_stmt->execute([$company_id]);
$unread_messages_count = $unread_stmt->fetchColumn();

// Store unread message count in session
$_SESSION['unread_messages'] = $unread_messages_count;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Messages</title>
    <link rel="stylesheet" href="messages.css">
</head>
<body>
    <h1>Messages</h1>

    <?php if (count($messages) > 0): ?>
        <ul>
            <?php foreach ($messages as $message): ?>
                <li>
                    <strong>From:</strong> <?php 
                    if (isset($message['passenger_id'])) {
                        $senderName = getSenderName($message['passenger_id']);
                        if ($senderName === 'Unknown Sender') {
                            echo "Passenger ID: " . $message['passenger_id'];
                        } else {
                            echo $senderName;
                        }
                    } else {
                        echo "Passenger ID missing";
                    }
                    ?>
                    <br>
                    <strong>Message:</strong> <?php echo htmlspecialchars($message['content']); ?>
                    <br>
                    <strong>Timestamp:</strong> <?php echo $message['timestamp']; ?>
                    <br>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No messages found.</p>
    <?php endif; ?>

    <a href="homepage.php">Back to Dashboard</a>

</body>
</html>

<?php
function getSenderName($passenger_id) {
    global $conn;
    $sender_query = "SELECT name FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sender_query);
    $stmt->execute([$passenger_id]);
    $sender = $stmt->fetch(PDO::FETCH_ASSOC);
    return $sender ? $sender['name'] : 'Unknown Sender';
}
?>
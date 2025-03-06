<?php
require '../database/database.php';

if (isset($_GET['message_id'])) {
    $message_id = $_GET['message_id'];

    try {
        $update_query = "UPDATE messages SET is_read = 1 WHERE message_id = :message_id";
        $stmt = $conn->prepare($update_query);
        $stmt->bindParam(':message_id', $message_id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: messages.php"); 
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
?>
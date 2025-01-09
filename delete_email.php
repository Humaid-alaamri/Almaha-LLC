<?php
require 'db_connection.php';

if (isset($_POST['email_id'])) {
    $email_id = $_POST['email_id'];

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM email_notifications WHERE id = ?");
    $stmt->bind_param("i", $email_id);

    if ($stmt->execute()) {
        echo "Email deleted successfully!";
    } else {
        echo "Error deleting email: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<?php
require 'db_connection.php';

if (isset($_POST['log_id'])) {
    $log_id = $_POST['log_id'];

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE id = ?");
    $stmt->bind_param("i", $log_id);

    if ($stmt->execute()) {
        echo "Log cleared successfully!";
    } else {
        echo "Error clearing log: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

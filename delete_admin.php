<?php
require 'db_connection.php';

if (isset($_POST['admin_id'])) {
    $admin_id = $_POST['admin_id'];

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->bind_param("i", $admin_id);

    if ($stmt->execute()) {
        echo "Admin deleted successfully!";
    } else {
        echo "Error deleting admin: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

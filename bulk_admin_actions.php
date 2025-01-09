<?php
// bulk_admin_actions.php
require 'db_connection.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.html');
    exit();
}

if (isset($_POST['bulk_action']) && isset($_POST['admin_ids'])) {
    $bulk_action = $_POST['bulk_action'];
    $admin_ids = $_POST['admin_ids'];
    $ids = implode(',', array_map('intval', $admin_ids));

    if ($bulk_action == 'delete') {
        $query = "DELETE FROM admins WHERE id IN ($ids)";
    } elseif ($bulk_action == 'block') {
        $query = "UPDATE admins SET status='blocked' WHERE id IN ($ids)";
    } elseif ($bulk_action == 'unblock') {
        $query = "UPDATE admins SET status='active' WHERE id IN ($ids)";
    }

    if ($conn->query($query)) {
        header("Location: admin.php?message=Bulk action '$bulk_action' executed successfully.");
    } else {
        echo "Error executing bulk action: " . $conn->error;
    }
} else {
    header("Location: admin.php?error=No action selected or no admins selected.");
}
?>

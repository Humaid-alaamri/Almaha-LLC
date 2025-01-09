<?php
// bulk_email_actions.php
require 'db_connection.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.html');
    exit();
}

if (isset($_POST['bulk_action']) && isset($_POST['email_ids'])) {
    $bulk_action = $_POST['bulk_action'];
    $email_ids = $_POST['email_ids'];
    $ids = implode(',', array_map('intval', $email_ids));

    if ($bulk_action == 'delete') {
        $query = "DELETE FROM email_notifications WHERE id IN ($ids)";
        if ($conn->query($query)) {
            header("Location: admin.php?message=Selected emails have been deleted.");
        } else {
            echo "Error deleting emails: " . $conn->error;
        }
    } elseif ($bulk_action == 'export') {
        $query = "SELECT email FROM email_notifications WHERE id IN ($ids)";
        $result = $conn->query($query);

        $filename = "emails_export_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Email'));

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, array($row['email']));
        }
        fclose($output);
        exit;
    }
} else {
    header("Location: admin.php?error=No action selected or no emails selected.");
}
?>

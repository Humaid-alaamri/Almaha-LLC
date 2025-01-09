<?php
// clear_logs.php
require 'db_connection.php';

$query = "DELETE FROM logs WHERE 1=1";

if (isset($_POST['filter_username']) && $_POST['filter_username'] != '') {
    $filter_username = $conn->real_escape_string($_POST['filter_username']);
    $query .= " AND username LIKE '%$filter_username%'";
}

if (isset($_POST['filter_date_from']) && $_POST['filter_date_from'] != '') {
    $filter_date_from = $conn->real_escape_string($_POST['filter_date_from']);
    $query .= " AND timestamp >= '$filter_date_from'";
}

if (isset($_POST['filter_date_to']) && $_POST['filter_date_to'] != '') {
    $filter_date_to = $conn->real_escape_string($_POST['filter_date_to']);
    $query .= " AND timestamp <= '$filter_date_to 23:59:59'";
}

if (isset($_POST['filter_country']) && $_POST['filter_country'] != '') {
    $filter_country = $conn->real_escape_string($_POST['filter_country']);
    $query .= " AND country LIKE '%$filter_country%'";
}

if (isset($_POST['filter_ip']) && $_POST['filter_ip'] != '') {
    $filter_ip = $conn->real_escape_string($_POST['filter_ip']);
    $query .= " AND ip_address LIKE '%$filter_ip%'";
}

if (isset($_POST['filter_status']) && $_POST['filter_status'] != '') {
    $filter_status = $conn->real_escape_string($_POST['filter_status']);
    $query .= " AND status = '$filter_status'";
}

if ($conn->query($query)) {
    echo "Logs have been cleared.";
} else {
    echo "Error clearing logs: " . $conn->error;
}
?>

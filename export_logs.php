<?php
// export_logs.php
require 'db_connection.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=logs_export_' . date('Ymd') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, array('Username', 'Device Type', 'Operating System', 'IP Address', 'Country', 'Timestamp', 'Status'));

$query = "SELECT username, device_type, operating_system, ip_address, country, timestamp, status FROM logs WHERE 1=1";

if (isset($_GET['filter_username']) && $_GET['filter_username'] != '') {
    $filter_username = $conn->real_escape_string($_GET['filter_username']);
    $query .= " AND username LIKE '%$filter_username%'";
}

if (isset($_GET['filter_date_from']) && $_GET['filter_date_from'] != '') {
    $filter_date_from = $conn->real_escape_string($_GET['filter_date_from']);
    $query .= " AND timestamp >= '$filter_date_from'";
}

if (isset($_GET['filter_date_to']) && $_GET['filter_date_to'] != '') {
    $filter_date_to = $conn->real_escape_string($_GET['filter_date_to']);
    $query .= " AND timestamp <= '$filter_date_to 23:59:59'";
}

if (isset($_GET['filter_country']) && $_GET['filter_country'] != '') {
    $filter_country = $conn->real_escape_string($_GET['filter_country']);
    $query .= " AND country LIKE '%$filter_country%'";
}

if (isset($_GET['filter_ip']) && $_GET['filter_ip'] != '') {
    $filter_ip = $conn->real_escape_string($_GET['filter_ip']);
    $query .= " AND ip_address LIKE '%$filter_ip%'";
}

if (isset($_GET['filter_status']) && $_GET['filter_status'] != '') {
    $filter_status = $conn->real_escape_string($_GET['filter_status']);
    $query .= " AND status = '$filter_status'";
}

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit;
?>

<?php
require 'db_connection.php';

$page = $_GET['page'];
$stmt = $conn->prepare("SELECT content FROM page_content WHERE page_name = ?");
$stmt->bind_param("s", $page);
$stmt->execute();
$stmt->bind_result($content);
$stmt->fetch();
echo $content;
$stmt->close();
$conn->close();
?>

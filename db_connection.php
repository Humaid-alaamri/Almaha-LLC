<?php
// Database connection details
$servername = "localhost";
$username = "u676630011_page";
$password = "!@Humaid123";
$dbname = "u676630011_page";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

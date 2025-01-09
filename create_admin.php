<?php
// create_admin.php

// **⚠️ IMPORTANT ⚠️**
// After running this script, DELETE it or restrict its access to prevent unauthorized use.

// Start by enabling error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require 'db_connection.php';

// Define the admin credentials
$admin_username = 'humaid';
$admin_password = 'admin'; // **Change this password immediately after setup**
$admin_role = 'full_admin'; // Assigning full admin permissions
$admin_status = 'active';

// Check if the admin already exists
$stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "Admin user '{$admin_username}' already exists.";
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Hash the password
$password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

// Prepare the insert statement
$insert_stmt = $conn->prepare("INSERT INTO admins (username, password_hash, role, status) VALUES (?, ?, ?, ?)");
if (!$insert_stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$insert_stmt->bind_param("ssss", $admin_username, $password_hash, $admin_role, $admin_status);

// Execute the statement
if ($insert_stmt->execute()) {
    echo "Admin user '{$admin_username}' created successfully.";
} else {
    echo "Error creating admin user: " . htmlspecialchars($insert_stmt->error);
}

$insert_stmt->close();
$conn->close();
?>

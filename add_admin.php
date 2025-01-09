<?php
// add_admin.php

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'full_admin') {
    header('Location: login.html'); // Ensure you have a login.html page
    exit();
}

require 'db_connection.php';

// Enable error reporting for debugging (remove or comment out in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if form data is set
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['admin_username']) && isset($_POST['admin_password']) && isset($_POST['admin_role'])) {
        $admin_username = trim($_POST['admin_username']);
        $admin_password = $_POST['admin_password'];
        $admin_role = $_POST['admin_role'];

        // Validate inputs
        if (empty($admin_username) || empty($admin_password)) {
            header("Location: admin.php?error=Username and password cannot be empty.");
            exit();
        }

        // Validate role
        $allowed_roles = ['editor_home', 'editor_about', 'editor_both', 'full_admin'];
        if (!in_array($admin_role, $allowed_roles)) {
            header("Location: admin.php?error=Invalid role selected.");
            exit();
        }

        // Hash the password
        $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

        // Insert into admins table
        $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, role, status) VALUES (?, ?, ?, 'active')");
        if ($stmt) {
            $stmt->bind_param("sss", $admin_username, $password_hash, $admin_role);
            if ($stmt->execute()) {
                header("Location: admin.php?message=New admin added successfully.");
                exit();
            } else {
                // Handle execution error (e.g., duplicate username)
                header("Location: admin.php?error=Failed to add admin. " . urlencode($stmt->error));
                exit();
            }
        } else {
            // Handle preparation error
            header("Location: admin.php?error=Failed to prepare statement.");
            exit();
        }
    } else {
        // Handle missing form data
        header("Location: admin.php?error=Invalid form data.");
        exit();
    }
} else {
    // Handle invalid request method
    header("Location: admin.php?error=Invalid request method.");
    exit();
}
?>

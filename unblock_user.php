<?php
// unblock_user.php

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
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);

        // Update user's status to 'active'
        $stmt = $conn->prepare("UPDATE admins SET status = 'active' WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            if ($stmt->execute()) {
                header("Location: admin.php?message=User '$username' has been unblocked.");
                exit();
            } else {
                // Handle execution error
                header("Location: admin.php?error=Failed to unblock user.");
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

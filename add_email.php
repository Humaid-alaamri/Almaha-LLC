<?php
// add_email.php

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
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: admin.php?error=Invalid email format.");
            exit();
        }

        // Insert into email_notifications table
        $stmt = $conn->prepare("INSERT INTO email_notifications (email) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                header("Location: admin.php?message=Email recipient added successfully.");
                exit();
            } else {
                // Handle execution error (e.g., duplicate email)
                header("Location: admin.php?error=Failed to add email. " . urlencode($stmt->error));
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

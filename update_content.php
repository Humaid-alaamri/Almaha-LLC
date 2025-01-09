<?php
// update_content.php

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.html'); // Ensure you have a login.html page
    exit();
}

$admin_role = $_SESSION['role'];

// Include database connection
require 'db_connection.php';

// Enable error reporting for debugging (remove or comment out in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to check if admin has permission to edit a specific page
function can_edit_page($role, $page) {
    $permissions = [
        'editor_home' => ['home'],
        'editor_about' => ['about'],
        'editor_both' => ['home', 'about'],
        'full_admin' => ['home', 'about']
    ];
    return in_array($page, $permissions[$role]);
}

// Check if form data is set
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['content']) && isset($_POST['page_name'])) {
        $content = $_POST['content'];
        $page_name = $_POST['page_name'];

        // Validate page_name
        $allowed_pages = ['home', 'about'];
        if (!in_array($page_name, $allowed_pages)) {
            header("Location: admin.php?error=Invalid page name.");
            exit();
        }

        // Check if admin has permission to edit this page
        if (!can_edit_page($admin_role, $page_name)) {
            header("Location: admin.php?error=You do not have permission to edit this page.");
            exit();
        }

        // Update the content in the database
        $stmt = $conn->prepare("UPDATE page_content SET content = ? WHERE page_name = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $content, $page_name);
            if ($stmt->execute()) {
                // Fetch all email recipients
                $emails = [];
                $email_stmt = $conn->prepare("SELECT email FROM email_notifications");
                if ($email_stmt) {
                    $email_stmt->execute();
                    $email_stmt->bind_result($email);
                    while ($email_stmt->fetch()) {
                        $emails[] = $email;
                    }
                    $email_stmt->close();
                } else {
                    // Handle error in fetching emails
                    error_log("Failed to prepare email fetching statement: " . $conn->error);
                }

                // Send email notifications
                if (!empty($emails)) {
                    $subject = "Website Content Updated: " . ucfirst($page_name) . " Page";
                    $message = "Hello,\n\nThe content of the '" . ucfirst($page_name) . "' page on our website has been updated. Please visit the website to see the latest changes.\n\nBest regards,\nWebsite Admin";
                    $headers = "From: no-reply@yourdomain.com"; // Replace with your domain's no-reply email

                    foreach ($emails as $recipient) {
                        // Using mail function, consider using a better mail library for reliability
                        if (!mail($recipient, $subject, $message, $headers)) {
                            // Handle mail sending failure
                            error_log("Failed to send email to: $recipient");
                        }
                    }
                }

                // Redirect back with success message
                header("Location: admin.php?message=Content updated successfully.");
                exit();
            } else {
                // Handle execution error
                error_log("Failed to execute update statement: " . $stmt->error);
                header("Location: admin.php?error=Failed to update content.");
                exit();
            }
        } else {
            // Handle preparation error
            error_log("Failed to prepare update statement: " . $conn->error);
            header("Location: admin.php?error=Failed to prepare update statement.");
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

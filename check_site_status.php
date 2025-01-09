<?php
// check_site_status.php

// 1. Include database connection
require '../db_connection.php';

// 2. Implement your logic to detect hacking or changes
function check_for_hacks_or_changes()
{
    /*
     * Example checks:
     *   - Compare current content hashes of critical files (e.g., index.php) with a known "good" hash
     *   - Look for unexpected new admin users in your admins table
     *   - etc.
     *
     * For demonstration, let's say we do a dummy check that always returns "OK".
     */
    return "OK"; // or "HACKED" if something is suspicious
}

$status = check_for_hacks_or_changes();

// 3. Fetch all email recipients
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
    error_log("Failed to prepare email fetching statement: " . $conn->error);
}

// 4. Send email notifications
if (!empty($emails)) {
    $subject = "Website Status Check - " . date('Y-m-d H:i:s');
    $message = "Hello,\n\n";
    $message .= "This is an automated status check of your website.\n";
    $message .= "Current Status: $status\n\n";
    $message .= "Best regards,\n";
    $message .= "Website Admin";

    $headers = "From: no-reply@almaha-oman.com"; // Replace with your domain's no-reply email

    foreach ($emails as $recipient) {
        if (!mail($recipient, $subject, $message, $headers)) {
            error_log("Failed to send status email to: $recipient");
        }
    }
}

$conn->close();

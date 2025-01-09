<?php
// about.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Monitor and Respond to Cyber Attacks</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <header>
        <h1><i class="fas fa-users"></i> About Us</h1>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
            </ul>
        </nav>
    </header>

    <section class="fixed-text">
        <h2><i class="fas fa-bullseye"></i> Problem Statement</h2>
        <p>Generally, misused websites help cybercriminals get unauthorized information. Creating a system to track and monitor website changes so that users can remain informed about any modifications is essential. 
To start with, the content of websites is dynamic and ever-changing. Keeping track of changes occurring on several websites can be difficult for people or organizations, particularly when looking at multiple pages or sites at once. The issue is figuring out a way to let system admins and database cells get notified whenever something changes on the website.
Moreover, the script is programmed to track visitors to the website. When a user signs in to a site by credential, the script will check the details of that user.
System administrators should keep the websites up to date to avoid vulnerabilities. In addition, scan websites from time to time to prevent cyber-attacks.
The project addresses the problem by creating scripts to scan any website to discover and achieve maximum security.

We are dedicated to improving website security and helping organizations protect against unauthorized access by tracking website changes in real-time.</p>
    </section>

     <section class="dynamic-content">
        <h2><i class="fas fa-sync-alt"></i> Latest Updates</h2>
        <?php
        require 'db_connection.php';
        $stmt = $conn->prepare("SELECT content FROM page_content WHERE page_name = ?");
        $page = 'about';
        $stmt->bind_param("s", $page);
        $stmt->execute();
        $stmt->bind_result($content);
        if ($stmt->fetch()) {
            echo "<p>$content</p>";
        } else {
            echo "<p>No content available.</p>";
        }
        $stmt->close();
        $conn->close();
        ?>
    </section>

    <footer>
        <p>Done by: Humaid AL-AAmri ID: 22F22683</p>
    </footer>

</body>
</html>

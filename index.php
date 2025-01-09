<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Monitor and Respond to Cyber Attacks</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

   <header>
        <div class="logo-container">
            <a href="index.php">
                <img src="images.jpg" alt="Logo" class="logo">
            </a>
        </div>
        <h1><i class="fas fa-shield-alt"></i> Almaha Company LLC</h1>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                <li><a href="admin.php"><i class="fas fa-user-shield"></i> Login</a></li>
            </ul>
        </nav>
    </header>

    <!-- Slideshow Container -->
    <section class="slideshow-container">
        <div class="slides fade">
            <img src="slide1.jpg" alt="Slide 1">
        </div>

        <div class="slides fade">
            <img src="slide2.jpg" alt="Slide 2">
        </div>

        <div class="slides fade">
            <img src="slide3.jpg" alt="Slide 3">
        </div>

        <!-- Navigation arrows -->
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </section>

    <!-- Dots for manual control -->
    <div class="dot-container">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
    </div>

    <section class="fixed-text">
        <h2><i class="fas fa-lock"></i> Why Website Tracking Matters</h2>
        <p>There are numerous techniques and resources available to efficiently track modifications to websites. Using tools or services that automatically track and notify you of any changes made to individual web pages or entire websites is one popular method. These tools usually compare the current and past versions of a website using crawling and diffing techniques to detect any changes.

Website monitoring services frequently include the ability to set up specific webpage sections or elements to be tracked, alter the frequency of monitoring, and get notifications by email, SMS, or other channels of communication. Because of this flexibility, users can customize their monitoring to suit their own requirements and tastes.

For people who depend on current information, such as researchers, corporations, and content providers, keeping an eye on website modifications can be quite helpful. It enables them to maintain the caliber and correctness of their online presence, stay one step ahead of the competition, and react to important changes or incidents.

Keeping an eye on website updates is a crucial habit that keeps people and businesses safe, proactive, and informed in the ever-changing digital world. You can efficiently monitor and react to any changes made to websites by putting the appropriate tools and tactics in place, giving you access to the most up-to-date and trustworthy information possible.
.</p>
    </section>

    <section class="dynamic-content">
        <h2><i class="fas fa-sync-alt"></i> Latest Updates</h2>
        <?php
        require 'db_connection.php';
        $stmt = $conn->prepare("SELECT content FROM page_content WHERE page_name = ?");
        $page = 'home';
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

    <script>
        let slideIndex = 1;
        showSlides(slideIndex);

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function currentSlide(n) {
            showSlides(slideIndex = n);
        }

        function showSlides(n) {
            let i;
            let slides = document.getElementsByClassName("slides");
            let dots = document.getElementsByClassName("dot");
            if (n > slides.length) {slideIndex = 1}
            if (n < 1) {slideIndex = slides.length}
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            for (i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }
            slides[slideIndex-1].style.display = "block";
            dots[slideIndex-1].className += " active";
        }

        setInterval(function() {
            plusSlides(1);
        }, 5000);
    </script>

</body>
</html>

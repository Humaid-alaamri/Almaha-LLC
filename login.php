<?php
// login.php

// Start the session with secure parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Ensure you are using HTTPS
ini_set('session.use_strict_mode', 1);
session_start();

// Enable error reporting for debugging (remove or comment out in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require 'db_connection.php';

// Function to get device type
function get_device_type() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'Mobile') !== false) {
        return 'Mobile';
    } else {
        return 'Desktop';
    }
}

// Function to get operating system
function get_operating_system() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $os_platform = "Unknown OS Platform";
    $os_array = array(
        '/windows nt 11/i'     => 'Windows 11',
        '/windows nt 10/i'     => 'Windows 10',
        '/windows nt 6.3/i'    => 'Windows 8.1',
        '/windows nt 6.2/i'    => 'Windows 8',
        '/windows nt 6.1/i'    => 'Windows 7',
        '/windows nt 6.0/i'    => 'Windows Vista',
        '/windows nt 5.2/i'    => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'    => 'Windows XP',
        '/macintosh|mac os x/i'=> 'Mac OS X',
        '/mac_powerpc/i'       => 'Mac OS 9',
        '/linux/i'             => 'Linux',
        '/ubuntu/i'            => 'Ubuntu',
        '/iphone/i'            => 'iPhone',
        '/ipod/i'              => 'iPod',
        '/ipad/i'              => 'iPad',
        '/android/i'           => 'Android',
        '/blackberry/i'        => 'BlackBerry',
        '/webos/i'             => 'Mobile'
    );

    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os_platform = $value;
            break;
        }
    }
    return $os_platform;
}

// Function to get user IP
function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // IP from shared internet
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // IP passed from proxy
        // In case of multiple IPs, take the first one
        $ip_addresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ip_addresses[0]);
    } else {
        // Direct IP
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Function to get country from IP using ipinfo.io
function get_country_from_ip($ip) {
    $url = "https://ipinfo.io/{$ip}/country";
    $country = @file_get_contents($url);
    if ($country !== false) {
        return trim($country);
    } else {
        return 'Unknown';
    }
}

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

// CSRF Protection: Generate and store CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle POST request (login attempt)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Log the failed CSRF attempt
        error_log("CSRF token mismatch for username: " . $_POST['username']);
        header('Location: login.php?error=' . urlencode('Invalid CSRF token.'));
        exit();
    }

    // Retrieve and sanitize form data
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $device_type = get_device_type();
    $operating_system = get_operating_system();
    $ip_address = get_user_ip();
    $country = get_country_from_ip($ip_address);

    // Validate input
    if (empty($username) || empty($password)) {
        header('Location: login.php?error=' . urlencode('Please enter both username and password.'));
        exit();
    }

    // Prepare statement to fetch admin user
    $stmt = $conn->prepare("SELECT username, password_hash, role, status FROM admins WHERE username = ?");
    if (!$stmt) {
        // Log the error
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        header('Location: login.php?error=' . urlencode('An unexpected error occurred. Please try again later.'));
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($fetched_username, $password_hash, $role, $status);
        $stmt->fetch();

        // Check if the admin is blocked
        if ($status === 'blocked') {
            // Log the attempt
            $log_stmt = $conn->prepare("INSERT INTO logs (username, device_type, operating_system, ip_address, country, status) VALUES (?, ?, ?, ?, ?, 'blocked')");
            if ($log_stmt) {
                $log_stmt->bind_param("sssss", $username, $device_type, $operating_system, $ip_address, $country);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                // Log the error
                error_log("Prepare failed for logging blocked attempt: (" . $conn->errno . ") " . $conn->error);
            }

            header('Location: login.php?error=' . urlencode('Your account is blocked. Please contact the administrator.'));
            exit();
        }

        // Verify password
        if (password_verify($password, $password_hash)) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['username'] = $fetched_username;
            $_SESSION['role'] = $role; // Store the role in the session

            // Log successful login
            $log_stmt = $conn->prepare("INSERT INTO logs (username, device_type, operating_system, ip_address, country, status) VALUES (?, ?, ?, ?, ?, 'active')");
            if ($log_stmt) {
                $log_stmt->bind_param("sssss", $username, $device_type, $operating_system, $ip_address, $country);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                // Log the error
                error_log("Prepare failed for logging successful login: (" . $conn->errno . ") " . $conn->error);
            }

            header('Location: admin.php');
            exit();
        } else {
            // Log failed login attempt
            $log_stmt = $conn->prepare("INSERT INTO logs (username, device_type, operating_system, ip_address, country, status) VALUES (?, ?, ?, ?, ?, 'failed')");
            if ($log_stmt) {
                $log_stmt->bind_param("sssss", $username, $device_type, $operating_system, $ip_address, $country);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                // Log the error
                error_log("Prepare failed for logging failed login: (" . $conn->errno . ") " . $conn->error);
            }

            header('Location: login.php?error=' . urlencode('Invalid credentials.'));
            exit();
        }
    } else {
        // Log failed login attempt for non-existent user
        $log_stmt = $conn->prepare("INSERT INTO logs (username, device_type, operating_system, ip_address, country, status) VALUES (?, ?, ?, ?, ?, 'failed')");
        if ($log_stmt) {
            $log_stmt->bind_param("sssss", $username, $device_type, $operating_system, $ip_address, $country);
            $log_stmt->execute();
            $log_stmt->close();
        } else {
            // Log the error
            error_log("Prepare failed for logging failed login (non-existent user): (" . $conn->errno . ") " . $conn->error);
        }

        header('Location: login.php?error=' . urlencode('Invalid credentials.'));
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // If accessed via GET, display the login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <!-- Link to your CSS file -->
        <link rel="stylesheet" href="styles.css">
        <style>
            /* Basic styling for the login form */
            body {
                font-family: Arial, sans-serif;
                background-color: #f2f2f2;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }

            .login-form {
                background-color: #ffffff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                width: 300px;
            }
            
            .login-form h1 {
                margin-bottom: 20px;
                text-align: center;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group label {
                display: block;
                margin-bottom: 5px;
            }

            .form-group input {
                width: 100%;
                padding: 10px;
                box-sizing: border-box;
            }

            button[type="submit"] {
                width: 100%;
                padding: 10px;
                background-color: #4CAF50;
                border: none;
                color: #ffffff;
                font-size: 16px;
                cursor: pointer;
                border-radius: 4px;
            }

            button[type="submit"]:hover {
                background-color: #45a049;
            }

            .error-message {
                color: red;
                margin-top: 10px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="login-form">
            <h1>Admin Login</h1>
            <form action="login.php" method="POST">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit">Login</button>

                <!-- Error message display -->
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
            </form>
        </div>
    </body>
    </html>
    <?php
}
?>

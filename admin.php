<?php
// admin.php

// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$admin_role = $_SESSION['role'];

// Include database connection
require 'db_connection.php';

// Enable error reporting for debugging (remove or comment out in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// System Health Monitoring Functions
function getServerUptime() {
    $uptimeString = @file_get_contents('/proc/uptime');
    if ($uptimeString === false) {
        return 'Uptime information not available.';
    } else {
        $uptime = (int)explode(' ', trim($uptimeString))[0];

        $days = floor($uptime / 86400);
        $hours = floor(($uptime % 86400) / 3600);
        $minutes = floor(($uptime % 3600) / 60);

        $uptimeParts = [];
        if ($days > 0) {
            $uptimeParts[] = $days . ' day' . ($days > 1 ? 's' : '');
        }
        if ($hours > 0) {
            $uptimeParts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
        }
        if ($minutes > 0) {
            $uptimeParts[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }

        return implode(', ', $uptimeParts);
    }
}

// Fetch system uptime
$uptime = getServerUptime();

// Database Response Time
$start_time = microtime(true);
if ($conn->query('SELECT 1') === FALSE) {
    $db_response_time = 'Error querying database.';
} else {
    $db_response_time = (microtime(true) - $start_time) * 1000; // in milliseconds
}

// Error Rates
$error_count_result = $conn->query("SELECT COUNT(*) as error_count FROM errors WHERE timestamp >= NOW() - INTERVAL 1 DAY");
if ($error_count_result) {
    $error_count_row = $error_count_result->fetch_assoc();
    $error_count = $error_count_row['error_count'];
} else {
    $error_count = 'Error retrieving error count.';
}

// Functions to calculate percentages for gauges
function getUptimePercentage($uptime) {
    // Assuming maximum uptime is 365 days (for percentage calculation)
    $max_uptime_days = 365;

    // Parse uptime string to get total minutes
    $uptime_minutes = parseUptimeToMinutes($uptime);

    // Convert maximum uptime to minutes
    $max_uptime_minutes = $max_uptime_days * 24 * 60;

    $percentage = ($uptime_minutes / $max_uptime_minutes) * 100;
    return min($percentage, 100); // Ensure it doesn't exceed 100%
}

function getResponseTimePercentage($response_time) {
    // Assuming 0 ms is best, 500 ms is worst
    $percentage = (1 - ($response_time / 500)) * 100;
    return max(min($percentage, 100), 0); // Ensure between 0 and 100%
}

function getErrorCountPercentage($error_count) {
    // Assuming 0 errors is best, 50 errors is worst
    $percentage = (1 - ($error_count / 50)) * 100;
    return max(min($percentage, 100), 0); // Ensure between 0 and 100%
}

function parseUptimeToMinutes($uptime_str) {
    // Parse the uptime string and calculate total minutes
    $days = $hours = $minutes = 0;
    if (preg_match('/(\d+) day/', $uptime_str, $matches)) {
        $days = $matches[1];
    }
    if (preg_match('/(\d+) hour/', $uptime_str, $matches)) {
        $hours = $matches[1];
    }
    if (preg_match('/(\d+) minute/', $uptime_str, $matches)) {
        $minutes = $matches[1];
    }
    $total_minutes = ($days * 24 * 60) + ($hours * 60) + $minutes;
    return $total_minutes;
}

function getGaugeColor($percentage, $inverse = false) {
    if ($inverse) {
        // For inverse gauges (where lower percentage is better)
        if ($percentage >= 75) {
            return '#f44336'; // Red
        } elseif ($percentage >= 50) {
            return '#ffeb3b'; // Yellow
        } else {
            return '#4caf50'; // Green
        }
    } else {
        // For normal gauges (where higher percentage is better)
        if ($percentage >= 75) {
            return '#4caf50'; // Green
        } elseif ($percentage >= 50) {
            return '#ffeb3b'; // Yellow
        } else {
            return '#f44336'; // Red
        }
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <!-- Include your stylesheets -->
    <link rel="stylesheet" href="styles.css">
    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Basic styles for success and error messages */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }

        /* Additional styles can be added here */
        .gauge {
            position: relative;
            width: 100%;
            background-color: #ddd;
            border-radius: 25px;
            margin: 10px 0;
            height: 25px;
        }

        .gauge-fill {
            height: 100%;
            border-radius: 25px;
            transition: width 0.5s;
        }

        .gauge-text {
            position: absolute;
            left: 10px;
            top: 0;
            height: 100%;
            display: flex;
            align-items: center;
            font-weight: bold;
        }

        .gauge-value {
            position: absolute;
            right: 10px;
            top: 0;
            height: 100%;
            display: flex;
            align-items: center;
            font-weight: bold;
        }

        .health-item {
            margin-bottom: 20px;
        }

        /* Style for block and unblock buttons */
        .block-button, .unblock-button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            color: inherit;
            font-size: 1em;
        }

        .block-button:hover {
            color: #f44336; /* Red */
        }

        .unblock-button:hover {
            color: #4caf50; /* Green */
        }

        /* Style for forms and tables */
        form {
            margin-bottom: 20px;
        }

        form label {
            display: block;
            margin-top: 10px;
        }

        form input, form select, form textarea, form button {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th input[type="checkbox"] {
            transform: scale(1.2);
        }

    </style>
</head>
<body>

    <header>
        <h1><i class="fas fa-user-shield"></i> Admin Panel</h1>
        <nav>
            <ul>
                <li><a href="admin.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="admin-content">

        <!-- Display Success or Error Messages -->
        <?php
        if (isset($_GET['message'])) {
            echo "<div class='success-message'>" . htmlspecialchars($_GET['message']) . "</div>";
        }
        if (isset($_GET['error'])) {
            echo "<div class='error-message'>" . htmlspecialchars($_GET['error']) . "</div>";
        }
        ?>

        <!-- System Health Monitoring -->
        <h2>System Health Monitoring</h2>

        <div class="health-item">
            <i class="fas fa-database"></i>
            <div class="gauge">
                <div class="gauge-fill" style="width: <?php echo getResponseTimePercentage($db_response_time); ?>%; background-color: <?php echo getGaugeColor(getResponseTimePercentage($db_response_time), true); ?>;"></div>
                <span class="gauge-text">Database Response Time</span>
                <span class="gauge-value"><?php echo is_numeric($db_response_time) ? number_format($db_response_time, 2) . " ms" : $db_response_time; ?></span>
            </div>
        </div>
        <div class="health-item">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="gauge">
                <div class="gauge-fill" style="width: <?php echo getErrorCountPercentage($error_count); ?>%; background-color: <?php echo getGaugeColor(getErrorCountPercentage($error_count), true); ?>;"></div>
                <span class="gauge-text">Error Count (Last 24h)</span>
                <span class="gauge-value"><?php echo htmlspecialchars($error_count); ?></span>
            </div>
        </div>

        <!-- Edit Home Page Content -->
        <?php if (can_edit_page($admin_role, 'home')): ?>
            <h2>Edit Home Page Content</h2>
            <form action="update_content.php" method="POST">
                <textarea name="content" rows="10" cols="80"><?php
                    // Fetch current content
                    $stmt = $conn->prepare("SELECT content FROM page_content WHERE page_name = ?");
                    if ($stmt) {
                        $page_name = 'home';
                        $stmt->bind_param("s", $page_name);
                        $stmt->execute();
                        $stmt->bind_result($content_home);
                        if ($stmt->fetch()) {
                            echo htmlspecialchars($content_home);
                        }
                        $stmt->close();
                    } else {
                        echo "Error fetching content: " . htmlspecialchars($conn->error);
                    }
                ?></textarea>
                <input type="hidden" name="page_name" value="home">
                <button type="submit"><i class="fas fa-save"></i> Update Home Content</button>
            </form>
        <?php endif; ?>

        <!-- Edit About Us Page Content -->
        <?php if (can_edit_page($admin_role, 'about')): ?>
            <h2>Edit About Us Page Content</h2>
            <form action="update_content.php" method="POST">
                <textarea name="content" rows="10" cols="80"><?php
                    // Fetch current content
                    $stmt = $conn->prepare("SELECT content FROM page_content WHERE page_name = ?");
                    if ($stmt) {
                        $page_name = 'about';
                        $stmt->bind_param("s", $page_name);
                        $stmt->execute();
                        $stmt->bind_result($content_about);
                        if ($stmt->fetch()) {
                            echo htmlspecialchars($content_about);
                        }
                        $stmt->close();
                    } else {
                        echo "Error fetching content: " . htmlspecialchars($conn->error);
                    }
                ?></textarea>
                <input type="hidden" name="page_name" value="about">
                <button type="submit"><i class="fas fa-save"></i> Update About Content</button>
            </form>
        <?php endif; ?>

        <!-- Only Full Admins Can See the Following Sections -->
        <?php if ($admin_role === 'full_admin'): ?>

            <!-- Add New Admin -->
            <h2>Add New Admin</h2>
            <form action="add_admin.php" method="POST">
                <label for="admin_username">Username:</label>
                <input type="text" id="admin_username" name="admin_username" required>
                
                <label for="admin_password">Password:</label>
                <input type="password" id="admin_password" name="admin_password" required>
                
                <label for="admin_role">Role:</label>
                <select id="admin_role" name="admin_role" required>
                    <option value="editor_home">Edit Home Page Only</option>
                    <option value="editor_about">Edit About Us Page Only</option>
                    <option value="editor_both">Edit Both Pages</option>
                    <option value="full_admin">Full Admin</option>
                </select>
                
                <button type="submit"><i class="fas fa-user-plus"></i> Add Admin</button>
            </form>

            <!-- Add New Email Recipient -->
            <h2>Add New Email Recipient</h2>
            <form action="add_email.php" method="POST">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <button type="submit"><i class="fas fa-envelope"></i> Add Email</button>
            </form>

            <!-- Bulk Actions for Admins -->
            <h2>Manage Admins</h2>
            <form id="bulkAdminForm" method="POST" action="bulk_admin_actions.php">
                <div>
                    <select name="bulk_action" required>
                        <option value="">--Select Action--</option>
                        <option value="delete">Delete Selected</option>
                        <option value="block">Block Selected</option>
                        <option value="unblock">Unblock Selected</option>
                    </select>
                    <button type="submit">Apply</button>
                </div>
                <table>
                    <tr>
                        <th><input type="checkbox" onclick="toggleSelectAll(this, 'admin_ids[]')"></th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                    <?php
                    $result = $conn->query("SELECT id, username, role, status FROM admins");
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><input type='checkbox' name='admin_ids[]' value='" . htmlspecialchars($row['id']) . "'></td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No admins found.</td></tr>";
                    }
                    ?>
                </table>
            </form>

            <!-- Bulk Actions for Email Recipients -->
            <h2>Email Recipients</h2>
            <form id="bulkEmailForm" method="POST" action="bulk_email_actions.php">
                <div>
                    <select name="bulk_action" required>
                        <option value="">--Select Action--</option>
                        <option value="delete">Delete Selected</option>
                        <option value="export">Export Selected</option>
                    </select>
                    <button type="submit">Apply</button>
                </div>
                <table>
                    <tr>
                        <th><input type="checkbox" onclick="toggleSelectAll(this, 'email_ids[]')"></th>
                        <th>Email</th>
                    </tr>
                    <?php
                    $result = $conn->query("SELECT id, email FROM email_notifications");
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><input type='checkbox' name='email_ids[]' value='" . htmlspecialchars($row['id']) . "'></td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>No email recipients found.</td></tr>";
                    }
                    ?>
                </table>
            </form>

        <?php endif; ?>

        <!-- Advanced Filtering and Search in Login Logs -->
        <h2>Login Logs</h2>

        <form method="GET" action="admin.php">
            <label for="filter_username">Username:</label>
            <input type="text" id="filter_username" name="filter_username" value="<?php echo isset($_GET['filter_username']) ? htmlspecialchars($_GET['filter_username']) : ''; ?>">

            <label for="filter_date_from">Date From:</label>
            <input type="date" id="filter_date_from" name="filter_date_from" value="<?php echo isset($_GET['filter_date_from']) ? htmlspecialchars($_GET['filter_date_from']) : ''; ?>">

            <label for="filter_date_to">Date To:</label>
            <input type="date" id="filter_date_to" name="filter_date_to" value="<?php echo isset($_GET['filter_date_to']) ? htmlspecialchars($_GET['filter_date_to']) : ''; ?>">

            <label for="filter_country">Country:</label>
            <input type="text" id="filter_country" name="filter_country" value="<?php echo isset($_GET['filter_country']) ? htmlspecialchars($_GET['filter_country']) : ''; ?>">

            <label for="filter_ip">IP Address:</label>
            <input type="text" id="filter_ip" name="filter_ip" value="<?php echo isset($_GET['filter_ip']) ? htmlspecialchars($_GET['filter_ip']) : ''; ?>">

            <label for="filter_status">Status:</label>
            <select id="filter_status" name="filter_status">
                <option value="">--All--</option>
                <option value="active" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="blocked" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'blocked') ? 'selected' : ''; ?>>Blocked</option>
                <option value="failed" <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
            </select>

            <button type="submit">Filter</button>
            <button type="button" onclick="clearFilteredLogs()"><i class="fas fa-trash-alt"></i> Clear Filtered Logs</button>
        </form>

        <table>
            <tr>
                <th>Username</th>
                <th>Device Type</th>
                <th>Operating System</th>
                <th>IP Address</th>
                <th>Country</th>
                <th>Timestamp</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            $query = "SELECT id, username, device_type, operating_system, ip_address, country, timestamp, status FROM logs WHERE 1=1";

            if (isset($_GET['filter_username']) && $_GET['filter_username'] != '') {
                $filter_username = $conn->real_escape_string($_GET['filter_username']);
                $query .= " AND username LIKE '%$filter_username%'";
            }

            if (isset($_GET['filter_date_from']) && $_GET['filter_date_from'] != '') {
                $filter_date_from = $conn->real_escape_string($_GET['filter_date_from']);
                $query .= " AND timestamp >= '$filter_date_from'";
            }

            if (isset($_GET['filter_date_to']) && $_GET['filter_date_to'] != '') {
                $filter_date_to = $conn->real_escape_string($_GET['filter_date_to']);
                $query .= " AND timestamp <= '$filter_date_to 23:59:59'";
            }

            if (isset($_GET['filter_country']) && $_GET['filter_country'] != '') {
                $filter_country = $conn->real_escape_string($_GET['filter_country']);
                $query .= " AND country LIKE '%$filter_country%'";
            }

            if (isset($_GET['filter_ip']) && $_GET['filter_ip'] != '') {
                $filter_ip = $conn->real_escape_string($_GET['filter_ip']);
                $query .= " AND ip_address LIKE '%$filter_ip%'";
            }

            if (isset($_GET['filter_status']) && $_GET['filter_status'] != '') {
                $filter_status = $conn->real_escape_string($_GET['filter_status']);
                $query .= " AND status = '$filter_status'";
            }

            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['device_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['operating_system']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['country']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>";
                    if ($row['status'] === 'active') {
                        echo "<form method='POST' action='block_user.php' style='display:inline-block;'>
                                <input type='hidden' name='username' value='" . htmlspecialchars($row['username']) . "'>
                                <button type='submit' class='block-button'><i class='fas fa-ban'></i> Block</button>
                              </form>";
                    } else if ($row['status'] === 'blocked') {
                        echo "<form method='POST' action='unblock_user.php' style='display:inline-block;'>
                                <input type='hidden' name='username' value='" . htmlspecialchars($row['username']) . "'>
                                <button type='submit' class='unblock-button'><i class='fas fa-check'></i> Unblock</button>
                              </form>";
                    } else {
                        echo "N/A";
                    }
                    echo "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No logs found.</td></tr>";
            }
            ?>
        </table>

        <button onclick="exportFilteredLogs()"><i class="fas fa-file-export"></i> Export Logs</button>

    </section>

    <footer>
        <p>Done by: Humaid AL-AAmri ID: 22F22683</p>
    </footer>

    <script>
        function clearFilteredLogs() {
            if (confirm('Are you sure you want to clear all filtered logs? This action cannot be undone.')) {
                var params = new URLSearchParams(window.location.search);
                fetch('clear_logs.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params.toString()
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    window.location.href = 'admin.php';
                });
            }
        }

        function exportFilteredLogs() {
            var params = new URLSearchParams(window.location.search);
            window.location.href = 'export_logs.php?' + params.toString();
        }

        function toggleSelectAll(source, checkboxName) {
            var checkboxes = document.getElementsByName(checkboxName);
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>

</body>
</html>

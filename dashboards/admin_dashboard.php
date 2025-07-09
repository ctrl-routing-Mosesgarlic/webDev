<?php
/**
 * Admin Dashboard
 * Main dashboard for administrators
 */

// Include session management
require_once '../session.php';
require_once '../connection.php';

// Check if user is logged in and has admin role
if (!isLoggedIn() || getSessionVar('role') !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Get user statistics
$users_query = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($conn, $users_query);
$total_users = 0;
if ($result) {
    $total_users = mysqli_fetch_assoc($result)['total'];
}

// Get new users today
$today_query = "SELECT COUNT(*) as today FROM users WHERE DATE(registration_date) = CURDATE()";
$result = mysqli_query($conn, $today_query);
$new_users_today = 0;
if ($result) {
    $new_users_today = mysqli_fetch_assoc($result)['today'];
}

// Get active sessions (this is a placeholder - in a real app you'd count from sessions table)
$active_sessions = 0;
$sessions_query = "SELECT COUNT(*) as active FROM users WHERE last_login >= NOW() - INTERVAL 1 DAY";
$result = mysqli_query($conn, $sessions_query);
if ($result) {
    $active_sessions = mysqli_fetch_assoc($result)['active'];
}

// Get recent user activity
$recent_activity_query = "SELECT u.fullname, u.email, u.registration_date 
                         FROM users u 
                         ORDER BY u.registration_date DESC LIMIT 5";
$recent_activity = mysqli_query($conn, $recent_activity_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - W&SM System</title>
    <link rel="stylesheet" href="../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>W&SM System</h2>
                <p>Administration</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="admin/user_management.php"><i class="fas fa-users"></i> User Management</a></li>
                    <li><a href="admin/system_settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
                    <li><a href="admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="admin/database.php"><i class="fas fa-database"></i> Database</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $new_users_today; ?></div>
                    <div class="stat-label">New Users Today</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $active_sessions; ?></div>
                    <div class="stat-label">Active Sessions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">0</div>
                    <div class="stat-label">System Alerts</div>
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="card">
                    <h2>Recent User Activity</h2>
                    <ul class="activity-list">
                        <?php if ($recent_activity && mysqli_num_rows($recent_activity) > 0): ?>
                            <?php while ($activity = mysqli_fetch_assoc($recent_activity)): ?>
                                <li>
                                    <div class="activity-icon"><i class="fas fa-user-plus"></i></div>
                                    <div class="activity-details">
                                        <div class="activity-title">New user registered</div>
                                        <div class="activity-info"><?php echo htmlspecialchars($activity['fullname']); ?> (<?php echo htmlspecialchars($activity['email']); ?>)</div>
                                        <div class="activity-time"><?php echo date('M d, Y H:i', strtotime($activity['registration_date'])); ?></div>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li>
                                <div class="activity-details">
                                    <div class="activity-title">No recent activity found</div>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="admin/user_management.php" style="text-decoration: none; color: #4a6fdc;">View All Users</a>
                    </div>
                </div>
                
                <div class="card">
                    <h2>System Information</h2>
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-label">PHP Version</div>
                            <div class="info-value"><?php echo phpversion(); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Server</div>
                            <div class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Database</div>
                            <div class="info-value">MySQL <?php 
                                $version_query = "SELECT VERSION() as version";
                                $version_result = mysqli_query($conn, $version_query);
                                if ($version_result) {
                                    echo mysqli_fetch_assoc($version_result)['version'];
                                } else {
                                    echo "Unknown";
                                }
                            ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Current Time</div>
                            <div class="info-value"><?php echo date('F d, Y H:i:s'); ?></div>
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="admin/system_settings.php" style="text-decoration: none; color: #4a6fdc;">System Settings</a> | 
                        <a href="admin/database.php" style="text-decoration: none; color: #4a6fdc;">Database Management</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../static/js/dashboard.js"></script>
</body>
</html>

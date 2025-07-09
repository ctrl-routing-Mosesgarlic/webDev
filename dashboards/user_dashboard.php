<?php
/**
 * Default User Dashboard
 * Dashboard for users with undefined or custom roles
 */

// Include session management
require_once '../session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page if not logged in
    header("Location: ../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');
$role = getSessionVar('role', 'user');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - W&SM System</title>
    <link rel="stylesheet" href="../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>W&SM System</h2>
                <p>User Portal</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-file-alt"></i> Documents</a></li>
                    <li><a href="#"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="#"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>User Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
                    <div class="stat-label">Account Type</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">3</div>
                    <div class="stat-label">Notifications</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">7</div>
                    <div class="stat-label">Documents</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Active</div>
                    <div class="stat-label">Account Status</div>
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="card">
                    <h2>Welcome to Your Dashboard</h2>
                    <p>Hello <?php echo htmlspecialchars($fullname); ?>, welcome to your personal dashboard in the W&SM System.</p>
                    <p>Your role is: <strong><?php echo htmlspecialchars(ucfirst($role)); ?></strong></p>
                    <p>Use the sidebar navigation to access different features of the system.</p>
                </div>
                
                <div class="card">
                    <h2>Recent Notifications</h2>
                    <ul class="notification-list">
                        <li>
                            <div class="notification-icon"><i class="fas fa-bell"></i></div>
                            <div class="notification-content">
                                <p>Your account has been successfully activated.</p>
                                <span class="notification-time">Today, 10:30 AM</span>
                            </div>
                        </li>
                        <li>
                            <div class="notification-icon"><i class="fas fa-file-alt"></i></div>
                            <div class="notification-content">
                                <p>A new document has been shared with you.</p>
                                <span class="notification-time">Yesterday, 3:45 PM</span>
                            </div>
                        </li>
                        <li>
                            <div class="notification-icon"><i class="fas fa-user-plus"></i></div>
                            <div class="notification-content">
                                <p>You have been added to a new project group.</p>
                                <span class="notification-time">Aug 15, 2023, 9:15 AM</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../static/js/dashboard.js"></script>
</body>
</html>

<?php
/**
 * Employee Dashboard
 * Dashboard for employees with standard access
 */

// Include session management
require_once '../session.php';

// Check if user is logged in and has employee role
if (!isLoggedIn() || getSessionVar('role') !== 'employee') {
    // Redirect to login page if not logged in or not an employee
    header("Location: ../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - W&SM System</title>
    <link rel="stylesheet" href="../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>W&SM System</h2>
                <p>Employee Portal</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-tasks"></i> My Tasks</a></li>
                    <li><a href="#"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                    <li><a href="#"><i class="fas fa-file-invoice"></i> Documents</a></li>
                    <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Employee Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-value">5</div>
                    <div class="stat-label">Tasks Assigned</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">3</div>
                    <div class="stat-label">Tasks Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">2</div>
                    <div class="stat-label">Tasks Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">90%</div>
                    <div class="stat-label">Performance</div>
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="card">
                    <h2>Today's Tasks</h2>
                    <ul class="task-list">
                        <li>
                            <input type="checkbox" id="task1">
                            <label for="task1">Complete project documentation</label>
                            <span class="task-priority high">High</span>
                        </li>
                        <li>
                            <input type="checkbox" id="task2">
                            <label for="task2">Attend team meeting at 2:00 PM</label>
                            <span class="task-priority medium">Medium</span>
                        </li>
                        <li>
                            <input type="checkbox" id="task3" checked>
                            <label for="task3">Submit weekly report</label>
                            <span class="task-priority completed">Completed</span>
                        </li>
                        <li>
                            <input type="checkbox" id="task4">
                            <label for="task4">Review client feedback</label>
                            <span class="task-priority low">Low</span>
                        </li>
                    </ul>
                </div>
                
                <div class="card">
                    <h2>Announcements</h2>
                    <div class="announcements">
                        <div class="announcement">
                            <h3>Office Closure Notice</h3>
                            <p>The office will be closed on August 30th for maintenance. All employees are requested to work remotely.</p>
                            <span class="announcement-date">Posted: Aug 15, 2023</span>
                        </div>
                        <div class="announcement">
                            <h3>New Project Kickoff</h3>
                            <p>Project Phoenix kickoff meeting scheduled for September 1st. All team members are required to attend.</p>
                            <span class="announcement-date">Posted: Aug 10, 2023</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../static/js/dashboard.js"></script>
</body>
</html>

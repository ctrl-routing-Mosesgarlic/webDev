<?php
/**
 * Warehouse Manager Dashboard
 * Dashboard for warehouse managers with inventory and staff management privileges
 */

// Include session management
require_once '../session.php';
require_once '../connection.php';

// Check if user is logged in and has warehouse_manager role
if (!isLoggedIn() || getSessionVar('role') !== 'warehouse_manager') {
    // Redirect to login page if not logged in or not a warehouse_manager
    header("Location: ../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Get staff count
$staff_query = "SELECT COUNT(*) as count FROM users WHERE role = 'stock_clerk'";
$staff_result = mysqli_query($conn, $staff_query);
$staff_count = 0;
if ($staff_result && mysqli_num_rows($staff_result) > 0) {
    $row = mysqli_fetch_assoc($staff_result);
    $staff_count = $row['count'];
}

// Get active tasks count (in a real app, this would be from a tasks table)
$active_tasks = 12;

// Get pending tasks count
$pending_tasks = 5;

// Get warehouse efficiency (simulated)
$efficiency = 87;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Manager Dashboard - W&SM System</title>
    <link rel="stylesheet" href="../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>W&SM System</h2>
                <p>Management</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="manager_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manager/staff_management.php"><i class="fas fa-users"></i> Staff Management</a></li>
                    <li><a href="manager/task_management.php"><i class="fas fa-tasks"></i> Task Management</a></li>
                    <li><a href="manager/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="manager/schedules.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Warehouse Manager Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $staff_count; ?></div>
                    <div class="stat-label">Staff Members</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $active_tasks; ?></div>
                    <div class="stat-label">Active Tasks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $pending_tasks; ?></div>
                    <div class="stat-label">Pending Tasks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $efficiency; ?>%</div>
                    <div class="stat-label">Warehouse Efficiency</div>
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="card">
                    <h2>Warehouse Overview</h2>
                    <p>Welcome <?php echo htmlspecialchars($fullname); ?> to the Warehouse Manager Dashboard. As a warehouse manager, you can oversee inventory, manage staff, assign tasks, and monitor warehouse operations.</p>
                    <p>Use the sidebar navigation to access different management functions:</p>
                    <ul>
                        <li><strong><a href="manager/staff_management.php">Staff Management</a></strong> - Manage warehouse staff and their assignments</li>
                        <li><strong><a href="manager/task_management.php">Task Management</a></strong> - Create and assign tasks to staff members</li>
                        <li><strong><a href="manager/reports.php">Reports</a></strong> - Generate and view warehouse performance reports</li>
                        <li><strong><a href="manager/schedules.php">Schedules</a></strong> - Manage staff schedules and shifts</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h2>Upcoming Tasks</h2>
                    <ul class="deadline-list">
                        <li>
                            <span class="deadline-project">Inventory Audit</span>
                            <span class="deadline-date"><?php echo date('M d, Y', strtotime('+2 days')); ?></span>
                            <span class="deadline-status on-track">On Track</span>
                        </li>
                        <li>
                            <span class="deadline-project">Supplier Delivery</span>
                            <span class="deadline-date"><?php echo date('M d, Y', strtotime('+3 days')); ?></span>
                            <span class="deadline-status at-risk">At Risk</span>
                        </li>
                        <li>
                            <span class="deadline-project">Monthly Stock Report</span>
                            <span class="deadline-date"><?php echo date('M d, Y', strtotime('+7 days')); ?></span>
                            <span class="deadline-status on-track">On Track</span>
                        </li>
                        <li>
                            <span class="deadline-project">Staff Training</span>
                            <span class="deadline-date"><?php echo date('M d, Y', strtotime('+10 days')); ?></span>
                            <span class="deadline-status not-started">Not Started</span>
                        </li>
                    </ul>
                </div>
                
                <div class="card">
                    <h2>Recent Activities</h2>
                    <ul class="deadline-list">
                        <?php
                        // In a real application, this would be fetched from a database
                        $activities = [
                            ['action' => 'Inventory updated', 'user' => 'John Doe', 'time' => '2 hours ago'],
                            ['action' => 'New task assigned', 'user' => 'Jane Smith', 'time' => '4 hours ago'],
                            ['action' => 'Schedule updated', 'user' => 'Mike Johnson', 'time' => 'Yesterday'],
                            ['action' => 'Report generated', 'user' => 'Sarah Williams', 'time' => 'Yesterday']
                        ];
                        
                        foreach ($activities as $activity) {
                            echo '<li>';
                            echo '<span class="deadline-project">' . htmlspecialchars($activity['action']) . '</span>';
                            echo '<span class="deadline-date">' . htmlspecialchars($activity['user']) . '</span>';
                            echo '<span class="deadline-status">' . htmlspecialchars($activity['time']) . '</span>';
                            echo '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Simple dashboard functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Warehouse Manager Dashboard loaded');
        });
    </script>
</body>
</html>

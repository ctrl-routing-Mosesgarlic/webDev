<?php
/**
 * Admin Reports
 * This page allows administrators to view and generate system reports
 */

// Include session management
require_once '../../session.php';
require_once '../../connection.php';

// Check if user is logged in and has admin role
if (!isLoggedIn() || getSessionVar('role') !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Handle report generation
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $report_type = $_POST['report_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    // In a real application, you would generate the actual report here
    $message = "Report generated successfully.";
    $message_type = "success";
}

// Sample data for demonstration
$user_stats = [
    'total_users' => 125,
    'active_users' => 98,
    'new_users_today' => 3,
    'new_users_week' => 15,
    'new_users_month' => 42
];

$role_distribution = [
    'admin' => 5,
    'manager' => 12,
    'employee' => 68,
    'customer' => 40
];

$monthly_registrations = [
    'Jan' => 8,
    'Feb' => 12,
    'Mar' => 15,
    'Apr' => 10,
    'May' => 18,
    'Jun' => 22,
    'Jul' => 14,
    'Aug' => 9,
    'Sep' => 11,
    'Oct' => 16,
    'Nov' => 20,
    'Dec' => 25
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .report-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .report-card h3 {
            margin-top: 0;
            color: #344767;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .report-filters {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-generate {
            padding: 8px 15px;
            background-color: #4a6fdc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .message.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 5px solid #4caf50;
        }
        .message.error {
            background-color: #ffebee;
            color: #b71c1c;
            border-left: 5px solid #f44336;
        }
        .export-btn {
            padding: 8px 15px;
            background-color: #2dce89;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-left: 10px;
        }
        .export-btn i {
            margin-right: 5px;
        }
    </style>
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
                    <li><a href="../admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="user_management.php"><i class="fas fa-users"></i> User Management</a></li>
                    <li><a href="system_settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
                    <li class="active"><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="database.php"><i class="fas fa-database"></i> Database</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Reports</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="report-filters">
                <h3>Generate Report</h3>
                <form class="filter-form" method="POST">
                    <div class="form-group">
                        <label for="report_type">Report Type:</label>
                        <select id="report_type" name="report_type" required>
                            <option value="user_registration">User Registration</option>
                            <option value="user_activity">User Activity</option>
                            <option value="system_usage">System Usage</option>
                            <option value="role_distribution">Role Distribution</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="generate_report" class="btn-generate">Generate Report</button>
                        <a href="#" class="export-btn" onclick="exportReport()"><i class="fas fa-file-export"></i> Export</a>
                    </div>
                </form>
            </div>
            
            <div class="report-container">
                <div class="report-card">
                    <h3>User Registration Trends</h3>
                    <div class="chart-container">
                        <canvas id="registrationChart"></canvas>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>Role Distribution</h3>
                    <div class="chart-container">
                        <canvas id="roleChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="report-card">
                <h3>User Statistics</h3>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total Users</td>
                            <td><?php echo $user_stats['total_users']; ?></td>
                        </tr>
                        <tr>
                            <td>Active Users</td>
                            <td><?php echo $user_stats['active_users']; ?></td>
                        </tr>
                        <tr>
                            <td>New Users Today</td>
                            <td><?php echo $user_stats['new_users_today']; ?></td>
                        </tr>
                        <tr>
                            <td>New Users This Week</td>
                            <td><?php echo $user_stats['new_users_week']; ?></td>
                        </tr>
                        <tr>
                            <td>New Users This Month</td>
                            <td><?php echo $user_stats['new_users_month']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script>
        // Initialize charts when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Registration chart
            const registrationCtx = document.getElementById('registrationChart').getContext('2d');
            const registrationChart = new Chart(registrationCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_keys($monthly_registrations)); ?>,
                    datasets: [{
                        label: 'New Users',
                        data: <?php echo json_encode(array_values($monthly_registrations)); ?>,
                        backgroundColor: 'rgba(74, 111, 220, 0.2)',
                        borderColor: 'rgba(74, 111, 220, 1)',
                        borderWidth: 2,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Role distribution chart
            const roleCtx = document.getElementById('roleChart').getContext('2d');
            const roleChart = new Chart(roleCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_keys($role_distribution)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($role_distribution)); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
        
        // Set default dates for the report filters
        window.onload = function() {
            const today = new Date();
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(today.getDate() - 30);
            
            document.getElementById('end_date').valueAsDate = today;
            document.getElementById('start_date').valueAsDate = thirtyDaysAgo;
        };
        
        // Export report function
        function exportReport() {
            const reportType = document.getElementById('report_type').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (!reportType || !startDate || !endDate) {
                alert('Please select all report parameters before exporting.');
                return;
            }
            
            alert(`This would export the ${reportType} report from ${startDate} to ${endDate} as a PDF or Excel file. Feature not implemented in this demo.`);
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

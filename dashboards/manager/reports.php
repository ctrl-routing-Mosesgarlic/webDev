<?php
/**
 * Manager Reports
 * This page allows managers to generate and view various reports
 */

// Include session management
require_once '../../session.php';
require_once '../../connection.php';

// Check if user is logged in and has manager role
if (!isLoggedIn() || getSessionVar('role') !== 'manager') {
    // Redirect to login page if not logged in or not a manager
    header("Location: ../../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Handle report generation (if any)
$message = '';
$message_type = '';
$show_report = false;
$report_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_report'])) {
        $report_type = mysqli_real_escape_string($conn, $_POST['report_type']);
        $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
        $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
        
        // In a real application, you would generate the report based on the selected parameters
        $message = "Report generated successfully.";
        $message_type = "success";
        $show_report = true;
    }
}

// Get employee count for charts
$employee_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'employee'";
$employee_result = mysqli_query($conn, $employee_count_query);
$employee_count = 0;
if ($employee_result && mysqli_num_rows($employee_result) > 0) {
    $row = mysqli_fetch_assoc($employee_result);
    $employee_count = $row['count'];
}

// Sample data for charts (in a real application, this would come from database queries)
$monthly_tasks = [12, 19, 15, 25, 22, 30, 28, 25, 30, 35, 40, 38];
$task_status = [
    'pending' => 15,
    'in_progress' => 22,
    'review' => 8,
    'completed' => 45,
    'cancelled' => 5
];
$employee_performance = [
    ['name' => 'John Doe', 'completed' => 15, 'pending' => 3],
    ['name' => 'Jane Smith', 'completed' => 12, 'pending' => 5],
    ['name' => 'Mike Johnson', 'completed' => 18, 'pending' => 2],
    ['name' => 'Sarah Williams', 'completed' => 10, 'pending' => 7],
    ['name' => 'David Brown', 'completed' => 14, 'pending' => 4]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Manager Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #344767;
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-generate {
            background-color: #4a6fdc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
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
        .chart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .chart-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex: 1;
            min-width: 300px;
        }
        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #344767;
            margin-bottom: 15px;
            text-align: center;
        }
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex: 1;
            min-width: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #4a6fdc;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 14px;
            color: #8898aa;
            text-align: center;
        }
        .stat-icon {
            font-size: 24px;
            color: #4a6fdc;
        }
        .performance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .performance-table th, .performance-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .performance-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #344767;
        }
        .performance-table tr:hover {
            background-color: #f8f9fa;
        }
        .progress-bar {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }
        .progress-fill {
            height: 100%;
            background-color: #4a6fdc;
        }
    </style>
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
                    <li><a href="../manager_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="staff_management.php"><i class="fas fa-users"></i> Staff Management</a></li>
                    <li><a href="task_management.php"><i class="fas fa-tasks"></i> Task Management</a></li>
                    <li class="active"><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="schedules.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
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
            
            <div class="report-form">
                <h2>Generate Report</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="report_type">Report Type:</label>
                            <select id="report_type" name="report_type" required>
                                <option value="task_summary">Task Summary</option>
                                <option value="employee_performance">Employee Performance</option>
                                <option value="task_completion">Task Completion Rate</option>
                                <option value="time_tracking">Time Tracking</option>
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
                    </div>
                    <button type="submit" name="generate_report" class="btn-generate">Generate Report</button>
                </form>
            </div>
            
            <div class="stats-container">
                <div class="stat-card">
                    <i class="fas fa-users stat-icon"></i>
                    <div class="stat-value"><?php echo $employee_count; ?></div>
                    <div class="stat-label">Total Employees</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-tasks stat-icon"></i>
                    <div class="stat-value"><?php echo array_sum($task_status); ?></div>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle stat-icon"></i>
                    <div class="stat-value"><?php echo $task_status['completed']; ?></div>
                    <div class="stat-label">Completed Tasks</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock stat-icon"></i>
                    <div class="stat-value"><?php echo round(($task_status['completed'] / array_sum($task_status)) * 100); ?>%</div>
                    <div class="stat-label">Completion Rate</div>
                </div>
            </div>
            
            <div class="chart-container">
                <div class="chart-card">
                    <div class="chart-title">Monthly Task Distribution</div>
                    <canvas id="monthlyTasksChart"></canvas>
                </div>
                <div class="chart-card">
                    <div class="chart-title">Task Status Distribution</div>
                    <canvas id="taskStatusChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card" style="margin-top: 20px;">
                <div class="chart-title">Employee Performance</div>
                <table class="performance-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Completed Tasks</th>
                            <th>Pending Tasks</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employee_performance as $employee): ?>
                            <?php 
                                $total_tasks = $employee['completed'] + $employee['pending'];
                                $completion_rate = $total_tasks > 0 ? round(($employee['completed'] / $total_tasks) * 100) : 0;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                <td><?php echo $employee['completed']; ?></td>
                                <td><?php echo $employee['pending']; ?></td>
                                <td>
                                    <div><?php echo $completion_rate; ?>%</div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $completion_rate; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script>
        // Set default dates for the date inputs
        window.onload = function() {
            // Set default start date to first day of current month
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            document.getElementById('start_date').valueAsDate = firstDay;
            
            // Set default end date to today
            document.getElementById('end_date').valueAsDate = today;
            
            // Initialize charts
            initializeCharts();
        };
        
        function initializeCharts() {
            // Monthly Tasks Chart
            const monthlyTasksCtx = document.getElementById('monthlyTasksChart').getContext('2d');
            new Chart(monthlyTasksCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Tasks',
                        data: <?php echo json_encode($monthly_tasks); ?>,
                        borderColor: '#4a6fdc',
                        backgroundColor: 'rgba(74, 111, 220, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            // Task Status Chart
            const taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
            new Chart(taskStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'In Progress', 'Under Review', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: [
                            <?php echo $task_status['pending']; ?>,
                            <?php echo $task_status['in_progress']; ?>,
                            <?php echo $task_status['review']; ?>,
                            <?php echo $task_status['completed']; ?>,
                            <?php echo $task_status['cancelled']; ?>
                        ],
                        backgroundColor: [
                            '#f5b74f', // Pending
                            '#5a8dee', // In Progress
                            '#a66dd4', // Under Review
                            '#2dce89', // Completed
                            '#f5365c'  // Cancelled
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '60%'
                }
            });
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

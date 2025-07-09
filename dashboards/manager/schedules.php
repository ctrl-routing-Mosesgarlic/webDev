<?php
/**
 * Manager Schedules
 * This page allows managers to manage employee schedules and shifts
 */

// Include session management
require_once '../../session.php';
require_once '../../connection.php';

// Check if user is logged in and has manager role
if (!isLoggedIn() || getSessionVar('role') !== 'warehouse_manager') {
    // Redirect to login page if not logged in or not a manager
    header("Location: ../../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Handle schedule actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Process form submissions based on action
        $message = "Schedule updated successfully.";
        $message_type = "success";
    }
}

// Sample schedule data (in a real app, this would come from database)
$schedules = [
    [
        'id' => 1,
        'employee' => 'John Doe',
        'shift' => 'Morning (8AM-4PM)',
        'date' => '2025-06-27',
        'status' => 'confirmed'
    ],
    [
        'id' => 2,
        'employee' => 'Jane Smith',
        'shift' => 'Evening (4PM-12AM)',
        'date' => '2025-06-27',
        'status' => 'pending'
    ],
    [
        'id' => 3,
        'employee' => 'Mike Johnson',
        'shift' => 'Morning (8AM-4PM)',
        'date' => '2025-06-28',
        'status' => 'confirmed'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedules - Manager Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }
        .schedule-table th, .schedule-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .schedule-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .schedule-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #4a6fdc;
            color: white;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            display: inline-block;
        }
        .status-confirmed {
            background-color: #2dce89;
        }
        .status-pending {
            background-color: #f5b74f;
        }
        .calendar-view {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        .calendar-day {
            border: 1px solid #e9ecef;
            padding: 10px;
            min-height: 100px;
            background-color: #fff;
        }
        .calendar-day-header {
            font-weight: 600;
            margin-bottom: 10px;
            text-align: center;
        }
        .calendar-day-content {
            font-size: 0.85rem;
        }
        .calendar-event {
            background-color: #e8f0fe;
            border-left: 3px solid #4a6fdc;
            padding: 5px;
            margin-bottom: 5px;
            font-size: 0.8rem;
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
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li class="active"><a href="schedules.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Schedules</h1>
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
            
            <div class="card">
                <div class="schedule-controls">
                    <div>
                        <h3>Staff Schedule - June 2025</h3>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="openAddScheduleModal()">
                            <i class="fas fa-plus"></i> Add Schedule
                        </button>
                    </div>
                </div>
                
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Shift</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($schedule['employee']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['shift']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($schedule['date']))); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $schedule['status']; ?>">
                                        <?php echo ucfirst($schedule['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-primary" onclick="editSchedule(<?php echo $schedule['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h3>Calendar View</h3>
                <div class="calendar-view">
                    <?php
                    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    foreach ($days as $day) {
                        echo '<div class="calendar-day">';
                        echo '<div class="calendar-day-header">' . $day . '</div>';
                        echo '<div class="calendar-day-content">';
                        
                        // Add sample events for demonstration
                        if ($day === 'Mon') {
                            echo '<div class="calendar-event">John - Morning</div>';
                            echo '<div class="calendar-event">Jane - Evening</div>';
                        } elseif ($day === 'Tue') {
                            echo '<div class="calendar-event">Mike - Morning</div>';
                        } elseif ($day === 'Wed') {
                            echo '<div class="calendar-event">John - Evening</div>';
                        } elseif ($day === 'Thu') {
                            echo '<div class="calendar-event">Jane - Morning</div>';
                        } elseif ($day === 'Fri') {
                            echo '<div class="calendar-event">Mike - Evening</div>';
                        }
                        
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function openAddScheduleModal() {
            alert("Add Schedule functionality would open a modal here");
            // In a real application, this would open a modal to add a new schedule
        }
        
        function editSchedule(id) {
            alert("Edit Schedule functionality for ID: " + id);
            // In a real application, this would open a modal to edit the schedule
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

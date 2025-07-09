<?php
/**
 * Manager Staff Management
 * This page allows managers to manage staff/employees
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

// Handle staff actions (if any)
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'assign_task':
                if (isset($_POST['employee_id']) && isset($_POST['task_description'])) {
                    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
                    $task_description = mysqli_real_escape_string($conn, $_POST['task_description']);
                    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
                    
                    // In a real application, you would insert this into a tasks table
                    $message = "Task assigned successfully.";
                    $message_type = "success";
                }
                break;
                
            case 'update_status':
                if (isset($_POST['employee_id']) && isset($_POST['status'])) {
                    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
                    $status = mysqli_real_escape_string($conn, $_POST['status']);
                    
                    // In a real application, you would update the employee status in the database
                    $message = "Employee status updated successfully.";
                    $message_type = "success";
                }
                break;
        }
    }
}

// Fetch all employees
$sql = "SELECT id, fullname, email, gender, registration_date FROM users WHERE role = 'employee' ORDER BY fullname ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Manager Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .staff-table {
            width: 100%;
            border-collapse: collapse;
        }
        .staff-table th, .staff-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .staff-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #344767;
        }
        .staff-table tr:hover {
            background-color: #f8f9fa;
        }
        .action-buttons a, .action-buttons button {
            padding: 6px 10px;
            margin-right: 5px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            cursor: pointer;
            border: none;
        }
        .assign-btn {
            background-color: #5a8dee;
            color: white;
        }
        .status-btn {
            background-color: #2dce89;
            color: white;
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
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            max-width: 500px;
        }
        .close-btn {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group select, .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-container {
            text-align: right;
            margin-top: 20px;
        }
        .btn-container button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #4a6fdc;
            color: white;
        }
        .btn-secondary {
            background-color: #8898aa;
            color: white;
            margin-right: 10px;
        }
        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-box {
            display: flex;
            align-items: center;
        }
        .search-box input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        .search-box button {
            padding: 8px 15px;
            background-color: #4a6fdc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
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
                    <li class="active"><a href="staff_management.php"><i class="fas fa-users"></i> Staff Management</a></li>
                    <li><a href="task_management.php"><i class="fas fa-tasks"></i> Task Management</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="schedules.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Staff Management</h1>
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
                <div class="filter-container">
                    <form class="search-box" method="GET">
                        <input type="text" name="search" placeholder="Search by name or email" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
                
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Gender</th>
                            <th>Join Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars(ucfirst($row['gender'])) . "</td>";
                                echo "<td>" . htmlspecialchars(date('M d, Y', strtotime($row['registration_date']))) . "</td>";
                                echo "<td class='action-buttons'>";
                                echo "<a href='#' class='assign-btn' onclick='openAssignModal(" . $row['id'] . ", \"" . $row['fullname'] . "\")'><i class='fas fa-tasks'></i> Assign Task</a>";
                                echo "<a href='#' class='status-btn' onclick='openStatusModal(" . $row['id'] . ", \"" . $row['fullname'] . "\")'><i class='fas fa-user-edit'></i> Update Status</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align: center;'>No employees found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Assign Task Modal -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAssignModal()">&times;</span>
            <h2>Assign Task to <span id="employee_name"></span></h2>
            <form id="assignForm" method="POST">
                <input type="hidden" name="action" value="assign_task">
                <input type="hidden" id="employee_id" name="employee_id" value="">
                
                <div class="form-group">
                    <label for="task_description">Task Description:</label>
                    <textarea id="task_description" name="task_description" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="due_date">Due Date:</label>
                    <input type="date" id="due_date" name="due_date" required>
                </div>
                
                <div class="form-group">
                    <label for="priority">Priority:</label>
                    <select id="priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                
                <div class="btn-container">
                    <button type="button" class="btn-secondary" onclick="closeAssignModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Update Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeStatusModal()">&times;</span>
            <h2>Update Status for <span id="status_employee_name"></span></h2>
            <form id="statusForm" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" id="status_employee_id" name="employee_id" value="">
                
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="active">Active</option>
                        <option value="on_leave">On Leave</option>
                        <option value="sick_leave">Sick Leave</option>
                        <option value="vacation">Vacation</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status_note">Note:</label>
                    <textarea id="status_note" name="status_note" rows="3"></textarea>
                </div>
                
                <div class="btn-container">
                    <button type="button" class="btn-secondary" onclick="closeStatusModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Set default date for due date field
        window.onload = function() {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('due_date').valueAsDate = tomorrow;
        };
        
        // Assign task modal functions
        function openAssignModal(employeeId, employeeName) {
            document.getElementById('employee_id').value = employeeId;
            document.getElementById('employee_name').textContent = employeeName;
            document.getElementById('assignModal').style.display = 'block';
        }
        
        function closeAssignModal() {
            document.getElementById('assignModal').style.display = 'none';
        }
        
        // Update status modal functions
        function openStatusModal(employeeId, employeeName) {
            document.getElementById('status_employee_id').value = employeeId;
            document.getElementById('status_employee_name').textContent = employeeName;
            document.getElementById('statusModal').style.display = 'block';
        }
        
        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('assignModal')) {
                closeAssignModal();
            }
            if (event.target == document.getElementById('statusModal')) {
                closeStatusModal();
            }
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

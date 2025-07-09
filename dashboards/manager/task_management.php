<?php
/**
 * Manager Task Management
 * This page allows managers to manage tasks assigned to employees
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

// Handle task actions (if any)
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_task':
                // In a real application, you would insert this into a tasks table
                $message = "Task created successfully.";
                $message_type = "success";
                break;
                
            case 'update_task':
                // In a real application, you would update the task in the database
                $message = "Task updated successfully.";
                $message_type = "success";
                break;
                
            case 'delete_task':
                // In a real application, you would delete the task from the database
                $message = "Task deleted successfully.";
                $message_type = "success";
                break;
        }
    }
}

// Fetch all employees for the dropdown
$employees_query = "SELECT id, fullname FROM users WHERE role = 'employee' ORDER BY fullname ASC";
$employees_result = mysqli_query($conn, $employees_query);

// Sample task data (in a real application, this would come from a database)
$tasks = [
    [
        'id' => 1,
        'title' => 'Complete monthly report',
        'description' => 'Prepare and submit the monthly sales report',
        'assigned_to' => 'John Doe',
        'due_date' => '2025-07-05',
        'priority' => 'high',
        'status' => 'in_progress'
    ],
    [
        'id' => 2,
        'title' => 'Client meeting preparation',
        'description' => 'Prepare presentation and materials for the client meeting',
        'assigned_to' => 'Jane Smith',
        'due_date' => '2025-07-02',
        'priority' => 'urgent',
        'status' => 'pending'
    ],
    [
        'id' => 3,
        'title' => 'Update inventory database',
        'description' => 'Update the inventory database with new products',
        'assigned_to' => 'Mike Johnson',
        'due_date' => '2025-07-10',
        'priority' => 'medium',
        'status' => 'completed'
    ]
];

// Status and priority labels and colors
$status_labels = [
    'pending' => 'Pending',
    'in_progress' => 'In Progress',
    'review' => 'Under Review',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
];

$status_colors = [
    'pending' => '#f5b74f',
    'in_progress' => '#5a8dee',
    'review' => '#a66dd4',
    'completed' => '#2dce89',
    'cancelled' => '#f5365c'
];

$priority_labels = [
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High',
    'urgent' => 'Urgent'
];

$priority_colors = [
    'low' => '#8898aa',
    'medium' => '#5a8dee',
    'high' => '#f5b74f',
    'urgent' => '#f5365c'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management - Manager Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .task-table {
            width: 100%;
            border-collapse: collapse;
        }
        .task-table th, .task-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .task-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #344767;
        }
        .task-table tr:hover {
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
        .edit-btn {
            background-color: #5a8dee;
            color: white;
        }
        .delete-btn {
            background-color: #f5365c;
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
        .add-task-btn {
            padding: 8px 15px;
            background-color: #2dce89;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .add-task-btn i {
            margin-right: 5px;
        }
        .status-badge, .priority-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            display: inline-block;
        }
        .task-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: #8898aa;
            position: relative;
        }
        .tab-btn.active {
            color: #4a6fdc;
        }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
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
                    <li class="active"><a href="task_management.php"><i class="fas fa-tasks"></i> Task Management</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="schedules.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Task Management</h1>
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
            
            <div class="task-tabs">
                <button class="tab-btn active" onclick="filterTasks('all')">All Tasks</button>
                <button class="tab-btn" onclick="filterTasks('pending')">Pending</button>
                <button class="tab-btn" onclick="filterTasks('in_progress')">In Progress</button>
                <button class="tab-btn" onclick="filterTasks('completed')">Completed</button>
            </div>
            
            <div class="card">
                <div class="filter-container">
                    <form class="search-box" method="GET">
                        <input type="text" name="search" placeholder="Search tasks" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                    <a href="#" class="add-task-btn" onclick="openTaskModal()"><i class="fas fa-plus"></i> New Task</a>
                </div>
                
                <table class="task-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Assigned To</th>
                            <th>Due Date</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr class="task-row" data-status="<?php echo $task['status']; ?>">
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td><?php echo htmlspecialchars($task['assigned_to']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($task['due_date']))); ?></td>
                                <td>
                                    <span class="priority-badge" style="background-color: <?php echo $priority_colors[$task['priority']]; ?>">
                                        <?php echo $priority_labels[$task['priority']]; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge" style="background-color: <?php echo $status_colors[$task['status']]; ?>">
                                        <?php echo $status_labels[$task['status']]; ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="#" class="edit-btn" onclick="openEditModal(<?php echo $task['id']; ?>)"><i class="fas fa-edit"></i> Edit</a>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $task['id']; ?>)"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Create/Edit Task Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeTaskModal()">&times;</span>
            <h2 id="modalTitle">Create New Task</h2>
            <form id="taskForm" method="POST">
                <input type="hidden" name="action" value="create_task">
                <input type="hidden" id="task_id" name="task_id" value="">
                
                <div class="form-group">
                    <label for="title">Task Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="assigned_to">Assign To:</label>
                    <select id="assigned_to" name="assigned_to" required>
                        <option value="">Select Employee</option>
                        <?php 
                        if ($employees_result && mysqli_num_rows($employees_result) > 0) {
                            while ($employee = mysqli_fetch_assoc($employees_result)) {
                                echo "<option value='" . $employee['id'] . "'>" . htmlspecialchars($employee['fullname']) . "</option>";
                            }
                        }
                        ?>
                    </select>
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
                
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status">
                        <option value="pending" selected>Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Under Review</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="btn-container">
                    <button type="button" class="btn-secondary" onclick="closeTaskModal()">Cancel</button>
                    <button type="submit" class="btn-primary" id="submitBtn">Create Task</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Form (Hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_task">
        <input type="hidden" id="delete_task_id" name="task_id" value="">
    </form>
    
    <script>
        // Set default date for due date field
        window.onload = function() {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('due_date').valueAsDate = tomorrow;
        };
        
        // Task modal functions
        function openTaskModal() {
            document.getElementById('modalTitle').textContent = 'Create New Task';
            document.getElementById('taskForm').reset();
            document.getElementById('taskForm').action.value = 'create_task';
            document.getElementById('submitBtn').textContent = 'Create Task';
            document.getElementById('taskModal').style.display = 'block';
        }
        
        function openEditModal(taskId) {
            document.getElementById('modalTitle').textContent = 'Edit Task';
            document.getElementById('task_id').value = taskId;
            document.getElementById('taskForm').action.value = 'update_task';
            document.getElementById('submitBtn').textContent = 'Update Task';
            
            // In a real application, you would fetch the task data from the server
            // For now, we'll just show the modal
            document.getElementById('taskModal').style.display = 'block';
        }
        
        function closeTaskModal() {
            document.getElementById('taskModal').style.display = 'none';
        }
        
        // Delete confirmation
        function confirmDelete(taskId) {
            if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
                document.getElementById('delete_task_id').value = taskId;
                document.getElementById('deleteForm').submit();
            }
        }
        
        // Filter tasks by status
        function filterTasks(status) {
            const rows = document.querySelectorAll('.task-row');
            
            rows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update active tab
            const tabs = document.querySelectorAll('.tab-btn');
            tabs.forEach(tab => tab.classList.remove('active'));
            event.currentTarget.classList.add('active');
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('taskModal')) {
                closeTaskModal();
            }
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

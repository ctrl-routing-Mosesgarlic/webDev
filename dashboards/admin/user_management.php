<?php
/**
 * Admin User Management
 * This page allows administrators to manage users in the system
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

// Handle user actions (if any)
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                if (isset($_POST['user_id'])) {
                    $delete_id = mysqli_real_escape_string($conn, $_POST['user_id']);
                    $delete_sql = "DELETE FROM users WHERE id = '$delete_id'";
                    if (mysqli_query($conn, $delete_sql)) {
                        $message = "User deleted successfully.";
                        $message_type = "success";
                    } else {
                        $message = "Error deleting user: " . mysqli_error($conn);
                        $message_type = "error";
                    }
                }
                break;
                
            case 'update_role':
                if (isset($_POST['user_id']) && isset($_POST['role'])) {
                    $update_id = mysqli_real_escape_string($conn, $_POST['user_id']);
                    $new_role = mysqli_real_escape_string($conn, $_POST['role']);
                    $update_sql = "UPDATE users SET role = '$new_role' WHERE id = '$update_id'";
                    if (mysqli_query($conn, $update_sql)) {
                        $message = "User role updated successfully.";
                        $message_type = "success";
                    } else {
                        $message = "Error updating user role: " . mysqli_error($conn);
                        $message_type = "error";
                    }
                }
                break;
        }
    }
}

// Fetch all users
$sql = "SELECT id, fullname, email, gender, role, registration_date FROM users ORDER BY registration_date DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-table th, .user-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .user-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #344767;
        }
        .user-table tr:hover {
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
        .form-group select, .form-group input {
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
        .add-user-btn {
            padding: 8px 15px;
            background-color: #2dce89;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .add-user-btn i {
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
                    <li class="active"><a href="user_management.php"><i class="fas fa-users"></i> User Management</a></li>
                    <li><a href="system_settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="database.php"><i class="fas fa-database"></i> Database</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>User Management</h1>
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
                    <a href="#" class="add-user-btn" onclick="openAddUserModal()"><i class="fas fa-user-plus"></i> Add New User</a>
                </div>
                
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Gender</th>
                            <th>Role</th>
                            <th>Registration Date</th>
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
                                echo "<td>" . htmlspecialchars(ucfirst($row['role'])) . "</td>";
                                echo "<td>" . htmlspecialchars(date('M d, Y', strtotime($row['registration_date']))) . "</td>";
                                echo "<td class='action-buttons'>";
                                echo "<a href='#' class='edit-btn' onclick='openEditModal(" . $row['id'] . ", \"" . $row['role'] . "\")'><i class='fas fa-edit'></i> Edit</a>";
                                echo "<button class='delete-btn' onclick='confirmDelete(" . $row['id'] . ")'><i class='fas fa-trash'></i> Delete</button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align: center;'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeEditModal()">&times;</span>
            <h2>Edit User Role</h2>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" id="edit_user_id" name="user_id" value="">
                
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="employee">Employee</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                
                <div class="btn-container">
                    <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAddUserModal()">&times;</span>
            <h2>Add New User</h2>
            <form action="../../process_register.php" method="POST">
                <div class="form-group">
                    <label for="fullname">Full Name:</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label>Gender:</label>
                    <div style="display: flex; gap: 15px;">
                        <label style="display: inline-flex; align-items: center;">
                            <input type="radio" name="gender" value="male" style="width: auto; margin-right: 5px;"> Male
                        </label>
                        <label style="display: inline-flex; align-items: center;">
                            <input type="radio" name="gender" value="female" style="width: auto; margin-right: 5px;"> Female
                        </label>
                        <label style="display: inline-flex; align-items: center;">
                            <input type="radio" name="gender" value="other" style="width: auto; margin-right: 5px;"> Other
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="add_role">Role:</label>
                    <select id="add_role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="employee">Employee</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                
                <div class="btn-container">
                    <button type="button" class="btn-secondary" onclick="closeAddUserModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Form (Hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" id="delete_user_id" name="user_id" value="">
    </form>
    
    <script>
        // Edit modal functions
        function openEditModal(userId, role) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('role').value = role;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Add user modal functions
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
        }
        
        function closeAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }
        
        // Delete confirmation
        function confirmDelete(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                document.getElementById('delete_user_id').value = userId;
                document.getElementById('deleteForm').submit();
            }
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
            if (event.target == document.getElementById('addUserModal')) {
                closeAddUserModal();
            }
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

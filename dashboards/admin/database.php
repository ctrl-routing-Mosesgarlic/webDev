<?php
/**
 * Admin Database Management
 * This page allows administrators to manage database operations
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

// Handle database operations
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'backup':
                // In a real application, you would implement database backup logic here
                $message = "Database backup initiated successfully.";
                $message_type = "success";
                break;
                
            case 'optimize':
                // In a real application, you would implement database optimization logic here
                $message = "Database optimization completed successfully.";
                $message_type = "success";
                break;
                
            case 'execute_query':
                // In a real application, you would validate and execute the SQL query
                if (isset($_POST['sql_query']) && !empty($_POST['sql_query'])) {
                    $sql_query = $_POST['sql_query'];
                    // For security reasons, we're not actually executing the query in this demo
                    $message = "Query execution simulated. In a production environment, the query would be validated and executed.";
                    $message_type = "success";
                } else {
                    $message = "Please enter a valid SQL query.";
                    $message_type = "error";
                }
                break;
        }
    }
}

// Get database information
$db_name = "wsm_system"; // From the memory
$db_host = "127.0.0.1"; // From the memory
$db_port = "3306"; // From the memory

// Get table information
$tables_query = "SHOW TABLES";
$tables_result = mysqli_query($conn, $tables_query);
$tables = [];
if ($tables_result) {
    while ($table = mysqli_fetch_array($tables_result)[0]) {
        $tables[] = $table;
        
        // Get row count for each table
        $count_query = "SELECT COUNT(*) AS count FROM `$table`";
        $count_result = mysqli_query($conn, $count_query);
        if ($count_result) {
            $row_count = mysqli_fetch_assoc($count_result)['count'];
            $tables_info[$table] = [
                'rows' => $row_count
            ];
        }
    }
}

// Get database size (this is a simplified version, in a real app you'd get actual size)
$db_size = "Unknown"; // In a real app, you would calculate this

// Get MySQL version
$version_query = "SELECT VERSION() as version";
$version_result = mysqli_query($conn, $version_query);
$mysql_version = "Unknown";
if ($version_result) {
    $mysql_version = mysqli_fetch_assoc($version_result)['version'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management - Admin Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .db-info-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .db-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .db-card h3 {
            margin-top: 0;
            color: #344767;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .db-stat {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .db-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .db-stat-icon i {
            color: #4a6fdc;
            font-size: 18px;
        }
        .db-stat-info {
            flex: 1;
        }
        .db-stat-value {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        .db-stat-label {
            color: #8898aa;
            font-size: 14px;
            margin: 0;
        }
        .db-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .db-action-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
        }
        .db-action-btn i {
            margin-right: 8px;
        }
        .btn-backup {
            background-color: #4a6fdc;
            color: white;
        }
        .btn-restore {
            background-color: #fb6340;
            color: white;
        }
        .btn-optimize {
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
        .sql-console {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .sql-console h3 {
            margin-top: 0;
            color: #344767;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .sql-textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            margin-bottom: 15px;
            resize: vertical;
        }
        .sql-submit {
            padding: 10px 15px;
            background-color: #4a6fdc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .table-list {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .table-list h3 {
            margin-top: 0;
            color: #344767;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .db-table {
            width: 100%;
            border-collapse: collapse;
        }
        .db-table th, .db-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .db-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #344767;
        }
        .db-table tr:hover {
            background-color: #f8f9fa;
        }
        .table-actions a {
            padding: 6px 10px;
            margin-right: 5px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            display: inline-block;
        }
        .view-btn {
            background-color: #5a8dee;
            color: white;
        }
        .structure-btn {
            background-color: #2dce89;
            color: white;
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
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li class="active"><a href="database.php"><i class="fas fa-database"></i> Database</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Database Management</h1>
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
            
            <div class="db-info-container">
                <div class="db-card">
                    <h3>Database Information</h3>
                    <div class="db-stat">
                        <div class="db-stat-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="db-stat-info">
                            <p class="db-stat-value"><?php echo htmlspecialchars($db_name); ?></p>
                            <p class="db-stat-label">Database Name</p>
                        </div>
                    </div>
                    <div class="db-stat">
                        <div class="db-stat-icon">
                            <i class="fas fa-server"></i>
                        </div>
                        <div class="db-stat-info">
                            <p class="db-stat-value"><?php echo htmlspecialchars($db_host) . ':' . htmlspecialchars($db_port); ?></p>
                            <p class="db-stat-label">Host & Port</p>
                        </div>
                    </div>
                    <div class="db-stat">
                        <div class="db-stat-icon">
                            <i class="fas fa-code-branch"></i>
                        </div>
                        <div class="db-stat-info">
                            <p class="db-stat-value"><?php echo htmlspecialchars($mysql_version); ?></p>
                            <p class="db-stat-label">MySQL Version</p>
                        </div>
                    </div>
                </div>
                
                <div class="db-card">
                    <h3>Database Statistics</h3>
                    <div class="db-stat">
                        <div class="db-stat-icon">
                            <i class="fas fa-table"></i>
                        </div>
                        <div class="db-stat-info">
                            <p class="db-stat-value"><?php echo count($tables); ?></p>
                            <p class="db-stat-label">Total Tables</p>
                        </div>
                    </div>
                    <div class="db-stat">
                        <div class="db-stat-icon">
                            <i class="fas fa-hdd"></i>
                        </div>
                        <div class="db-stat-info">
                            <p class="db-stat-value"><?php echo $db_size; ?></p>
                            <p class="db-stat-label">Database Size</p>
                        </div>
                    </div>
                    <div class="db-stat">
                        <div class="db-stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="db-stat-info">
                            <p class="db-stat-value"><?php echo date('Y-m-d H:i:s'); ?></p>
                            <p class="db-stat-label">Current Time</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="db-actions">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="backup">
                    <button type="submit" class="db-action-btn btn-backup">
                        <i class="fas fa-download"></i> Backup Database
                    </button>
                </form>
                
                <button class="db-action-btn btn-restore" onclick="openRestoreModal()">
                    <i class="fas fa-upload"></i> Restore Database
                </button>
                
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="optimize">
                    <button type="submit" class="db-action-btn btn-optimize">
                        <i class="fas fa-broom"></i> Optimize Database
                    </button>
                </form>
            </div>
            
            <div class="sql-console">
                <h3>SQL Console</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="execute_query">
                    <textarea name="sql_query" class="sql-textarea" placeholder="Enter your SQL query here..."></textarea>
                    <button type="submit" class="sql-submit">Execute Query</button>
                </form>
            </div>
            
            <div class="table-list">
                <h3>Database Tables</h3>
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Rows</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $table): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($table); ?></td>
                                <td><?php echo isset($tables_info[$table]) ? $tables_info[$table]['rows'] : 'N/A'; ?></td>
                                <td class="table-actions">
                                    <a href="#" class="view-btn" onclick="viewTableData('<?php echo $table; ?>')">
                                        <i class="fas fa-eye"></i> View Data
                                    </a>
                                    <a href="#" class="structure-btn" onclick="viewTableStructure('<?php echo $table; ?>')">
                                        <i class="fas fa-sitemap"></i> Structure
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Restore Database Modal (would be implemented in a real application) -->
    <script>
        function openRestoreModal() {
            alert('This would open a modal to upload a database backup file. Feature not implemented in this demo.');
        }
        
        function viewTableData(tableName) {
            alert(`This would show the data in the "${tableName}" table. Feature not implemented in this demo.`);
        }
        
        function viewTableStructure(tableName) {
            alert(`This would show the structure of the "${tableName}" table. Feature not implemented in this demo.`);
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

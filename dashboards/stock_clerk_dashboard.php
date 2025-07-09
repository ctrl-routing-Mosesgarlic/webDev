<?php
/**
 * Stock Clerk Dashboard
 * Dashboard for stock clerks with inventory management capabilities
 */

// Include session management
require_once '../session.php';
require_once '../connection.php';

// Check if user is logged in and has stock_clerk role
if (!isLoggedIn() || getSessionVar('role') !== 'stock_clerk') {
    // Redirect to login page if not logged in or not a stock clerk
    header("Location: ../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Get inventory statistics (in a real app, these would come from database)
$total_items = 0;
$low_stock_items = 0;
$pending_orders = 0;

// Query for total inventory items
$inventory_query = "SELECT COUNT(*) as count FROM inventory";
$inventory_result = mysqli_query($conn, $inventory_query);
if ($inventory_result && mysqli_num_rows($inventory_result) > 0) {
    $row = mysqli_fetch_assoc($inventory_result);
    $total_items = $row['count'];
} else {
    // For demo purposes if inventory table doesn't exist yet
    $total_items = 245;
}

// Query for low stock items (simulated)
$low_stock_items = 18;

// Query for pending orders (simulated)
$pending_orders = 7;

// Recent activities (in a real app, these would come from a log table)
$recent_activities = [
    ['action' => 'Stock updated', 'item' => 'Printer Paper', 'time' => '1 hour ago'],
    ['action' => 'Item received', 'item' => 'Toner Cartridges', 'time' => '3 hours ago'],
    ['action' => 'Order fulfilled', 'item' => 'USB Drives', 'time' => 'Yesterday'],
    ['action' => 'Inventory count', 'item' => 'Office Supplies', 'time' => 'Yesterday']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Clerk Dashboard - W&SM System</title>
    <link rel="stylesheet" href="../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .alert-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #fff3cd;
            color: #856404;
            border-left: 5px solid #ffeeba;
        }
        .task-list {
            list-style: none;
            padding: 0;
        }
        .task-list li {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .task-list li:last-child {
            border-bottom: none;
        }
        .task-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }
        .status-pending {
            background-color: #f5b74f;
        }
        .status-completed {
            background-color: #2dce89;
        }
        .status-in-progress {
            background-color: #5a8dee;
        }
        .activity-list {
            list-style: none;
            padding: 0;
        }
        .activity-list li {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }
        .activity-list li:last-child {
            border-bottom: none;
        }
        .activity-item {
            display: flex;
            justify-content: space-between;
        }
        .activity-details {
            flex-grow: 1;
        }
        .activity-action {
            font-weight: 600;
        }
        .activity-item-name {
            color: #8898aa;
            font-size: 0.9rem;
        }
        .activity-time {
            color: #8898aa;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>W&SM System</h2>
                <p>Inventory</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="stock_clerk_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="stock_clerk/inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="stock_clerk/orders.php"><i class="fas fa-clipboard-list"></i> Orders</a></li>
                    <li><a href="stock_clerk/receiving.php"><i class="fas fa-truck-loading"></i> Receiving</a></li>
                    <li><a href="stock_clerk/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Stock Clerk Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_items; ?></div>
                    <div class="stat-label">Total Items</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $low_stock_items; ?></div>
                    <div class="stat-label">Low Stock Items</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $pending_orders; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo date('M d'); ?></div>
                    <div class="stat-label">Today's Date</div>
                </div>
            </div>
            
            <?php if ($low_stock_items > 0): ?>
            <div class="alert-box">
                <i class="fas fa-exclamation-triangle"></i> 
                There are <?php echo $low_stock_items; ?> items with low stock levels that need attention.
                <a href="stock_clerk/inventory.php?filter=low_stock" style="margin-left: 10px; font-weight: bold;">View Items</a>
            </div>
            <?php endif; ?>
            
            <div class="dashboard-content">
                <div class="card">
                    <h2>Inventory Overview</h2>
                    <p>Welcome <?php echo htmlspecialchars($fullname); ?> to the Stock Clerk Dashboard. As a stock clerk, you can manage inventory, process orders, and handle receiving of new stock.</p>
                    <p>Use the sidebar navigation to access different inventory management functions:</p>
                    <ul>
                        <li><strong><a href="stock_clerk/inventory.php">Inventory</a></strong> - View and manage all inventory items</li>
                        <li><strong><a href="stock_clerk/orders.php">Orders</a></strong> - Process and fulfill orders</li>
                        <li><strong><a href="stock_clerk/receiving.php">Receiving</a></strong> - Receive and log new inventory</li>
                        <li><strong><a href="stock_clerk/reports.php">Reports</a></strong> - Generate inventory reports</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h2>Today's Tasks</h2>
                    <ul class="task-list">
                        <li>
                            <div>
                                <strong>Process new shipment from Supplier Inc.</strong>
                                <div>Verify contents and update inventory</div>
                            </div>
                            <span class="task-status status-pending">Pending</span>
                        </li>
                        <li>
                            <div>
                                <strong>Fulfill order #1089</strong>
                                <div>Prepare items for shipping</div>
                            </div>
                            <span class="task-status status-in-progress">In Progress</span>
                        </li>
                        <li>
                            <div>
                                <strong>Inventory count for Office Supplies</strong>
                                <div>Verify physical count matches system</div>
                            </div>
                            <span class="task-status status-completed">Completed</span>
                        </li>
                        <li>
                            <div>
                                <strong>Update low stock items</strong>
                                <div>Create purchase orders for low stock</div>
                            </div>
                            <span class="task-status status-pending">Pending</span>
                        </li>
                    </ul>
                </div>
                
                <div class="card">
                    <h2>Recent Activities</h2>
                    <ul class="activity-list">
                        <?php foreach ($recent_activities as $activity): ?>
                        <li>
                            <div class="activity-item">
                                <div class="activity-details">
                                    <div class="activity-action"><?php echo htmlspecialchars($activity['action']); ?></div>
                                    <div class="activity-item-name"><?php echo htmlspecialchars($activity['item']); ?></div>
                                </div>
                                <div class="activity-time"><?php echo htmlspecialchars($activity['time']); ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Simple dashboard functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Stock Clerk Dashboard loaded');
        });
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

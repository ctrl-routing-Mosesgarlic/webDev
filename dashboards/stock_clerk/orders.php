<?php
/**
 * Stock Clerk Orders Management
 * This page allows stock clerks to view and process orders
 */

// Include session management
require_once '../../session.php';
require_once '../../connection.php';

// Check if user is logged in and has stock_clerk role
if (!isLoggedIn() || getSessionVar('role') !== 'stock_clerk') {
    // Redirect to login page if not logged in or not a stock clerk
    header("Location: ../../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Handle order actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                if (isset($_POST['order_id']) && isset($_POST['status'])) {
                    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
                    $status = mysqli_real_escape_string($conn, $_POST['status']);
                    
                    // In a real application, you would update the order status in the database
                    $message = "Order status updated successfully.";
                    $message_type = "success";
                }
                break;
                
            case 'fulfill_order':
                if (isset($_POST['order_id'])) {
                    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
                    
                    // In a real application, you would mark the order as fulfilled in the database
                    $message = "Order marked as fulfilled successfully.";
                    $message_type = "success";
                }
                break;
        }
    }
}

// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Sample orders data (in a real app, this would come from database)
$orders = [
    [
        'id' => 1001,
        'customer' => 'John Smith',
        'date' => '2025-06-25',
        'items' => [
            ['name' => 'Printer Paper', 'quantity' => 5],
            ['name' => 'Stapler', 'quantity' => 1]
        ],
        'status' => 'pending',
        'priority' => 'normal'
    ],
    [
        'id' => 1002,
        'customer' => 'Jane Doe',
        'date' => '2025-06-24',
        'items' => [
            ['name' => 'USB Flash Drive 32GB', 'quantity' => 3],
            ['name' => 'Toner Cartridge', 'quantity' => 1]
        ],
        'status' => 'processing',
        'priority' => 'high'
    ],
    [
        'id' => 1003,
        'customer' => 'Robert Johnson',
        'date' => '2025-06-23',
        'items' => [
            ['name' => 'Ballpoint Pens (Box)', 'quantity' => 2]
        ],
        'status' => 'fulfilled',
        'priority' => 'normal'
    ],
    [
        'id' => 1004,
        'customer' => 'Sarah Williams',
        'date' => '2025-06-22',
        'items' => [
            ['name' => 'Printer Paper', 'quantity' => 10],
            ['name' => 'Stapler', 'quantity' => 2],
            ['name' => 'USB Flash Drive 32GB', 'quantity' => 5]
        ],
        'status' => 'pending',
        'priority' => 'urgent'
    ],
    [
        'id' => 1005,
        'customer' => 'Michael Brown',
        'date' => '2025-06-21',
        'items' => [
            ['name' => 'Toner Cartridge', 'quantity' => 2]
        ],
        'status' => 'fulfilled',
        'priority' => 'high'
    ]
];

// Filter orders based on status
if ($filter !== 'all') {
    $filtered_orders = array_filter($orders, function($order) use ($filter) {
        return $order['status'] === $filter;
    });
} else {
    $filtered_orders = $orders;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Stock Clerk Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        .orders-table th, .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .orders-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #344767;
        }
        .orders-table tr:hover {
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
        .view-btn {
            background-color: #5a8dee;
            color: white;
        }
        .fulfill-btn {
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
        .filter-tabs {
            display: flex;
            margin-bottom: 20px;
        }
        .filter-tab {
            padding: 8px 15px;
            margin-right: 10px;
            border-radius: 4px;
            cursor: pointer;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        .filter-tab.active {
            background-color: #4a6fdc;
            color: white;
            border-color: #4a6fdc;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            display: inline-block;
        }
        .status-pending {
            background-color: #f5b74f;
        }
        .status-processing {
            background-color: #5a8dee;
        }
        .status-fulfilled {
            background-color: #2dce89;
        }
        .status-cancelled {
            background-color: #f5365c;
        }
        .priority-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .priority-normal {
            background-color: #e9ecef;
            color: #344767;
        }
        .priority-high {
            background-color: #ffeeba;
            color: #856404;
        }
        .priority-urgent {
            background-color: #f8d7da;
            color: #721c24;
        }
        .order-items {
            margin-top: 10px;
            padding-left: 20px;
        }
        .order-items li {
            margin-bottom: 5px;
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
                    <li><a href="../stock_clerk_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li class="active"><a href="orders.php"><i class="fas fa-clipboard-list"></i> Orders</a></li>
                    <li><a href="receiving.php"><i class="fas fa-truck-loading"></i> Receiving</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Orders Management</h1>
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
            
            <div class="filter-tabs">
                <a href="orders.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All Orders</a>
                <a href="orders.php?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                <a href="orders.php?filter=processing" class="filter-tab <?php echo $filter === 'processing' ? 'active' : ''; ?>">Processing</a>
                <a href="orders.php?filter=fulfilled" class="filter-tab <?php echo $filter === 'fulfilled' ? 'active' : ''; ?>">Fulfilled</a>
            </div>
            
            <div class="card">
                <div class="filter-container">
                    <form class="search-box" method="GET">
                        <input type="text" name="search" placeholder="Search by order ID or customer" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
                
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filtered_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['date']))); ?></td>
                                <td>
                                    <ul class="order-items">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <li><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="priority-badge priority-<?php echo $order['priority']; ?>">
                                        <?php echo ucfirst($order['priority']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="#" class="view-btn" onclick="openViewModal(<?php echo $order['id']; ?>)"><i class="fas fa-eye"></i> View</a>
                                    <?php if ($order['status'] !== 'fulfilled'): ?>
                                        <a href="#" class="fulfill-btn" onclick="openFulfillModal(<?php echo $order['id']; ?>)"><i class="fas fa-check"></i> Fulfill</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- View Order Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeViewModal()">&times;</span>
            <h2>Order Details - #<span id="view_order_id"></span></h2>
            <div id="order_details">
                <!-- Order details will be populated here -->
            </div>
            
            <div class="form-group">
                <label for="status">Update Status:</label>
                <form id="updateStatusForm" method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" id="status_order_id" name="order_id" value="">
                    
                    <select id="status" name="status">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="fulfilled">Fulfilled</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    
                    <div class="btn-container">
                        <button type="submit" class="btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Fulfill Order Modal -->
    <div id="fulfillModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeFulfillModal()">&times;</span>
            <h2>Fulfill Order - #<span id="fulfill_order_id"></span></h2>
            <form id="fulfillForm" method="POST">
                <input type="hidden" name="action" value="fulfill_order">
                <input type="hidden" id="fulfill_order_id_input" name="order_id" value="">
                
                <div class="form-group">
                    <label>Please confirm that all items have been picked and packed:</label>
                    <div id="fulfill_items_list">
                        <!-- Items will be populated here -->
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes:</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <div class="btn-container">
                    <button type="button" class="btn-secondary" onclick="closeFulfillModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Mark as Fulfilled</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Sample order data for JavaScript functions
        const orderData = <?php echo json_encode($orders); ?>;
        
        // View order modal functions
        function openViewModal(orderId) {
            const order = orderData.find(o => o.id === orderId);
            if (!order) return;
            
            document.getElementById('view_order_id').textContent = order.id;
            document.getElementById('status_order_id').value = order.id;
            document.getElementById('status').value = order.status;
            
            let detailsHtml = `
                <p><strong>Customer:</strong> ${order.customer}</p>
                <p><strong>Date:</strong> ${new Date(order.date).toLocaleDateString()}</p>
                <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></p>
                <p><strong>Priority:</strong> <span class="priority-badge priority-${order.priority}">${order.priority.charAt(0).toUpperCase() + order.priority.slice(1)}</span></p>
                <p><strong>Items:</strong></p>
                <ul>
            `;
            
            order.items.forEach(item => {
                detailsHtml += `<li>${item.name} (x${item.quantity})</li>`;
            });
            
            detailsHtml += '</ul>';
            document.getElementById('order_details').innerHTML = detailsHtml;
            document.getElementById('viewModal').style.display = 'block';
        }
        
        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }
        
        // Fulfill order modal functions
        function openFulfillModal(orderId) {
            const order = orderData.find(o => o.id === orderId);
            if (!order) return;
            
            document.getElementById('fulfill_order_id').textContent = order.id;
            document.getElementById('fulfill_order_id_input').value = order.id;
            
            let itemsHtml = '<ul>';
            order.items.forEach(item => {
                itemsHtml += `
                    <li>
                        <input type="checkbox" id="item_${item.name.replace(/\s+/g, '_')}" required>
                        <label for="item_${item.name.replace(/\s+/g, '_')}">${item.name} (x${item.quantity})</label>
                    </li>
                `;
            });
            itemsHtml += '</ul>';
            
            document.getElementById('fulfill_items_list').innerHTML = itemsHtml;
            document.getElementById('fulfillModal').style.display = 'block';
        }
        
        function closeFulfillModal() {
            document.getElementById('fulfillModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('viewModal')) {
                closeViewModal();
            }
            if (event.target == document.getElementById('fulfillModal')) {
                closeFulfillModal();
            }
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

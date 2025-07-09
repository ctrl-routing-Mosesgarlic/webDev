<?php
/**
 * Customer Orders Page
 * This page allows customers to view their order history and details
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include session management and database connection
require_once '../../session.php';
require_once '../../connection.php';

// Initialize database connection
$db = new database();
$conn = $db->getConnection();

if (!$conn) {
    die("Database connection failed");
}

// Check if user is logged in and has customer role
if (!isLoggedIn() || getSessionVar('role') !== 'customer') {
    // Redirect to login page if not logged in or not a customer
    header("Location: ../../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Initialize variables
$view_order = false;
$order_details = null;
$order_id = '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Handle order view request
$view_order = false;
$order_details = null;
$order_id = '';

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $view_order = true;
    
    // In a real application, you would fetch the order details from the database
    // For demo purposes, we'll use sample data
    if ($order_id === 'ORD-2023-8745') {
        $order_details = [
            'id' => 'ORD-2023-8745',
            'date' => '2025-06-15',
            'amount' => 120.00,
            'status' => 'delivered',
            'shipping_address' => '123 Main St, Anytown, ST 12345',
            'payment_method' => 'Credit Card (ending in 1234)',
            'items' => [
                ['name' => 'Office Chair', 'quantity' => 1, 'price' => 89.99],
                ['name' => 'Desk Lamp', 'quantity' => 1, 'price' => 30.01]
            ],
            'tracking' => [
                ['date' => '2025-06-15', 'status' => 'Delivered', 'location' => 'Customer address'],
                ['date' => '2025-06-14', 'status' => 'Out for delivery', 'location' => 'Local distribution center'],
                ['date' => '2025-06-13', 'status' => 'Arrived at sorting facility', 'location' => 'Regional hub'],
                ['date' => '2025-06-12', 'status' => 'Shipped', 'location' => 'Warehouse']
            ]
        ];
    } elseif ($order_id === 'ORD-2023-8621') {
        $order_details = [
            'id' => 'ORD-2023-8621',
            'date' => '2025-06-10',
            'amount' => 85.50,
            'status' => 'in-transit',
            'shipping_address' => '123 Main St, Anytown, ST 12345',
            'payment_method' => 'PayPal',
            'items' => [
                ['name' => 'Wireless Mouse', 'quantity' => 1, 'price' => 45.50],
                ['name' => 'Mouse Pad', 'quantity' => 2, 'price' => 20.00]
            ],
            'tracking' => [
                ['date' => '2025-06-12', 'status' => 'In transit', 'location' => 'Regional distribution center'],
                ['date' => '2025-06-11', 'status' => 'Shipped', 'location' => 'Warehouse']
            ]
        ];
    } elseif ($order_id === 'ORD-2023-8512') {
        $order_details = [
            'id' => 'ORD-2023-8512',
            'date' => '2025-06-05',
            'amount' => 45.00,
            'status' => 'delivered',
            'shipping_address' => '123 Main St, Anytown, ST 12345',
            'payment_method' => 'Credit Card (ending in 1234)',
            'items' => [
                ['name' => 'USB Cable', 'quantity' => 3, 'price' => 45.00]
            ],
            'tracking' => [
                ['date' => '2025-06-08', 'status' => 'Delivered', 'location' => 'Customer address'],
                ['date' => '2025-06-07', 'status' => 'Out for delivery', 'location' => 'Local distribution center'],
                ['date' => '2025-06-06', 'status' => 'Shipped', 'location' => 'Warehouse']
            ]
        ];
    }
}

// Sample orders data (in a real app, this would come from database)
$orders = [
    [
        'id' => 'ORD-2023-8745',
        'date' => '2025-06-15',
        'amount' => 120.00,
        'status' => 'delivered',
        'items_count' => 2
    ],
    [
        'id' => 'ORD-2023-8621',
        'date' => '2025-06-10',
        'amount' => 85.50,
        'status' => 'in-transit',
        'items_count' => 3
    ],
    [
        'id' => 'ORD-2023-8512',
        'date' => '2025-06-05',
        'amount' => 45.00,
        'status' => 'delivered',
        'items_count' => 1
    ],
    [
        'id' => 'ORD-2023-8423',
        'date' => '2025-05-28',
        'amount' => 67.25,
        'status' => 'delivered',
        'items_count' => 2
    ],
    [
        'id' => 'ORD-2023-8356',
        'date' => '2025-05-20',
        'amount' => 124.99,
        'status' => 'delivered',
        'items_count' => 3
    ]
];

// Filter orders if needed
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
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
    <title>My Orders - Customer Dashboard</title>
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
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            display: inline-block;
        }
        .delivered {
            background-color: #2dce89;
        }
        .in-transit {
            background-color: #5a8dee;
        }
        .processing {
            background-color: #f5b74f;
        }
        .cancelled {
            background-color: #f5365c;
        }
        .view-btn {
            padding: 6px 10px;
            background-color: #5a8dee;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
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
            text-decoration: none;
            color: #344767;
        }
        .filter-tab.active {
            background-color: #4a6fdc;
            color: white;
            border-color: #4a6fdc;
        }
        .order-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .order-header h2 {
            margin: 0;
        }
        .order-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        .meta-item {
            flex: 1;
            min-width: 200px;
        }
        .meta-item h4 {
            margin-top: 0;
            margin-bottom: 5px;
            color: #8898aa;
        }
        .meta-item p {
            margin: 0;
            font-weight: 500;
        }
        .order-items {
            margin-bottom: 20px;
        }
        .order-items table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-items th, .order-items td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .order-items th {
            background-color: #fff;
            font-weight: 600;
            color: #344767;
        }
        .order-total {
            text-align: right;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .tracking-timeline {
            margin-top: 30px;
        }
        .timeline-item {
            display: flex;
            margin-bottom: 15px;
            position: relative;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: 16px;
            top: 30px;
            height: calc(100% + 15px);
            width: 2px;
            background-color: #e9ecef;
        }
        .timeline-item:last-child:before {
            display: none;
        }
        .timeline-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background-color: #5a8dee;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            position: relative;
            z-index: 1;
        }
        .timeline-content {
            flex: 1;
        }
        .timeline-date {
            color: #8898aa;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .timeline-status {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .timeline-location {
            color: #8898aa;
        }
        .back-btn {
            padding: 8px 15px;
            background-color: #8898aa;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .back-btn i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>W&SM System</h2>
                <p>Customer Portal</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../customer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                    <li><a href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li><a href="support.php"><i class="fas fa-comment-alt"></i> Support</a></li>
                    <li><a href="account.php"><i class="fas fa-user-circle"></i> My Account</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1><?php echo $view_order ? 'Order Details' : 'My Orders'; ?></h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <?php if ($view_order && $order_details): ?>
                <a href="orders.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Orders</a>
                
                <div class="order-details">
                    <div class="order-header">
                        <h2>Order #<?php echo htmlspecialchars($order_details['id']); ?></h2>
                        <span class="status-badge <?php echo $order_details['status']; ?>">
                            <?php echo ucfirst($order_details['status'] === 'in-transit' ? 'In Transit' : $order_details['status']); ?>
                        </span>
                    </div>
                    
                    <div class="order-meta">
                        <div class="meta-item">
                            <h4>Order Date</h4>
                            <p><?php echo date('M d, Y', strtotime($order_details['date'])); ?></p>
                        </div>
                        <div class="meta-item">
                            <h4>Total Amount</h4>
                            <p>$<?php echo number_format($order_details['amount'], 2); ?></p>
                        </div>
                        <div class="meta-item">
                            <h4>Shipping Address</h4>
                            <p><?php echo htmlspecialchars($order_details['shipping_address']); ?></p>
                        </div>
                        <div class="meta-item">
                            <h4>Payment Method</h4>
                            <p><?php echo htmlspecialchars($order_details['payment_method']); ?></p>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <h3>Order Items</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_details['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['price'] / $item['quantity'], 2); ?></td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="order-total">Total:</td>
                                    <td class="order-total">$<?php echo number_format($order_details['amount'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="tracking-timeline">
                        <h3>Order Tracking</h3>
                        <?php foreach ($order_details['tracking'] as $track): ?>
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-date"><?php echo date('M d, Y', strtotime($track['date'])); ?></div>
                                    <div class="timeline-status"><?php echo htmlspecialchars($track['status']); ?></div>
                                    <div class="timeline-location"><?php echo htmlspecialchars($track['location']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="filter-tabs">
                    <a href="orders.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All Orders</a>
                    <a href="orders.php?filter=in-transit" class="filter-tab <?php echo $filter === 'in-transit' ? 'active' : ''; ?>">In Transit</a>
                    <a href="orders.php?filter=delivered" class="filter-tab <?php echo $filter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
                </div>
                
                <div class="card">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['date'])); ?></td>
                                    <td><?php echo $order['items_count']; ?> item(s)</td>
                                    <td>$<?php echo number_format($order['amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status'] === 'in-transit' ? 'In Transit' : $order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="orders.php?order_id=<?php echo urlencode($order['id']); ?>" class="view-btn">
                                            <?php echo $order['status'] === 'in-transit' ? 'Track' : 'View'; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
<?php
// Close database connection
if (isset($db)) {
    $db->closeConnection();
}
?>

<?php
/**
 * Customer Dashboard
 * Dashboard for customers with customer-specific features
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include session management and database connection
require_once '../session.php';
require_once '../connection.php';

// Initialize database connection
$db = new database();
$conn = $db->getConnection();

if (!$conn) {
    die("Database connection failed");
}

// Check if user is logged in and has customer role
if (!isLoggedIn() || getSessionVar('role') !== 'customer') {
    // Redirect to login page if not logged in or not a customer
    header("Location: ../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Initialize default values for demo purposes
$total_orders = 0;
$active_orders = 0;
$completed_orders = 0;
$account_credit = 0;

// Check if orders table exists before querying
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'orders'");

if ($table_check && mysqli_num_rows($table_check) > 0) {
    // Orders table exists, try to query it
    $orders_query = "SELECT COUNT(*) as count FROM orders WHERE customer_id = '" . mysqli_real_escape_string($conn, $user_id) . "'";
    $orders_result = mysqli_query($conn, $orders_query);
    
    if ($orders_result && mysqli_num_rows($orders_result) > 0) {
        $row = mysqli_fetch_assoc($orders_result);
        $total_orders = (int)$row['count'];
    }
    
    // You can add more queries for active_orders, completed_orders, etc. here
    // For now, we'll use demo values
    $active_orders = 1;
    $completed_orders = $total_orders - $active_orders;
} else {
    // For demo purposes since tables don't exist yet
    $total_orders = 5;
    $active_orders = 1;
    $completed_orders = 4;
}

// Set demo account credit
$account_credit = 0;

// Query for completed orders (simulated)
$completed_orders = 3;

// Query for account credit (simulated)
$account_credit = 250.00;

// Get recent orders (in a real app, these would come from database)
$recent_orders = [
    [
        'id' => 'ORD-2023-8745',
        'date' => '2025-06-15',
        'amount' => 120.00,
        'status' => 'delivered'
    ],
    [
        'id' => 'ORD-2023-8621',
        'date' => '2025-06-10',
        'amount' => 85.50,
        'status' => 'in-transit'
    ],
    [
        'id' => 'ORD-2023-8512',
        'date' => '2025-06-05',
        'amount' => 45.00,
        'status' => 'delivered'
    ]
];
?>

<?php
/**
 * Customer Dashboard
 * Dashboard for customers with customer-specific features
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include session management and database connection
require_once '../session.php';
require_once '../connection.php';

// Initialize database connection
$db = new database();
$conn = $db->getConnection();

if (!$conn) {
    die("Database connection failed");
}

// Check if user is logged in and has customer role
if (!isLoggedIn() || getSessionVar('role') !== 'customer') {
    // Redirect to login page if not logged in or not a customer
    header("Location: ../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Initialize default values for demo purposes
$total_orders = 0;
$active_orders = 0;
$completed_orders = 0;
$account_credit = 0;

// Check if orders table exists before querying
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'orders'");

if ($table_check && mysqli_num_rows($table_check) > 0) {
    // Orders table exists, try to query it
    $orders_query = "SELECT COUNT(*) as count FROM orders WHERE customer_id = '" . mysqli_real_escape_string($conn, $user_id) . "'";
    $orders_result = mysqli_query($conn, $orders_query);
    
    if ($orders_result && mysqli_num_rows($orders_result) > 0) {
        $row = mysqli_fetch_assoc($orders_result);
        $total_orders = (int)$row['count'];
    }
    
    // You can add more queries for active_orders, completed_orders, etc. here
    // For now, we'll use demo values
    $active_orders = 1;
    $completed_orders = $total_orders - $active_orders;
} else {
    // For demo purposes since tables don't exist yet
    $total_orders = 5;
    $active_orders = 1;
    $completed_orders = 4;
}

// Set demo account credit
$account_credit = 0;
?>

{% extends "../base.html" %}

{% block head %}
    <link rel="stylesheet" href="/static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Customer Dashboard - W&SM System</title>
{% endblock %}

{% block content %}
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>W&SM System</h2>
            <p>Customer Portal</p>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="active"><a href="customer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="customer/orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                    <li><a href="customer/favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li><a href="customer/support.php"><i class="fas fa-comment-alt"></i> Support</a></li>
                    <li><a href="customer/account.php"><i class="fas fa-user-circle"></i> My Account</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Customer Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $active_orders; ?></div>
                    <div class="stat-label">Active Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $completed_orders; ?></div>
                    <div class="stat-label">Completed Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">$<?php echo number_format($account_credit, 2); ?></div>
                    <div class="stat-label">Account Credit</div>
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="card">
                    <h2>Recent Orders</h2>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['date'])); ?></td>
                                <td>$<?php echo number_format($order['amount'], 2); ?></td>
                                <td><span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status'] === 'in-transit' ? 'In Transit' : $order['status']); ?></span></td>
                                <td><a href="customer/orders.php?order_id=<?php echo urlencode($order['id']); ?>" class="view-btn"><?php echo $order['status'] === 'in-transit' ? 'Track' : 'View'; ?></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="card">
                    <h2>Special Offers</h2>
                    <div class="offers-container">
                        <div class="offer">
                            <div class="offer-badge">20% OFF</div>
                            <h3>Summer Sale</h3>
                            <p>Get 20% off on all summer products. Limited time offer!</p>
                            <button class="offer-btn">Shop Now</button>
                        </div>
                        <div class="offer">
                            <div class="offer-badge">FREE</div>
                            <h3>Free Shipping</h3>
                            <p>Free shipping on all orders above $50. No coupon needed.</p>
                            <button class="offer-btn">Learn More</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../static/js/dashboard.js"></script>
{% endblock %}

<?php
// Close database connection
if (isset($db)) {
    $db->closeConnection();
}
?>

<?php
/**
 * Stock Clerk Reports
 * This page allows stock clerks to generate and view inventory reports
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

// Handle report generation
$message = '';
$message_type = '';
$show_report = false;
$report_type = '';
$start_date = '';
$end_date = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'generate_report') {
        $report_type = isset($_POST['report_type']) ? $_POST['report_type'] : '';
        $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
        $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
        
        if (empty($report_type) || empty($start_date) || empty($end_date)) {
            $message = "Please fill in all required fields.";
            $message_type = "error";
        } else {
            // In a real application, you would generate the report from database data
            $show_report = true;
            $message = "Report generated successfully.";
            $message_type = "success";
        }
    }
}

// Sample inventory statistics (in a real app, these would come from database)
$inventory_stats = [
    'total_items' => 245,
    'low_stock_items' => 18,
    'out_of_stock' => 5,
    'inventory_value' => 12750.50
];

// Sample category distribution (in a real app, this would come from database)
$category_distribution = [
    ['category' => 'Office Supplies', 'count' => 120, 'percentage' => 49],
    ['category' => 'Printer Supplies', 'count' => 45, 'percentage' => 18],
    ['category' => 'Electronics', 'count' => 60, 'percentage' => 24],
    ['category' => 'Furniture', 'count' => 20, 'percentage' => 9]
];

// Sample monthly activity (in a real app, this would come from database)
$monthly_activity = [
    ['month' => 'Jan', 'received' => 45, 'shipped' => 38],
    ['month' => 'Feb', 'received' => 52, 'shipped' => 45],
    ['month' => 'Mar', 'received' => 48, 'shipped' => 51],
    ['month' => 'Apr', 'received' => 60, 'shipped' => 55],
    ['month' => 'May', 'received' => 55, 'shipped' => 48],
    ['month' => 'Jun', 'received' => 70, 'shipped' => 65]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Stock Clerk Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        .form-group {
            margin-right: 20px;
            margin-bottom: 15px;
            flex: 1;
            min-width: 200px;
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
            margin-top: 10px;
        }
        .btn-primary {
            background-color: #4a6fdc;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
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
        .report-container {
            margin-top: 30px;
        }
        .stats-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 200px;
        }
        .stat-card h3 {
            margin-top: 0;
            color: #344767;
            font-size: 1rem;
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: #344767;
            margin: 10px 0;
        }
        .chart-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .chart-container h3 {
            margin-top: 0;
            color: #344767;
        }
        .chart-wrapper {
            height: 300px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .report-table th, .report-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .report-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #344767;
        }
        .report-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .report-actions button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .report-actions button i {
            margin-right: 5px;
        }
        .btn-export {
            background-color: #2dce89;
            color: white;
        }
        .btn-print {
            background-color: #5a8dee;
            color: white;
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
                    <li><a href="orders.php"><i class="fas fa-clipboard-list"></i> Orders</a></li>
                    <li><a href="receiving.php"><i class="fas fa-truck-loading"></i> Receiving</a></li>
                    <li class="active"><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Inventory Reports</h1>
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
                <h2>Generate Report</h2>
                <form class="report-form" method="POST">
                    <input type="hidden" name="action" value="generate_report">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="report_type">Report Type:</label>
                            <select id="report_type" name="report_type" required>
                                <option value="">Select Report Type</option>
                                <option value="inventory_status" <?php echo $report_type === 'inventory_status' ? 'selected' : ''; ?>>Inventory Status</option>
                                <option value="low_stock" <?php echo $report_type === 'low_stock' ? 'selected' : ''; ?>>Low Stock Items</option>
                                <option value="inventory_movement" <?php echo $report_type === 'inventory_movement' ? 'selected' : ''; ?>>Inventory Movement</option>
                                <option value="category_distribution" <?php echo $report_type === 'category_distribution' ? 'selected' : ''; ?>>Category Distribution</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="start_date">Start Date:</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date">End Date:</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
                        </div>
                    </div>
                    
                    <div class="btn-container">
                        <button type="submit" class="btn-primary"><i class="fas fa-chart-line"></i> Generate Report</button>
                    </div>
                </form>
            </div>
            
            <?php if ($show_report): ?>
            <div class="report-container">
                <h2>Report: <?php echo ucwords(str_replace('_', ' ', $report_type)); ?></h2>
                <p>Period: <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?></p>
                
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3>Total Items</h3>
                        <div class="stat-value"><?php echo $inventory_stats['total_items']; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Low Stock Items</h3>
                        <div class="stat-value"><?php echo $inventory_stats['low_stock_items']; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Out of Stock</h3>
                        <div class="stat-value"><?php echo $inventory_stats['out_of_stock']; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Inventory Value</h3>
                        <div class="stat-value">$<?php echo number_format($inventory_stats['inventory_value'], 2); ?></div>
                    </div>
                </div>
                
                <div class="chart-container">
                    <h3>Category Distribution</h3>
                    <div class="chart-wrapper">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-container">
                    <h3>Monthly Activity</h3>
                    <div class="chart-wrapper">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
                
                <div class="report-actions">
                    <button class="btn-export" onclick="exportReport()"><i class="fas fa-file-export"></i> Export to Excel</button>
                    <button class="btn-print" onclick="printReport()"><i class="fas fa-print"></i> Print Report</button>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        // Initialize charts if report is shown
        <?php if ($show_report): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Category distribution chart
            const categoryData = <?php echo json_encode($category_distribution); ?>;
            const categoryLabels = categoryData.map(item => item.category);
            const categoryValues = categoryData.map(item => item.count);
            const categoryColors = [
                'rgba(74, 111, 220, 0.7)',
                'rgba(45, 206, 137, 0.7)',
                'rgba(245, 183, 79, 0.7)',
                'rgba(90, 141, 238, 0.7)'
            ];
            
            new Chart(document.getElementById('categoryChart'), {
                type: 'pie',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryValues,
                        backgroundColor: categoryColors,
                        borderColor: categoryColors.map(color => color.replace('0.7', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
            
            // Monthly activity chart
            const activityData = <?php echo json_encode($monthly_activity); ?>;
            const months = activityData.map(item => item.month);
            const receivedData = activityData.map(item => item.received);
            const shippedData = activityData.map(item => item.shipped);
            
            new Chart(document.getElementById('activityChart'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Items Received',
                            data: receivedData,
                            backgroundColor: 'rgba(74, 111, 220, 0.7)',
                            borderColor: 'rgba(74, 111, 220, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Items Shipped',
                            data: shippedData,
                            backgroundColor: 'rgba(245, 183, 79, 0.7)',
                            borderColor: 'rgba(245, 183, 79, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
        <?php endif; ?>
        
        // Export report function (placeholder)
        function exportReport() {
            alert('In a real application, this would export the report to Excel.');
        }
        
        // Print report function
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

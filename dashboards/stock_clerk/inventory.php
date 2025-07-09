<?php
/**
 * Stock Clerk Inventory Management
 * This page allows stock clerks to view and manage inventory items
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

// Handle inventory actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_quantity':
                if (isset($_POST['item_id']) && isset($_POST['quantity'])) {
                    $item_id = mysqli_real_escape_string($conn, $_POST['item_id']);
                    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
                    
                    // In a real application, you would update the inventory in the database
                    $message = "Inventory quantity updated successfully.";
                    $message_type = "success";
                }
                break;
                
            case 'add_item':
                // Process adding a new inventory item
                $message = "New inventory item added successfully.";
                $message_type = "success";
                break;
        }
    }
}

// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Sample inventory data (in a real app, this would come from database)
$inventory_items = [
    [
        'id' => 1,
        'name' => 'Printer Paper',
        'sku' => 'PP-8511',
        'category' => 'Office Supplies',
        'quantity' => 120,
        'min_quantity' => 50,
        'location' => 'Shelf A1',
        'last_updated' => '2025-06-25'
    ],
    [
        'id' => 2,
        'name' => 'Toner Cartridge',
        'sku' => 'TC-4500',
        'category' => 'Printer Supplies',
        'quantity' => 15,
        'min_quantity' => 20,
        'location' => 'Shelf B3',
        'last_updated' => '2025-06-24'
    ],
    [
        'id' => 3,
        'name' => 'USB Flash Drive 32GB',
        'sku' => 'USB-32',
        'category' => 'Electronics',
        'quantity' => 45,
        'min_quantity' => 30,
        'location' => 'Shelf C2',
        'last_updated' => '2025-06-23'
    ],
    [
        'id' => 4,
        'name' => 'Stapler',
        'sku' => 'ST-101',
        'category' => 'Office Supplies',
        'quantity' => 25,
        'min_quantity' => 15,
        'location' => 'Shelf A2',
        'last_updated' => '2025-06-22'
    ],
    [
        'id' => 5,
        'name' => 'Ballpoint Pens (Box)',
        'sku' => 'BP-50',
        'category' => 'Office Supplies',
        'quantity' => 8,
        'min_quantity' => 10,
        'location' => 'Shelf A3',
        'last_updated' => '2025-06-21'
    ]
];

// Filter inventory items if needed
if ($filter === 'low_stock') {
    $filtered_items = array_filter($inventory_items, function($item) {
        return $item['quantity'] <= $item['min_quantity'];
    });
} else {
    $filtered_items = $inventory_items;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Stock Clerk Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .inventory-table {
            width: 100%;
            border-collapse: collapse;
        }
        .inventory-table th, .inventory-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .inventory-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #344767;
        }
        .inventory-table tr:hover {
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
        .adjust-btn {
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
        .add-item-btn {
            padding: 8px 15px;
            background-color: #2dce89;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .add-item-btn i {
            margin-right: 5px;
        }
        .low-stock {
            color: #f5365c;
            font-weight: bold;
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
                    <li class="active"><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="orders.php"><i class="fas fa-clipboard-list"></i> Orders</a></li>
                    <li><a href="receiving.php"><i class="fas fa-truck-loading"></i> Receiving</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Inventory Management</h1>
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
                <a href="inventory.php" class="filter-tab <?php echo $filter === '' ? 'active' : ''; ?>">All Items</a>
                <a href="inventory.php?filter=low_stock" class="filter-tab <?php echo $filter === 'low_stock' ? 'active' : ''; ?>">Low Stock</a>
            </div>
            
            <div class="card">
                <div class="filter-container">
                    <form class="search-box" method="GET">
                        <input type="text" name="search" placeholder="Search by name or SKU" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                    <a href="#" class="add-item-btn" onclick="openAddItemModal()"><i class="fas fa-plus"></i> Add Item</a>
                </div>
                
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Min Quantity</th>
                            <th>Location</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filtered_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['sku']); ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td class="<?php echo $item['quantity'] <= $item['min_quantity'] ? 'low-stock' : ''; ?>">
                                    <?php echo $item['quantity']; ?>
                                </td>
                                <td><?php echo $item['min_quantity']; ?></td>
                                <td><?php echo htmlspecialchars($item['location']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($item['last_updated']))); ?></td>
                                <td class="action-buttons">
                                    <a href="#" class="edit-btn" onclick="openEditModal(<?php echo $item['id']; ?>)"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="#" class="adjust-btn" onclick="openAdjustModal(<?php echo $item['id']; ?>, '<?php echo $item['name']; ?>', <?php echo $item['quantity']; ?>)"><i class="fas fa-balance-scale"></i> Adjust</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Adjust Quantity Modal -->
    <div id="adjustModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAdjustModal()">&times;</span>
            <h2>Adjust Quantity for <span id="item_name"></span></h2>
            <form id="adjustForm" method="POST">
                <input type="hidden" name="action" value="update_quantity">
                <input type="hidden" id="item_id" name="item_id" value="">
                
                <div class="form-group">
                    <label for="current_quantity">Current Quantity:</label>
                    <input type="number" id="current_quantity" disabled>
                </div>
                
                <div class="form-group">
                    <label for="quantity">New Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="reason">Reason for Adjustment:</label>
                    <select id="reason" name="reason" required>
                        <option value="">Select Reason</option>
                        <option value="inventory_count">Inventory Count</option>
                        <option value="damaged">Damaged/Defective</option>
                        <option value="returned">Customer Return</option>
                        <option value="correction">Data Correction</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes:</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <div class="btn-container">
                    <button type="button" class="btn-secondary" onclick="closeAdjustModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Update Quantity</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Item Modal -->
    <div id="addItemModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeAddItemModal()">&times;</span>
            <h2>Add New Inventory Item</h2>
            <form id="addItemForm" method="POST">
                <input type="hidden" name="action" value="add_item">
                
                <div class="form-group">
                    <label for="new_name">Item Name:</label>
                    <input type="text" id="new_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="new_sku">SKU:</label>
                    <input type="text" id="new_sku" name="sku" required>
                </div>
                
                <div class="form-group">
                    <label for="new_category">Category:</label>
                    <select id="new_category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Office Supplies">Office Supplies</option>
                        <option value="Printer Supplies">Printer Supplies</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Furniture">Furniture</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="new_quantity">Initial Quantity:</label>
                    <input type="number" id="new_quantity" name="quantity" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="new_min_quantity">Minimum Quantity:</label>
                    <input type="number" id="new_min_quantity" name="min_quantity" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="new_location">Storage Location:</label>
                    <input type="text" id="new_location" name="location" required>
                </div>
                
                <div class="btn-container">
                    <button type="button" class="btn-secondary" onclick="closeAddItemModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Adjust quantity modal functions
        function openAdjustModal(itemId, itemName, currentQuantity) {
            document.getElementById('item_id').value = itemId;
            document.getElementById('item_name').textContent = itemName;
            document.getElementById('current_quantity').value = currentQuantity;
            document.getElementById('quantity').value = currentQuantity;
            document.getElementById('adjustModal').style.display = 'block';
        }
        
        function closeAdjustModal() {
            document.getElementById('adjustModal').style.display = 'none';
        }
        
        // Add item modal functions
        function openAddItemModal() {
            document.getElementById('addItemModal').style.display = 'block';
        }
        
        function closeAddItemModal() {
            document.getElementById('addItemModal').style.display = 'none';
        }
        
        // Edit item modal function (would be implemented in a real app)
        function openEditModal(itemId) {
            alert("Edit functionality would open a modal for item ID: " + itemId);
            // In a real application, this would open a modal to edit the item
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('adjustModal')) {
                closeAdjustModal();
            }
            if (event.target == document.getElementById('addItemModal')) {
                closeAddItemModal();
            }
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

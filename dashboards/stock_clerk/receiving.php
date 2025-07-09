<?php
/**
 * Stock Clerk Receiving Management
 * This page allows stock clerks to receive and process incoming inventory
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

// Handle receiving actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'receive_shipment':
                // Process receiving a shipment
                $message = "Shipment received successfully.";
                $message_type = "success";
                break;
        }
    }
}

// Sample shipments data (in a real app, this would come from database)
$shipments = [
    [
        'id' => 'SHP-1001',
        'supplier' => 'Office Supplies Inc.',
        'expected_date' => '2025-06-26',
        'status' => 'pending',
        'items' => [
            ['name' => 'Printer Paper', 'quantity' => 50],
            ['name' => 'Stapler', 'quantity' => 10]
        ]
    ],
    [
        'id' => 'SHP-1002',
        'supplier' => 'Tech Solutions Ltd.',
        'expected_date' => '2025-06-27',
        'status' => 'in_transit',
        'items' => [
            ['name' => 'USB Flash Drive 32GB', 'quantity' => 25],
            ['name' => 'Toner Cartridge', 'quantity' => 5]
        ]
    ],
    [
        'id' => 'SHP-1003',
        'supplier' => 'Office Supplies Inc.',
        'expected_date' => '2025-06-24',
        'status' => 'received',
        'items' => [
            ['name' => 'Ballpoint Pens (Box)', 'quantity' => 20]
        ],
        'received_date' => '2025-06-24',
        'received_by' => 'John Doe'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receiving Management - Stock Clerk Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .shipments-table {
            width: 100%;
            border-collapse: collapse;
        }
        .shipments-table th, .shipments-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .shipments-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #344767;
        }
        .shipments-table tr:hover {
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
        .receive-btn {
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
        .status-in_transit {
            background-color: #5a8dee;
        }
        .status-received {
            background-color: #2dce89;
        }
        .shipment-items {
            margin-top: 10px;
            padding-left: 20px;
        }
        .shipment-items li {
            margin-bottom: 5px;
        }
        .item-verification {
            margin-top: 10px;
        }
        .item-verification li {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 4px;
        }
        .item-verification input[type="number"] {
            width: 80px;
            padding: 5px;
            margin-left: 10px;
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
                    <li class="active"><a href="receiving.php"><i class="fas fa-truck-loading"></i> Receiving</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Receiving Management</h1>
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
                <h2>Expected Shipments</h2>
                <table class="shipments-table">
                    <thead>
                        <tr>
                            <th>Shipment ID</th>
                            <th>Supplier</th>
                            <th>Expected Date</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $shipment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($shipment['id']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['supplier']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($shipment['expected_date']))); ?></td>
                                <td>
                                    <ul class="shipment-items">
                                        <?php foreach ($shipment['items'] as $item): ?>
                                            <li><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $shipment['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $shipment['status'])); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="#" class="view-btn" onclick="openViewModal('<?php echo $shipment['id']; ?>')"><i class="fas fa-eye"></i> View</a>
                                    <?php if ($shipment['status'] !== 'received'): ?>
                                        <a href="#" class="receive-btn" onclick="openReceiveModal('<?php echo $shipment['id']; ?>')"><i class="fas fa-check"></i> Receive</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>Recently Received</h2>
                <table class="shipments-table">
                    <thead>
                        <tr>
                            <th>Shipment ID</th>
                            <th>Supplier</th>
                            <th>Received Date</th>
                            <th>Received By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $shipment): ?>
                            <?php if ($shipment['status'] === 'received'): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($shipment['id']); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['supplier']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($shipment['received_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['received_by']); ?></td>
                                    <td class="action-buttons">
                                        <a href="#" class="view-btn" onclick="openViewModal('<?php echo $shipment['id']; ?>')"><i class="fas fa-eye"></i> View</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- View Shipment Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeViewModal()">&times;</span>
            <h2>Shipment Details - <span id="view_shipment_id"></span></h2>
            <div id="shipment_details">
                <!-- Shipment details will be populated here -->
            </div>
        </div>
    </div>
    
    <!-- Receive Shipment Modal -->
    <div id="receiveModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeReceiveModal()">&times;</span>
            <h2>Receive Shipment - <span id="receive_shipment_id"></span></h2>
            <form id="receiveForm" method="POST">
                <input type="hidden" name="action" value="receive_shipment">
                <input type="hidden" id="receive_shipment_id_input" name="shipment_id" value="">
                
                <div class="form-group">
                    <label>Please verify the received items:</label>
                    <ul class="item-verification" id="receive_items_list">
                        <!-- Items will be populated here -->
                    </ul>
                </div>
                
                <div class="form-group">
                    <label for="condition">Shipment Condition:</label>
                    <select id="condition" name="condition" required>
                        <option value="">Select Condition</option>
                        <option value="excellent">Excellent</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes:</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <div class="btn-container">
                    <button type="button" class="btn-secondary" onclick="closeReceiveModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Confirm Receipt</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Sample shipment data for JavaScript functions
        const shipmentData = <?php echo json_encode($shipments); ?>;
        
        // View shipment modal functions
        function openViewModal(shipmentId) {
            const shipment = shipmentData.find(s => s.id === shipmentId);
            if (!shipment) return;
            
            document.getElementById('view_shipment_id').textContent = shipment.id;
            
            let detailsHtml = `
                <p><strong>Supplier:</strong> ${shipment.supplier}</p>
                <p><strong>Expected Date:</strong> ${new Date(shipment.expected_date).toLocaleDateString()}</p>
                <p><strong>Status:</strong> <span class="status-badge status-${shipment.status}">${shipment.status.charAt(0).toUpperCase() + shipment.status.slice(1).replace('_', ' ')}</span></p>
                <p><strong>Items:</strong></p>
                <ul>
            `;
            
            shipment.items.forEach(item => {
                detailsHtml += `<li>${item.name} (x${item.quantity})</li>`;
            });
            
            detailsHtml += '</ul>';
            
            if (shipment.status === 'received') {
                detailsHtml += `
                    <p><strong>Received Date:</strong> ${new Date(shipment.received_date).toLocaleDateString()}</p>
                    <p><strong>Received By:</strong> ${shipment.received_by}</p>
                `;
            }
            
            document.getElementById('shipment_details').innerHTML = detailsHtml;
            document.getElementById('viewModal').style.display = 'block';
        }
        
        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }
        
        // Receive shipment modal functions
        function openReceiveModal(shipmentId) {
            const shipment = shipmentData.find(s => s.id === shipmentId);
            if (!shipment) return;
            
            document.getElementById('receive_shipment_id').textContent = shipment.id;
            document.getElementById('receive_shipment_id_input').value = shipment.id;
            
            let itemsHtml = '';
            shipment.items.forEach(item => {
                itemsHtml += `
                    <li>
                        <div><strong>${item.name}</strong> - Expected: ${item.quantity}</div>
                        <div>
                            <label for="received_${item.name.replace(/\s+/g, '_')}">Received Quantity:</label>
                            <input type="number" id="received_${item.name.replace(/\s+/g, '_')}" 
                                   name="received_items[${item.name}]" value="${item.quantity}" min="0" required>
                        </div>
                    </li>
                `;
            });
            
            document.getElementById('receive_items_list').innerHTML = itemsHtml;
            document.getElementById('receiveModal').style.display = 'block';
        }
        
        function closeReceiveModal() {
            document.getElementById('receiveModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('viewModal')) {
                closeViewModal();
            }
            if (event.target == document.getElementById('receiveModal')) {
                closeReceiveModal();
            }
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

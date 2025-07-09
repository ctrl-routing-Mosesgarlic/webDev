<?php
/**
 * Customer Support Page
 * This page allows customers to view and create support tickets
 */

// Include session management
require_once '../../session.php';
require_once '../../connection.php';

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

// Handle new ticket submission
$success_message = '';
$error_message = '';

if (isset($_POST['submit_ticket'])) {
    $subject = trim($_POST['subject']);
    $category = trim($_POST['category']);
    $message = trim($_POST['message']);
    $priority = trim($_POST['priority']);
    
    // Validate input
    if (empty($subject) || empty($message) || empty($category) || empty($priority)) {
        $error_message = "All fields are required.";
    } else {
        // In a real application, you would insert the ticket into the database
        // For demo purposes, we'll just show a success message
        $success_message = "Your support ticket has been submitted successfully. We'll get back to you soon.";
    }
}

// Sample tickets data (in a real app, this would come from database)
$tickets = [
    [
        'id' => 'TKT-2023-1234',
        'subject' => 'Order delivery delayed',
        'date' => '2025-06-20',
        'status' => 'open',
        'priority' => 'high',
        'category' => 'Order Issue',
        'last_update' => '2025-06-20'
    ],
    [
        'id' => 'TKT-2023-1156',
        'subject' => 'Wrong item received',
        'date' => '2025-06-15',
        'status' => 'in-progress',
        'priority' => 'medium',
        'category' => 'Order Issue',
        'last_update' => '2025-06-18'
    ],
    [
        'id' => 'TKT-2023-1089',
        'subject' => 'Account login issues',
        'date' => '2025-06-10',
        'status' => 'closed',
        'priority' => 'low',
        'category' => 'Technical Support',
        'last_update' => '2025-06-12'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Customer Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            color: #8898aa;
            font-weight: 500;
        }
        .tab.active {
            border-bottom-color: #4a6fdc;
            color: #344767;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tickets-table {
            width: 100%;
            border-collapse: collapse;
        }
        .tickets-table th, .tickets-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .tickets-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #344767;
        }
        .tickets-table tr:hover {
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
        .open {
            background-color: #5a8dee;
        }
        .in-progress {
            background-color: #f5b74f;
        }
        .closed {
            background-color: #2dce89;
        }
        .priority-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            display: inline-block;
        }
        .high {
            background-color: #f5365c;
        }
        .medium {
            background-color: #f5b74f;
        }
        .low {
            background-color: #2dce89;
        }
        .view-btn {
            padding: 6px 10px;
            background-color: #5a8dee;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #344767;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            font-size: 1rem;
            background-color: white;
        }
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
        }
        .btn-primary {
            background-color: #4a6fdc;
            color: white;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .faq-item {
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }
        .faq-question {
            padding: 15px;
            background-color: #f8f9fa;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .faq-answer {
            padding: 15px;
            border-top: 1px solid #e9ecef;
            display: none;
        }
        .faq-answer.show {
            display: block;
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
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                    <li><a href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li class="active"><a href="support.php"><i class="fas fa-comment-alt"></i> Support</a></li>
                    <li><a href="account.php"><i class="fas fa-user-circle"></i> My Account</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>Customer Support</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <!-- Alerts for form submission -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Tabs navigation -->
            <div class="tabs">
                <div class="tab active" data-tab="my-tickets">My Tickets</div>
                <div class="tab" data-tab="new-ticket">Create New Ticket</div>
                <div class="tab" data-tab="faq">FAQ</div>
            </div>
            
            <!-- My Tickets Tab -->
            <div class="tab-content active" id="my-tickets">
                <div class="card">
                    <table class="tickets-table">
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Last Update</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($ticket['date'])); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['category']); ?></td>
                                    <td>
                                        <span class="priority-badge <?php echo $ticket['priority']; ?>">
                                            <?php echo ucfirst($ticket['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $ticket['status']; ?>">
                                            <?php echo ucfirst(str_replace('-', ' ', $ticket['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($ticket['last_update'])); ?></td>
                                    <td><a href="#" class="view-btn">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- New Ticket Tab -->
            <div class="tab-content" id="new-ticket">
                <div class="card">
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control" placeholder="Brief description of your issue">
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-select">
                                <option value="">Select a category</option>
                                <option value="Order Issue">Order Issue</option>
                                <option value="Product Inquiry">Product Inquiry</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Billing">Billing</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select id="priority" name="priority" class="form-select">
                                <option value="">Select priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" class="form-control" placeholder="Please describe your issue in detail"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="submit_ticket" class="btn btn-primary">Submit Ticket</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- FAQ Tab -->
            <div class="tab-content" id="faq">
                <div class="card">
                    <div class="faq-item">
                        <div class="faq-question">
                            How do I track my order?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            You can track your order by going to the "My Orders" section in your dashboard. Click on the "Track" button next to your order to see real-time shipping updates.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            How can I return an item?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            To return an item, go to "My Orders" and find the order containing the item you wish to return. Click on "View" and then select "Return Item". Follow the instructions to complete your return request.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            What payment methods do you accept?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers. For corporate accounts, we also offer invoicing options.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            How long does shipping take?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Standard shipping typically takes 3-5 business days. Express shipping is available for an additional fee and delivers within 1-2 business days. International shipping times vary by destination.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            How do I change my account information?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            You can update your account information by going to the "My Account" section in your dashboard. There you can edit your personal details, change your password, and update your shipping and billing information.
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to current tab and content
                    this.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // FAQ toggle functionality
            const faqQuestions = document.querySelectorAll('.faq-question');
            
            faqQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    const answer = this.nextElementSibling;
                    const icon = this.querySelector('i');
                    
                    if (answer.classList.contains('show')) {
                        answer.classList.remove('show');
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    } else {
                        answer.classList.add('show');
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>

<?php
/**
 * Customer Account Page
 * This page allows customers to manage their account settings
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
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle account update logic here
    if (isset($_POST['update_profile'])) {
        // Process profile update
        $new_fullname = $_POST['fullname'] ?? '';
        $new_email = $_POST['email'] ?? '';
        
        // Basic validation
        if (!empty($new_fullname) && !empty($new_email)) {
            // Update user in database
            $update_sql = "UPDATE users SET fullname = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssi", $new_fullname, $new_email, $user_id);
            
            if ($stmt->execute()) {
                // Update session variables
                $_SESSION['fullname'] = $new_fullname;
                $_SESSION['email'] = $new_email;
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Error updating profile: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error_message = "Please fill in all required fields.";
        }
    } elseif (isset($_POST['change_password'])) {
        // Handle password change
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (!empty($current_password) && !empty($new_password) && $new_password === $confirm_password) {
            // Verify current password
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($current_password, $user['password'])) {
                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "Password updated successfully!";
                    } else {
                        $error_message = "Error updating password.";
                    }
                    $update_stmt->close();
                } else {
                    $error_message = "Current password is incorrect.";
                }
            }
            $stmt->close();
        } else {
            $error_message = "Please fill in all password fields and ensure they match.";
        }
    }
}

// Get updated user data
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

{% extends "../../templates/base.html" %}

{% block head %}
    <link rel="stylesheet" href="/static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>My Account - W&SM System</title>
    <style>
        .account-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        .account-section {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .account-section h2 {
            margin-top: 0;
            color: #2d3748;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #4a5568;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 1rem;
        }
        .btn {
            background: #5a8dee;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
        }
        .btn:hover {
            background: #4a7de0;
        }
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background: #e6fffa;
            color: #2c7a7b;
            border: 1px solid #b2f5ea;
        }
        .alert-error {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #fed7d7;
        }
    </style>
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
                <li><a href="../customer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                <li class="active"><a href="account.php"><i class="fas fa-user"></i> My Account</a></li>
                <li><a href="support.php"><i class="fas fa-comment-alt"></i> Support</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main class="content">
        <header class="content-header">
            <h1>My Account</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
            </div>
        </header>

        <div class="account-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="account-section">
                <h2>Profile Information</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" 
                               value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn">Update Profile</button>
                </form>
            </div>

            <div class="account-section">
                <h2>Change Password</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn">Change Password</button>
                </form>
            </div>
        </div>
    </main>
</div>
{% endblock %}

<?php
// Close database connection
if (isset($db)) {
    $db->closeConnection();
}
?>

<?php
/**
 * Login Processing File
 * This file processes the login form data, authenticates the user, and sets up sessions
 */

// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and session management
require_once 'connection.php';
require_once 'session.php';

// Create database connection
$db = new database();
$conn = $db->getConnection();

if (!$conn) {
    $_SESSION['error'] = "Database connection failed";
    header("Location: templates/auth/sign-in.html");
    exit();
}

// Check if the form is submitted
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and validate form data
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate input (basic validation)
    if (empty($email) || empty($password)) {
        echo "<div style='max-width: 600px; margin: 50px auto; padding: 20px; background-color: #ffebee; border-left: 5px solid #f44336; color: #b71c1c;'>";
        echo "<h3>Error</h3>";
        echo "<p>Email and password are required!</p>";
        echo "<p><a href='javascript:history.back()'>Go back</a></p>";
        echo "</div>";
        exit();
    }
    
    // SQL query to find user
    $sql = "SELECT id, fullname, email, password, role FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Regenerate session ID for security
            regenerateSession();
            
            // Set session variables
            setSessionVar('user_id', $user['id']);
            setSessionVar('fullname', $user['fullname']);
            setSessionVar('email', $user['email']);
            setSessionVar('role', $user['role']);
            setSessionVar('logged_in', true);
            setSessionVar('last_activity', time());
            
            // Handle "Remember Me" functionality
            if ($remember) {
                // Generate a secure token
                $token = bin2hex(random_bytes(32));
                $user_id = $user['id'];
                
                // Store token in database (you would need a remember_tokens table)
                $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                $token_hash = password_hash($token, PASSWORD_DEFAULT);
                
                // Delete any existing tokens for this user
                $delete_sql = "DELETE FROM remember_tokens WHERE user_id = $user_id";
                mysqli_query($conn, $delete_sql);
                
                // Insert new token
                $insert_sql = "INSERT INTO remember_tokens (user_id, token, expiry) VALUES ($user_id, '$token_hash', '$expiry')";
                
                // Only set cookie if token was successfully stored
                if (mysqli_query($conn, $insert_sql)) {
                    setRememberMeCookie($user_id, $token);
                }
            }
            
            // Redirect to appropriate dashboard based on role
            $role = strtolower($user['role']);
            switch ($role) {
                case 'admin':
                    header("Location: /dashboards/admin/dashboard.php");
                    break;
                case 'supplier':
                    header("Location: /dashboards/supplier/dashboard.php");
                    break;
                case 'staff':
                    header("Location: /dashboards/staff/dashboard.php");
                    break;
                case 'customer':
                default:
                    header("Location: /dashboards/customer/customer_dashboard.php");
            }
            exit();
        } else {
            // Invalid password
            $_SESSION['error'] = "Invalid email or password";
            header("Location: /templates/auth/sign-in.html");
            exit();
        }
    } else {
        // User not found
        $_SESSION['error'] = "No account found with that email";
        header("Location: /templates/auth/sign-in.html");
        exit();
    }
}
?>

<?php
/**
 * Registration Processing File
 * This file processes the registration form data and inserts it into the database
 */

// Include database connection and session management
require_once 'connection.php';
require_once 'session.php';

// Create database connection
$db = new database();
$conn = $db->getConnection();

if (!$conn) {
    die("Database connection failed");
}

// Check if the form is submitted
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and validate form data
    $fullname = isset($_POST['fullname']) ? mysqli_real_escape_string($conn, $_POST['fullname']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $password = $_POST['password'] ?? ''; // Will be hashed
    $gender = isset($_POST['gender']) ? mysqli_real_escape_string($conn, $_POST['gender']) : '';
    $role = isset($_POST['role']) ? mysqli_real_escape_string($conn, $_POST['role']) : 'customer'; // Default to 'customer' if not provided
    
    // Validate input (basic validation)
    if (empty($fullname) || empty($email) || empty($password) || empty($gender)) {
        echo "<div style='max-width: 600px; margin: 50px auto; padding: 20px; background-color: #ffebee; border-left: 5px solid #f44336; color: #b71c1c;'>";
        echo "<h3>Error</h3>";
        echo "<p>All fields are required!</p>";
        echo "<p><a href='javascript:history.back()'>Go back</a></p>";
        echo "</div>";
        exit();
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div style='max-width: 600px; margin: 50px auto; padding: 20px; background-color: #ffebee; border-left: 5px solid #f44336; color: #b71c1c;'>";
        echo "<h3>Error</h3>";
        echo "<p>Invalid email format!</p>";
        echo "<p><a href='javascript:history.back()'>Go back</a></p>";
        echo "</div>";
        exit();
    }
    
    // Password hashing for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Registration date
    $registration_date = date('Y-m-d H:i:s');
    
    // SQL query to insert data
    $sql = "INSERT INTO users (fullname, email, password, gender, role, registration_date) 
            VALUES ('$fullname', '$email', '$hashed_password', '$gender', '$role', '$registration_date')";
    
    // Execute query and check if successful
    if (mysqli_query($conn, $sql)) {
        // Show success message and redirect after delay
        echo "<div style='max-width: 600px; margin: 50px auto; padding: 20px; background-color: #e8f5e9; border-left: 5px solid #4caf50; color: #2e7d32;'>";
        echo "<h3>Registration Successful!</h3>";
        echo "<p>Your account has been created successfully.</p>";
        echo "<p>You will be redirected to the login page in 3 seconds...</p>";
        echo "<p>If not redirected, <a href='templates/auth/sign-in.html'>click here</a> to login.</p>";
        echo "</div>";
        echo "<script>setTimeout(function() { window.location.href = 'templates/auth/sign-in.html'; }, 3000);</script>";
        exit();
    } else {
        // Check for duplicate email error
        if (mysqli_errno($conn) == 1062) { // 1062 is the error code for duplicate entry
            echo "<div style='max-width: 600px; margin: 50px auto; padding: 20px; background-color: #ffebee; border-left: 5px solid #f44336; color: #b71c1c;'>";
            echo "<h3>Error</h3>";
            echo "<p>Email already exists. Please use a different email address.</p>";
            echo "<p><a href='javascript:history.back()'>Go back</a></p>";
            echo "</div>";
        } else {
            echo "<div style='max-width: 600px; margin: 50px auto; padding: 20px; background-color: #ffebee; border-left: 5px solid #f44336; color: #b71c1c;'>";
            echo "<h3>Database Error</h3>";
            echo "<p>" . mysqli_error($conn) . "</p>";
            echo "<p><a href='javascript:history.back()'>Go back</a></p>";
            echo "</div>";
        }
    }
}
?>

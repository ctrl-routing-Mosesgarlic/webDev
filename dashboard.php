<?php
/**
 * Dashboard Router
 * This file routes users to their appropriate dashboard based on their role
 */

// Include session management
require_once 'session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page if not logged in
    header("Location: templates/auth/sign-in.html");
    exit();
}

// Get user role from session
$role = getSessionVar('role', '');

// Route to appropriate dashboard based on role
switch ($role) {
    case 'admin':
        header("Location: dashboards/admin_dashboard.php");
        break;
    case 'manager':
        header("Location: dashboards/manager_dashboard.php");
        break;
    case 'employee':
        header("Location: dashboards/employee_dashboard.php");
        break;
    case 'customer':
        header("Location: dashboards/customer_dashboard.php");
        break;
    default:
        // Default dashboard for unknown roles
        header("Location: dashboards/user_dashboard.php");
        break;
}
exit();
?>

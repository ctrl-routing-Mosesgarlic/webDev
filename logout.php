<?php
/**
 * Logout Script
 * This file handles user logout by destroying the session and redirecting to login page
 */

// Include session management
require_once 'session.php';

// Clear remember me cookie if exists
clearRememberMeCookie();

// Destroy session
destroySession();

// Redirect to login page
header("Location: templates/auth/sign-in.html");
exit();
?>

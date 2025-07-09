<?php
/**
 * Session Management File
 * This file handles session initialization, validation, and cleanup
 */

// Start or resume session
if (session_status() == PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

/**
 * Sets a session variable
 * 
 * @param string $key Session key
 * @param mixed $value Session value
 */
function setSessionVar($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Gets a session variable
 * 
 * @param string $key Session key
 * @param mixed $default Default value if session key doesn't exist
 * @return mixed Session value or default
 */
function getSessionVar($key, $default = null) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Checks if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Sets a remember me cookie
 * 
 * @param int $user_id User ID
 * @param string $token Remember me token
 * @param int $days Number of days to remember (default: 30)
 */
function setRememberMeCookie($user_id, $token, $days = 30) {
    setcookie(
        'remember_me',
        json_encode(['user_id' => $user_id, 'token' => $token]),
        time() + (86400 * $days), // 86400 = 1 day
        '/',
        '',
        false, // Set to true if using HTTPS
        true // HTTP only
    );
}

/**
 * Gets remember me cookie data
 * 
 * @return array|null Cookie data or null if cookie doesn't exist
 */
function getRememberMeCookie() {
    if (isset($_COOKIE['remember_me'])) {
        return json_decode($_COOKIE['remember_me'], true);
    }
    return null;
}

/**
 * Clears remember me cookie
 */
function clearRememberMeCookie() {
    setcookie('remember_me', '', time() - 3600, '/', '', false, true);
}

/**
 * Regenerates session ID to prevent session fixation attacks
 */
function regenerateSession() {
    session_regenerate_id(true);
}

/**
 * Destroys session and cleans up
 */
function destroySession() {
    $_SESSION = array();
    
    // If a session cookie is used, clear it
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}
?>

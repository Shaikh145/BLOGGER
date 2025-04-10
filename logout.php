<?php
// logout.php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Clear any other cookies you might have set
setcookie('remember_me', '', time() - 3600, '/');
setcookie('user_preferences', '', time() - 3600, '/');

// Optional: Log the logout action
if (isset($_SESSION['username'])) {
    error_log("User " . $_SESSION['username'] . " logged out at " . date('Y-m-d H:i:s'));
}

// Redirect to login page with logged out message
header('Location: login.php?msg=logged_out');
exit();

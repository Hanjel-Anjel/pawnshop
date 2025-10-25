<?php
session_start();  // Start the session to access session data

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Optionally, delete session cookie if you want to completely clear the session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page
header('Location: index.php');
exit();
?>

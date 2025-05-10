<?php
session_start();
session_unset();      // Unset all session variables
session_destroy();    // Destroy the session

// Optional: Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login or homepage
header("Location: ../home.php");
exit();
?>

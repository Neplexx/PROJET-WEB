<?php
session_start();

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

setcookie('user_auth', '', time() - 3600, '/');

session_unset(); 
session_destroy(); 
session_write_close(); 

header("Location: login.php");
exit();
?>

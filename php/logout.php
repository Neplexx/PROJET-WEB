<?php
session_start();

// Détruire toutes les données de session
$_SESSION = array();

// Supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Supprimer le cookie personnalisé
setcookie('user_auth', '', time() - 3600, '/');

session_unset(); // Supprime toutes les variables de session
session_destroy(); // Détruit la session
session_write_close(); // Force l'écriture des données

// Rediriger vers la page de connexion
header("Location: login.php");
exit();
?>

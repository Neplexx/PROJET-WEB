<?php
session_start();
require_once('verification.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}
$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'ctmdata';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        switch ($user['user_type']) {
            case 'client':
                header("Location: espace/espace_client.php");
                break;
            case 'graphiste':
                header("Location: espace/espace_graphiste.php");
                break;
            case 'monteur':
                header("Location: espace/espace_monteur.php");
                break;
            case 'employeur':
                header("Location: espace/espace_employeur.php");
                break;
            case 'admin':
                header("Location: espace/espace_admin.php");
                break;
            case 'manager':
                header("Location: espace/espace_manager.php");
                break;
            case 'développeur':
                header("Location: espace/espace_developpeur.php");
                break;
            case 'beatmaker':
                header("Location: espace/espace_beatmaker.php");
                break;
            default:
                header("Location: accueil.php");
        }
        exit();
    }
} catch(PDOException $e) {
    header("Location: accueil.php");
    exit();
}

header("Location: accueil.php");
?>

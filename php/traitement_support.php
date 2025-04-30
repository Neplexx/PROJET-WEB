<?php
session_start();
require_once('verification.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'ctmdata';
$charset = 'utf8mb4';

$dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($message)) {
        $_SESSION['support_message'] = [
            'type' => 'error',
            'text' => 'Le message est obligatoire.'
        ];
        header("Location: support.php");
        exit();
    }
    $stmt = $pdo->query("SELECT user_id FROM users WHERE user_type = 'admin' LIMIT 1");
    $admin = $stmt->fetch();
    
    if (!$admin) {
        $_SESSION['support_message'] = [
            'type' => 'error',
            'text' => 'Aucun administrateur disponible pour recevoir votre message.'
        ];
        header("Location: support.php");
        exit();
    }
    
    $admin_id = $admin['user_id'];
    $content = "Nouveau message de support:\n\nDe: $prenom $nom ($email)\n\nMessage:\n$message";
    $query = "INSERT INTO notifications (user_id, content, is_read, notification_type, related_id, created_at) 
              VALUES (?, ?, 0, 'support', ?, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$admin_id, $content, $user_id]);
    
    $_SESSION['support_message'] = [
        'type' => 'success',
        'text' => 'Votre message a bien été envoyé à notre équipe de support.'
    ];
    header("Location: support.php");
    exit();
    
} catch (\PDOException $e) {
    $_SESSION['support_message'] = [
        'type' => 'error',
        'text' => 'Une erreur est survenue lors de l\'envoi de votre message: ' . $e->getMessage()
    ];
    header("Location: support.php");
    exit();
}
?>
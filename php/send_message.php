<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Vous devez être connecté pour envoyer un message.");
}

$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'ctmdata';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['content'])) {
        $sender_id = $_SESSION['user_id'];
        $receiver_id = $_POST['receiver_id'];
        $content = $_POST['content'];

        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (:sender_id, :receiver_id, :content)");
        $stmt->execute([
            ':sender_id' => $sender_id,
            ':receiver_id' => $receiver_id,
            ':content' => $content
        ]);

        //header("Location: employees.php?success=1");
        exit();
    } else {
        var_dump($_POST);
        die("Données invalides.");
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
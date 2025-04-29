<?php
session_start();
$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'ctmdata';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_content'], $_POST['receiver_id'])) {
        $sender_id = $_SESSION['user_id'];
        $receiver_id = $_POST['receiver_id'];
        $reply_content = $_POST['reply_content'];

        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, content, sent_at) 
            VALUES (:sender_id, :receiver_id, :content, NOW())
        ");
        $stmt->execute([
            ':sender_id' => $sender_id,
            ':receiver_id' => $receiver_id,
            ':content' => $reply_content
        ]);

        header("Location: notifications.php?reply_success=1");
        exit();
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}
?>
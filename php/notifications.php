<?php
$servername = 'localhost'; 
$username = 'root'; 
$password = 'root'; 
$dbname = 'ctmdata';
session_start();


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        SELECT m.message_id, m.content, m.sent_at AS created_at, 
               u.first_name, u.last_name 
        FROM messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE m.receiver_id = :user_id
        ORDER BY m.sent_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | CTM</title>
    <link rel="stylesheet" href="../styles/style_notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f7fa;
    color: #333;
    }

    .notifications-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }

    .notifications-header {
        text-align: center;
        margin-bottom: 20px;
    }

    .notifications-header h1 {
        color: #2c3e50;
        margin: 0;
    }

    .notifications-header p {
        color: #7f8c8d;
        margin: 5px 0 0;
    }

    .notifications-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .notification-item {
        background-color: #ffffff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }

    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .notification-header strong {
        color: #2c3e50;
    }

    .notification-date {
        color: #95a5a6;
        font-size: 0.9rem;
    }

    .notification-content {
        margin: 0;
        color: #34495e;
    }

    .no-notifications {
        text-align: center;
        color: #95a5a6;
    }

    .no-notifications i {
        font-size: 50px;
        margin-bottom: 10px;
        color: #bdc3c7;
    }
    .reply-form {
        margin-top: 10px;
    }

    .reply-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        resize: none;
        font-family: inherit;
        font-size: 1rem;
    }

    .reply-btn {
        margin-top: 5px;
        background-color: #3498db;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .reply-btn:hover {
        background-color: #2980b9;
    }
    .success-message {
    background-color: #2ecc71; 
    color: white;
    padding: 15px;
    margin: 20px auto;
    border-radius: 8px;
    text-align: center;
    font-size: 16px;
    font-weight: bold;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    max-width: 600px;
    animation: fadeOut 5s forwards; 
}

.success-message i {
    font-size: 20px;
}
@keyframes fadeOut {
    0% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        opacity: 0;
        display: none;
    }
}
</style>
</head>
<body>
    <?php if (isset($_GET['reply_success']) && $_GET['reply_success'] == 1): ?>
        <div class="success-message">Votre réponse a été envoyée avec succès.</div>
    <?php endif; ?>
    <div class="notifications-container">
        <header class="notifications-header">
            <h1>Vos Notifications</h1>
            <p>Consultez vos messages reçus</p>
        </header>

        <main class="notifications-main">
            <?php if (!empty($messages)): ?>
                <ul class="notifications-list">
                    <?php foreach ($messages as $message): ?>
                        <li class="notification-item">
                            <div class="notification-header">
                                <strong><?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?></strong>
                                <span class="notification-date"><?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></span>
                            </div>
                            <p class="notification-content"><?php echo htmlspecialchars($message['content']); ?></p>

                            <form method="POST" action="rep_message.php" class="reply-form">
                                <input type="hidden" name="receiver_id" value="<?php echo isset($message['sender_id']) ? htmlspecialchars($message['sender_id']) : 0; ?>">
                                <textarea name="reply_content" class="form-control" placeholder="Écrivez votre réponse..." required></textarea>
                                <button type="submit" class="reply-btn">Envoyer</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="no-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <h3>Aucune notification</h3>
                    <p>Vous n'avez reçu aucun message pour le moment.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
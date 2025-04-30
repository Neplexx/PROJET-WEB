<!DOCTYPE html>
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
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT first_name, last_name, email FROM users WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die("Utilisateur non trouvé dans la base de données");
    }
    
} catch (\PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

if (isset($_SESSION['support_message'])) {
    $message = $_SESSION['support_message'];
    unset($_SESSION['support_message']);
}
?>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page de Support pour CTM">
    <meta name="keywords" content="Support, CTM, assistance">
    <meta name="author" content="JAIDANE Khaled">
    <link rel="icon" type="image/x-icon" href="../pictures/logo.ico">
    <link rel="stylesheet" type="text/css" href="../styles/style_support.css">
    <title>Support | CTM</title>
</head>
<style>
input[readonly], textarea[readonly] {
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    color: #777;
    cursor: not-allowed;
}
</style>
<body>
    <header>
        <a id="index" href="../index.html"><img id="indexImage" src="../pictures/logorond.png" alt="Logo CTM"></a>
        <a id="espaceClient" href="../php/espclient.php"><img id="espaceClientImage" src="../pictures/photoDeProfil.png" alt="Photo de profil"></a>
    </header>
    <nav>
        <ul>
            <li><a class="pageNonActive" href="accueil.php">Accueil</a></li>
            <li><a class="pageNonActive" href="recherche.php">Recherche</a></li>
        </ul>
    </nav>
    <main>
        <h1>Support</h1>
        <p>Bienvenue sur la page de Support. Si vous avez une question ou un problème, n'hésitez pas à nous contacter via le formulaire ci-dessous. Nous sommes là également pour vous éclairer !</p>
        <section>
            <form id="supportForm" method="POST" action="traitement_support.php">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="message">Message :</label>
                    <textarea id="message" name="message" rows="5" placeholder="Décrivez votre problème ici..." required></textarea>
                </div>
                <button type="submit">Envoyer</button>
            </form>
        </section>
        <p class="spacer">ㅤ</p>
    </main>
    <footer>
        <div id="conteneurfooter">
            <a id="imagediscord" href="https://discord.gg/q6Fd6tJCaY" target="_blank">
                <img src="../pictures/discord.png" alt="Logo Discord">
            </a>
            <a id="imagegithub" href="https://github.com/Neplexx/PROJET-WEB" target="_blank">
                <img src="../pictures/github.png" alt="Logo GitHub">
            </a>
        </div>
    </footer>
</body>
</html>

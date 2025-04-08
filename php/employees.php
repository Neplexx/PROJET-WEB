<?php
// Connexion à la base de données et traitement des données
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ctm_platform";

$results = [];
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$keyword = str_replace('+', ' ', $keyword);

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!empty($keyword)) {
        $sql = "SELECT u.first_name, u.last_name, u.email, u.phone, u.profile_picture, u.bio, u.user_type, 
                       e.years_experience, e.daily_rate, e.availability, 
                       emp.company_name, emp.company_size, emp.industry
                FROM users u
                LEFT JOIN editors e ON u.user_id = e.user_id
                LEFT JOIN employers emp ON u.user_id = emp.user_id
                WHERE CONCAT(u.first_name, ' ', u.last_name) LIKE :keyword";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche CTM</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Recherche de profils CTM</h1>
        </header>
        
        <main class="main-content">
            <section class="search-section">
                <form action="" method="get" class="search-form">
                    <input type="text" name="keyword" placeholder="Entrez un nom..." 
                           value="<?php echo htmlspecialchars($keyword); ?>" class="search-input">
                    <button type="submit" class="search-button">Rechercher</button>
                </form>
            </section>
            
            <section class="results-section">
                <?php if (!empty($keyword)): ?>
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $row): ?>
                            <div class="profile-card">
                                <?php if (!empty($row['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Photo de profil" class="profile-pic">
                                <?php endif; ?>
                                
                                <div class="profile-info">
                                    <h2><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h2>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                                    <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($row['phone']); ?></p>
                                    <p><strong>Bio:</strong> <?php echo htmlspecialchars($row['bio']); ?></p>
                                    <p><strong>Type:</strong> <?php echo htmlspecialchars($row['user_type']); ?></p>
                                    
                                    <?php if ($row['user_type'] === 'monteur'): ?>
                                        <div class="editor-details">
                                            <h3>Détails Monteur</h3>
                                            <p><strong>Expérience:</strong> <?php echo htmlspecialchars($row['years_experience']); ?> ans</p>
                                            <p><strong>Taux journalier:</strong> <?php echo htmlspecialchars($row['daily_rate']); ?>€</p>
                                            <p><strong>Disponibilité:</strong> <?php echo htmlspecialchars($row['availability']); ?></p>
                                        </div>
                                    <?php elseif ($row['user_type'] === 'employeur'): ?>
                                        <div class="employer-details">
                                            <h3>Détails Employeur</h3>
                                            <p><strong>Entreprise:</strong> <?php echo htmlspecialchars($row['company_name']); ?></p>
                                            <p><strong>Taille:</strong> <?php echo htmlspecialchars($row['company_size']); ?></p>
                                            <p><strong>Secteur:</strong> <?php echo htmlspecialchars($row['industry']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-results">Aucun résultat trouvé pour "<?php echo htmlspecialchars($keyword); ?>"</p>
                    <?php endif; ?>
                <?php elseif (empty($keyword) && isset($_GET['keyword'])): ?>
                    <p class="no-keyword">Veuillez entrer un terme de recherche</p>
                <?php endif; ?>
            </section>
        </main>
        
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> CTM Data. Tous droits réservés.</p>
        </footer>
    </div>
</body>
</html>
<?php
// Fermer la connexion
$conn = null;
?>
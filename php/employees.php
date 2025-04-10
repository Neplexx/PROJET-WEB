<?php
$results = [];
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$keyword = str_replace('+', ' ', $keyword);

try {
    $servername ='localhost'; 
    $username ='root'; 
    $password ='root'; 
    $dbname='ctmdata';

try{
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
}
catch(Exception $e){
    die('Erreur : ' . $e->getMessage());
}

    if (!empty($keyword)) {
        $sql = "SELECT u.first_name, u.last_name, u.email, u.phone, u.profile_picture, u.bio, u.user_type, 
                       e.years_experience, e.daily_rate, e.availability, 
                       emp.company_name, emp.company_size, emp.industry
                FROM users u
                LEFT JOIN editors e ON u.user_id = e.user_id
                LEFT JOIN employers emp ON u.user_id = emp.user_id
                WHERE CONCAT(u.first_name, ' ', u.last_name) LIKE :keyword COLLATE utf8_general_ci";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../styles/style_employees.css">
</head>
<body>
    <div class="search-container">
        <header class="search-header">
            <a href="../html/recherche.html" class="logo-link">
                <img src="../pictures/logorond.png" alt="Logo CTM" class="logo">
            </a>
            <h1>Trouvez le professionnel idéal</h1>
            <p>Recherchez parmi nos monteurs, graphistes et autres experts</p>
        </header>
        
        <main class="search-main">
            <section class="search-box">
                <form action="" method="get" class="search-form">
                    <div class="search-input-group">
                        <input type="text" name="keyword" placeholder="Rechercher par nom..." 
                               value="<?php echo htmlspecialchars($keyword); ?>" class="search-input">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </div>
                    <div class="search-filters">
                        <div class="filter-group">
                            <label for="user_type">Type :</label>
                            <select id="user_type" name="user_type" class="filter-select">
                                <option value="">Tous types</option>
                                <option value="monteur">Monteur</option>
                                <option value="graphiste">Graphiste</option>
                                <option value="manager">Manager</option>
                                <option value="développeur">Développeur</option>
                                <option value="beatmaker">Beatmaker</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="availability">Disponibilité :</label>
                            <select id="availability" name="availability" class="filter-select">
                                <option value="">Toutes</option>
                                <option value="disponible">Disponible</option>
                                <option value="occupé">Occupé</option>
                            </select>
                        </div>
                    </div>
                </form>
            </section>
            
            <section class="results-section">
                <?php if (!empty($keyword)): ?>
                    <div class="results-header">
                        <h2>Résultats pour "<?php echo htmlspecialchars($keyword); ?>"</h2>
                        <span class="results-count"><?php echo count($results); ?> résultat(s)</span>
                    </div>
                    
                    <?php if (!empty($results)): ?>
                        <div class="profiles-grid">
                            <?php foreach ($results as $row): ?>
                                <article class="profile-card">
                                    <div class="profile-header">
                                        <?php if (!empty($row['profile_picture'])): ?>
                                            <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Photo de profil" class="profile-pic">
                                        <?php else: ?>
                                            <div class="profile-pic default-pic">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="profile-meta">
                                            <h3><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h3>
                                            <span class="profile-type <?php echo htmlspecialchars($row['user_type']); ?>">
                                                <?php echo htmlspecialchars($row['user_type']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="profile-body">
                                        <div class="profile-info">
                                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></p>
                                            <?php if (!empty($row['phone'])): ?>
                                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phone']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($row['bio'])): ?>
                                                <p class="profile-bio"><?php echo htmlspecialchars($row['bio']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($row['user_type'] === 'monteur'): ?>
                                            <div class="profile-details">
                                                <h4><i class="fas fa-chart-line"></i> Spécificités</h4>
                                                <ul class="details-list">
                                                    <li><strong>Expérience :</strong> <?php echo htmlspecialchars($row['years_experience']); ?> ans</li>
                                                    <li><strong>Taux journalier :</strong> <?php echo htmlspecialchars($row['daily_rate']); ?>€</li>
                                                    <li><strong>Disponibilité :</strong> 
                                                        <span class="availability <?php echo htmlspecialchars($row['availability']); ?>">
                                                            <?php echo htmlspecialchars($row['availability']); ?>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php elseif ($row['user_type'] === 'employeur'): ?>
                                            <div class="profile-details">
                                                <h4><i class="fas fa-building"></i> Entreprise</h4>
                                                <ul class="details-list">
                                                    <li><strong>Nom :</strong> <?php echo htmlspecialchars($row['company_name']); ?></li>
                                                    <li><strong>Taille :</strong> <?php echo htmlspecialchars($row['company_size']); ?></li>
                                                    <li><strong>Secteur :</strong> <?php echo htmlspecialchars($row['industry']); ?></li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="profile-footer">
                                        <button class="btn btn-contact">
                                            <i class="fas fa-envelope"></i> Contacter
                                        </button>
                                            <a href="profile.php?id=<?php echo $row['user_id']; ?>" class="btn btn-view">
                                                <i class="fas fa-eye"></i> Voir le profil
                                            </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <i class="fas fa-search-minus"></i>
                            <h3>Aucun résultat trouvé</h3>
                            <p>Essayez avec d'autres termes de recherche ou élargissez vos critères.</p>
                        </div>
                    <?php endif; ?>
                <?php elseif (empty($keyword) && isset($_GET['keyword'])): ?>
                    <div class="no-keyword">
                        <i class="fas fa-search"></i>
                        <h3>Veuillez entrer un terme de recherche</h3>
                        <p>Utilisez le champ ci-dessus pour trouver des professionnels.</p>
                    </div>
                <?php else: ?>
                    <div class="search-prompt">
                        <i class="fas fa-users"></i>
                        <h3>Recherchez parmi nos professionnels</h3>
                        <p>Entrez un nom ou utilisez les filtres pour affiner votre recherche.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
        
        <footer class="search-footer">
            <div class="footer-content">
                <p>&copy; <?php echo date('Y'); ?> CTM. Tous droits réservés.</p>
                <div class="footer-links">
                    <a href="../html/support.html"><i class="fas fa-question-circle"></i> Aide</a>
                    <a href="../html/conditions.html"><i class="fas fa-file-alt"></i> Conditions</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
<?php
$conn = null;
?>

<?php
$results = [];
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$keyword = str_replace('+', ' ', $keyword);
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';
$availability = isset($_GET['availability']) ? $_GET['availability'] : '';

try {
    $servername = 'localhost'; 
    $username = 'root'; 
    $password = 'root'; 
    $dbname = 'ctmdata';

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    } catch(Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }

    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone, u.profile_picture, u.bio, u.user_type, 
                   e.years_experience, e.daily_rate, e.availability, 
                   emp.company_name, emp.company_size, emp.industry
            FROM users u
            LEFT JOIN editors e ON u.user_id = e.user_id
            LEFT JOIN employers emp ON u.user_id = emp.user_id
            WHERE 1=1"; 

    $params = [];

    if (!empty($keyword)) {
        $sql .= " AND CONCAT(u.first_name, ' ', u.last_name) LIKE :keyword COLLATE utf8_general_ci";
        $params[':keyword'] = '%' . $keyword . '%';
    }

    if (!empty($user_type)) {
        $sql .= " AND u.user_type = :user_type";
        $params[':user_type'] = $user_type;
    }

    if (!empty($availability)) {
        $sql .= " AND e.availability = :availability";
        $params[':availability'] = $availability;
    }

    if (!empty($keyword) || !empty($user_type) || !empty($availability)) {
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
session_start();
require_once('verification.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
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
<style>
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

.success-message {
    animation: fadeOut 5s forwards; 
}
</style>
<script>
        function showContactForm(userId) {
            var form = document.getElementById('contact-form-' + userId);
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block'; // Affiche le formulaire
            } else {
                form.style.display = 'none'; // Masque le formulaire
            }
        }
    </script>
<body>
<?php
    if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> Votre message a été envoyé avec succès !
        </div>
<?php endif; ?>
    
    <div class="search-container">
        <header class="search-header">
            <a href="recherche.php" class="logo-link">
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
                                <option value="monteur" <?php echo (isset($_GET['user_type']) && $_GET['user_type'] === 'monteur') ? 'selected' : ''; ?>>Monteur</option>
                                <option value="graphiste" <?php echo (isset($_GET['user_type']) && $_GET['user_type'] === 'graphiste') ? 'selected' : ''; ?>>Graphiste</option>
                                <option value="manager" <?php echo (isset($_GET['user_type']) && $_GET['user_type'] === 'manager') ? 'selected' : ''; ?>>Manager</option>
                                <option value="développeur" <?php echo (isset($_GET['user_type']) && $_GET['user_type'] === 'développeur') ? 'selected' : ''; ?>>Développeur</option>
                                <option value="beatmaker" <?php echo (isset($_GET['user_type']) && $_GET['user_type'] === 'beatmaker') ? 'selected' : ''; ?>>Beatmaker</option>
                            </select>
                        </div>
                    </div>
                </form>
            </section>
            
            <section class="results-section">
                <?php if (!empty($keyword) || !empty($user_type) || !empty($availability)): ?>
                    <div class="results-header">
                        <h2>
                            Résultats 
                            <?php if (!empty($keyword)): ?>pour "<?php echo htmlspecialchars($keyword); ?>"<?php endif; ?>
                            <?php if (!empty($user_type)): ?>(Type: <?php echo htmlspecialchars($user_type); ?>)<?php endif; ?>
                            <?php if (!empty($availability)): ?>(Dispo: <?php echo htmlspecialchars($availability); ?>)<?php endif; ?>
                        </h2>
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
                                                    <?php if (!empty($row['years_experience'])): ?>
                                                    <li><strong>Expérience :</strong> 
                                                        <?= htmlspecialchars($row['years_experience']) ?> ans
                                                    </li>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($row['daily_rate'])): ?>
                                                    <li><strong>Taux journalier :</strong> 
                                                        <?= htmlspecialchars($row['daily_rate']) ?>€
                                                    </li>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($row['availability'])): ?>
                                                    <li><strong>Disponibilité :</strong> 
                                                        <span class="availability <?= htmlspecialchars($row['availability']) ?>">
                                                            <?= htmlspecialchars($row['availability']) ?>
                                                        </span>
                                                    </li>
                                                    <?php endif; ?>
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
                                        <button class="btn btn-contact" onclick="showContactForm(<?php echo $row['user_id']; ?>)">
                                            <i class="fas fa-envelope"></i> Contacter
                                        </button>
                                        <a href="profile.php?id=<?php echo $row['user_id']; ?>" class="btn btn-view">
                                            <i class="fas fa-eye"></i> Voir le profil
                                        </a>

                                        <form id="contact-form-<?php echo $row['user_id']; ?>" class="contact-form" action="send_message.php" method="post" style="display: none;">
                                            <input type="hidden" name="receiver_id" value="<?php echo $row['user_id']; ?>">
                                            <textarea name="content" placeholder="Votre message..." required></textarea>
                                            <button type="submit" class="btn btn-send"><i class="fas fa-paper-plane"></i> Envoyer</button>
                                        </form>
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
                <?php elseif (empty($keyword) && empty($_GET['user_type']) && empty($_GET['availability']) && isset($_GET['keyword'])): ?>
                    <div class="no-keyword">
                        <i class="fas fa-search"></i>
                        <h3>Veuillez entrer un critère de recherche</h3>
                        <p>Utilisez le champ de texte ou les filtres pour trouver des professionnels.</p>
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
                    <a href="support.php"><i class="fas fa-question-circle"></i> Aide</a>
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

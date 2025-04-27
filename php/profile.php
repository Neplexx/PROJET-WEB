<?php

$servername = 'localhost'; 
$username = 'root'; 
$password = 'root'; 
$dbname = 'ctmdata';
session_start();
require_once('verification.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($user_id <= 0) {
        die("ID utilisateur invalide");
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_info) {
        die("Utilisateur non trouvé");
    }

    if ($user_info['user_type'] === 'monteur') {
        $stmt = $conn->prepare("SELECT * FROM editors WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $specific_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $conn->prepare("SELECT s.skill_name, s.category, es.proficiency_level 
                               FROM editor_skills es 
                               JOIN skills s ON es.skill_id = s.skill_id 
                               WHERE es.editor_id = ?");
        $stmt->execute([$specific_info['editor_id']]);
        $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $conn->prepare("SELECT * FROM portfolios WHERE editor_id = ?");
        $stmt->execute([$specific_info['editor_id']]);
        $portfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } elseif ($user_info['user_type'] === 'employeur') {
        $stmt = $conn->prepare("SELECT * FROM employers WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $specific_info = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $stmt = $conn->prepare("SELECT r.*, u.first_name, u.last_name 
                           FROM reviews r 
                           JOIN users u ON r.reviewer_id = u.user_id 
                           WHERE r.reviewed_id = ?");
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo htmlspecialchars($user_info['first_name'] . ' ' . $user_info['last_name']); ?> | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../styles/style_employees.css">
    <style>
        .profile-view-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 2rem;
            border: 5px solid #f5f5f5;
        }
        
        .profile-basic-info h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 2rem;
        }
        
        .profile-type {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }
        
        .profile-type.monteur {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .profile-type.employeur {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .profile-rating {
            margin-top: 1rem;
            color: #f39c12;
        }
        
        .profile-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .profile-section {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 1rem;
            color: #e74c3c;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .info-item {
            margin-bottom: 1rem;
        }
        
        .info-label {
            display: block;
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 0.3rem;
        }
        
        .info-value {
            font-weight: 500;
            font-size: 1.1rem;
        }
        
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
        }
        
        .skill-tag {
            background-color: #f5f5f5;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .portfolio-item {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .portfolio-thumbnail {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .portfolio-info {
            padding: 1rem;
        }
        
        .portfolio-title {
            margin: 0 0 0.5rem;
            font-size: 1.1rem;
        }
        
        .reviews-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .review-item {
            border-bottom: 1px solid #eee;
            padding-bottom: 1.5rem;
        }
        
        .review-item:last-child {
            border-bottom: none;
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .review-author {
            font-weight: 500;
        }
        
        .review-rating {
            color: #f39c12;
            margin-left: 1rem;
        }
        
        .review-date {
            font-size: 0.8rem;
            color: #7f8c8d;
        }
        
        .back-button {
            display: inline-block;
            margin-bottom: 2rem;
            padding: 0.5rem 1rem;
            background-color: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .back-button:hover {
            background-color: #1a252f;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-picture {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="profile-view-container">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($user_info['profile_picture'] ?: '../pictures/profile1.jpg'); ?>" 
                 alt="Photo de profil" class="profile-picture">
            
            <div class="profile-basic-info">
                <h1><?php echo htmlspecialchars($user_info['first_name'] . ' ' . $user_info['last_name']); ?></h1>
                <span class="profile-type <?php echo htmlspecialchars($user_info['user_type']); ?>">
                    <?php echo htmlspecialchars(ucfirst($user_info['user_type'])); ?>
                </span>
                
                <div class="profile-rating">
                    <?php 
                    $avg_rating = 0;
                    if (!empty($reviews)) {
                        $total = 0;
                        foreach ($reviews as $review) {
                            $total += $review['rating'];
                        }
                        $avg_rating = $total / count($reviews);
                    }
                    
                    $full_stars = floor($avg_rating);
                    $half_star = ($avg_rating - $full_stars) >= 0.5;
                    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                    
                    for ($i = 0; $i < $full_stars; $i++) {
                        echo '<i class="fas fa-star"></i>';
                    }
                    if ($half_star) {
                        echo '<i class="fas fa-star-half-alt"></i>';
                    }
                    for ($i = 0; $i < $empty_stars; $i++) {
                        echo '<i class="far fa-star"></i>';
                    }
                    
                    echo ' <span>(' . number_format($avg_rating, 1) . ' / 5)</span>';
                    ?>
                </div>
                
                <?php if (!empty($user_info['bio'])): ?>
                    <p class="profile-bio" style="margin-top: 1rem;"><?php echo htmlspecialchars($user_info['bio']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-sections">
            <section class="profile-section">
                <h2 class="section-title"><i class="fas fa-info-circle"></i> Informations</h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <div class="info-value"><?php echo htmlspecialchars($user_info['email']); ?></div>
                    </div>
                    
                    <?php if (!empty($user_info['phone'])): ?>
                    <div class="info-item">
                        <span class="info-label">Téléphone</span>
                        <div class="info-value"><?php echo htmlspecialchars($user_info['phone']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user_info['user_type'] === 'monteur' && !empty($specific_info)): ?>
                    <div class="info-item">
                        <span class="info-label">Expérience</span>
                        <div class="info-value"><?php echo htmlspecialchars($specific_info['years_experience']); ?> ans</div>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Taux journalier</span>
                        <div class="info-value"><?php echo htmlspecialchars($specific_info['daily_rate']); ?> €</div>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Disponibilité</span>
                        <div class="info-value">
                            <span style="color: <?php 
                                echo $specific_info['availability'] === 'disponible' ? '#27ae60' : 
                                     ($specific_info['availability'] === 'occupé' ? '#e74c3c' : '#f39c12'); 
                            ?>">
                                <?php echo htmlspecialchars(ucfirst($specific_info['availability'])); ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user_info['user_type'] === 'employeur' && !empty($specific_info)): ?>
                    <div class="info-item">
                        <span class="info-label">Entreprise</span>
                        <div class="info-value"><?php echo htmlspecialchars($specific_info['company_name']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Taille</span>
                        <div class="info-value"><?php echo htmlspecialchars($specific_info['company_size']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Secteur</span>
                        <div class="info-value"><?php echo htmlspecialchars($specific_info['industry']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <?php if ($user_info['user_type'] === 'monteur' && !empty($skills)): ?>
            <section class="profile-section">
                <h2 class="section-title"><i class="fas fa-tools"></i> Compétences</h2>
                
                <div class="skills-list">
                    <?php foreach ($skills as $skill): ?>
                    <div class="skill-tag">
                        <?php echo htmlspecialchars($skill['skill_name']); ?>
                        <small>(<?php echo htmlspecialchars($skill['proficiency_level']); ?>)</small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
            
            <?php if ($user_info['user_type'] === 'monteur' && !empty($portfolio)): ?>
            <section class="profile-section">
                <h2 class="section-title"><i class="fas fa-briefcase"></i> Portfolio</h2>
                
                <div class="portfolio-grid">
                    <?php foreach ($portfolio as $item): ?>
                    <div class="portfolio-item">
                        <?php if ($item['thumbnail_url']): ?>
                        <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" 
                             alt="<?php echo htmlspecialchars($item['title']); ?>" 
                             class="portfolio-thumbnail">
                        <?php else: ?>
                        <div class="portfolio-thumbnail" style="background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-video" style="font-size: 2rem; color: #bdc3c7;"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="portfolio-info">
                            <h3 class="portfolio-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <?php if ($item['description']): ?>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($item['video_url']): ?>
                            <a href="<?php echo htmlspecialchars($item['video_url']); ?>" 
                               target="_blank" 
                               style="color: #e74c3c; text-decoration: none;">
                                <i class="fas fa-play"></i> Voir le projet
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
            
            <?php if (!empty($reviews)): ?>
            <section class="profile-section">
                <h2 class="section-title"><i class="fas fa-star"></i> Avis (<?php echo count($reviews); ?>)</h2>
                
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-author">
                                <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                            </div>
                            
                            <div class="review-rating">
                                <?php 
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $review['rating']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="review-date">
                            <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                        </div>
                        
                        <?php if (!empty($review['comment'])): ?>
                        <div class="review-comment" style="margin-top: 0.5rem;">
                            <?php echo htmlspecialchars($review['comment']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

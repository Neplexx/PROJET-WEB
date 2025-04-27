<?php
session_start();
require_once('../verification.php');

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
    $stmt = $pdo->prepare("
        SELECT u.*, e.years_experience, e.daily_rate, e.availability 
        FROM users u 
        JOIN editors e ON u.user_id = e.user_id 
        WHERE u.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $monteur = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT s.skill_name, es.proficiency_level 
        FROM editor_skills es
        JOIN skills s ON es.skill_id = s.skill_id
        WHERE es.editor_id = (
            SELECT editor_id FROM editors WHERE user_id = ?
        )
    ");
    $stmt->execute([$user_id]);
    $competences = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT p.*, pr.skill_id, s.skill_name 
        FROM projects p
        JOIN project_requirements pr ON p.project_id = pr.project_id
        JOIN skills s ON pr.skill_id = s.skill_id
        WHERE p.status = 'publié'
        AND pr.skill_id IN (
            SELECT skill_id FROM editor_skills WHERE editor_id = (
                SELECT editor_id FROM editors WHERE user_id = ?
            )
        )
        ORDER BY p.created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$user_id]);
    $projets = $stmt->fetchAll();

} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Monteur | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #e74c3c;
            --light: #ecf0f1;
            --dark: #1a252f;
            --text: #333;
            --text-light: #777;
            --white: #ffffff;
            --gray: #95a5a6;
            --dark-gray: #7f8c8d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text);
            margin: 0;
            padding: 0;
            background-color: var(--light);
        }

        .main-header {
            background-color: var(--primary);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            height: 60px;
            transition: transform 0.3s;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .user-nav {
            display: flex;
            align-items: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--white);
            transition: all 0.3s;
        }

        .user-profile:hover {
            color: var(--secondary);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.5rem;
            border: 2px solid var(--white);
            transition: all 0.3s;
        }

        .user-profile:hover .user-avatar {
            border-color: var(--secondary);
        }

        .user-name {
            font-weight: 500;
        }

        .main-nav {
            background-color: var(--dark);
            padding: 0;
        }

        .nav-list {
            display: flex;
            justify-content: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            margin: 0 1rem;
        }

        .nav-link {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
        }

        .nav-link:hover {
            color: var(--secondary);
        }

        .nav-link.active {
            color: var(--secondary);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--secondary);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .profile-card {
            background: var(--white);
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 5px solid var(--light);
        }

        .profile-name {
            color: var(--primary);
            margin: 0.5rem 0;
        }

        .profile-title {
            color: var(--secondary);
            margin: 0 0 1rem;
            font-weight: 500;
        }

        .profile-info {
            text-align: left;
            margin: 1.5rem 0;
        }

        .info-item {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .info-icon {
            color: var(--secondary);
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .skill-tag {
            background-color: var(--primary);
            color: var(--white);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .main-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .content-card {
            background: var(--white);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--light);
            padding-bottom: 0.5rem;
        }

        .card-title {
            color: var(--primary);
            margin: 0;
        }

        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--secondary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .project-card {
            border: 1px solid var(--light);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .project-image {
            height: 150px;
            background-color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 2rem;
        }

        .project-content {
            padding: 1rem;
        }

        .project-title {
            color: var(--primary);
            margin: 0 0 0.5rem;
        }

        .project-meta {
            display: flex;
            justify-content: space-between;
            color: var(--text-light);
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }

        .project-description {
            font-size: 0.9rem;
            margin: 0.5rem 0;
            color: var(--text);
        }

        .project-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            margin-top: 0.5rem;
        }

        .project-skill {
            background-color: var(--light);
            color: var(--primary);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        .availability-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .available {
            background-color: #2ecc71;
            color: white;
        }

        .busy {
            background-color: #f39c12;
            color: white;
        }

        .unavailable {
            background-color: #e74c3c;
            color: white;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="logo-container">
            <a href="../index.html" class="logo-link">
                <img src="../../pictures/logorond.png" alt="Logo CTM" class="logo">
            </a>
        </div>
        <nav class="user-nav">
            <a href="../php/espclient.php" class="user-profile">
                <img src="../../pictures/photoDeProfil.png" alt="Photo de profil" class="user-avatar">
                <span class="user-name">Mon compte</span>
            </a>
        </nav>
    </header>

    <nav class="main-nav">
        <ul class="nav-list">
            <li class="nav-item"><a href="../accueil.php" class="nav-link">Accueil</a></li>
            <li class="nav-item"><a href="../recherche.php" class="nav-link">Recherche</a></li>
        </ul>
    </nav>

    <div class="dashboard-container">
        <div class="profile-card">
            <img src="../../pictures/photoDeProfil.png" alt="Photo de profil" class="profile-avatar">
            <h2 class="profile-name"><?php echo htmlspecialchars($monteur['first_name'] . ' ' . $monteur['last_name']); ?></h2>
            <h3 class="profile-title">Monteur Vidéo</h3>
            
            <div class="profile-info">
                <div class="info-item">
                    <i class="fas fa-envelope info-icon"></i>
                    <span><?php echo htmlspecialchars($monteur['email']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone info-icon"></i>
                    <span><?php echo htmlspecialchars($monteur['phone'] ?? 'Non renseigné'); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-briefcase info-icon"></i>
                    <span><?php echo htmlspecialchars($monteur['years_experience']); ?> ans d'expérience</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-euro-sign info-icon"></i>
                    <span>Taux journalier: <?php echo htmlspecialchars($monteur['daily_rate']); ?>€</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-calendar-check info-icon"></i>
                    <span>Disponibilité: 
                        <span class="availability-badge <?php 
                            echo $monteur['availability'] === 'disponible' ? 'available' : 
                                 ($monteur['availability'] === 'occupé' ? 'busy' : 'unavailable'); 
                        ?>">
                            <?php 
                            echo $monteur['availability'] === 'disponible' ? 'Disponible' : 
                                 ($monteur['availability'] === 'occupé' ? 'Occupé' : 'Indisponible'); 
                            ?>
                        </span>
                    </span>
                </div>
            </div>

            <h4>Compétences</h4>
            <div class="skills-list">
                <?php foreach ($competences as $competence): ?>
                    <div class="skill-tag"><?php echo htmlspecialchars($competence['skill_name']); ?> (<?php echo htmlspecialchars($competence['proficiency_level']); ?>)</div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="main-content">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Projets correspondant à vos compétences</h2>
                    <a href="../recherche.php" class="btn btn-primary">Voir plus</a>
                </div>
                
                <?php if (!empty($projets)): ?>
                    <div class="projects-grid">
                        <?php foreach ($projets as $projet): ?>
                            <div class="project-card">
                                <div class="project-image">
                                    <i class="fas fa-video"></i>
                                </div>
                                <div class="project-content">
                                    <h3 class="project-title"><?php echo htmlspecialchars($projet['title']); ?></h3>
                                    <div class="project-meta">
                                        <span><?php echo htmlspecialchars($projet['project_type']); ?></span>
                                        <span>Budget: <?php echo htmlspecialchars($projet['budget']); ?>€</span>
                                    </div>
                                    <p class="project-description"><?php echo htmlspecialchars(substr($projet['description'], 0, 100) . '...'); ?></p>
                                    <div class="project-skills">
                                        <span class="project-skill"><?php echo htmlspecialchars($projet['skill_name']); ?></span>
                                    </div>
                                    <a href="projet_details.php?id=<?php echo $projet['project_id']; ?>" class="btn btn-primary" style="display: block; text-align: center; margin-top: 1rem;">Voir le projet</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucun projet ne correspond actuellement à vos compétences.</p>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Statistiques</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center;">
                    <div>
                        <h3 style="color: var(--secondary); margin: 0; font-size: 2rem;">12</h3>
                        <p style="margin: 0;">Projets réalisés</p>
                    </div>
                    <div>
                        <h3 style="color: var(--secondary); margin: 0; font-size: 2rem;">4.8</h3>
                        <p style="margin: 0;">Note moyenne</p>
                    </div>
                    <div>
                        <h3 style="color: var(--secondary); margin: 0; font-size: 2rem;">92%</h3>
                        <p style="margin: 0;">Satisfaction clients</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

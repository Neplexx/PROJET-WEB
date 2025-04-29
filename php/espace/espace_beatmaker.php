<!DOCTYPE html>
<?php
session_start();
require_once('../verification.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
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
    $stmt = $pdo->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || $user['user_type'] !== 'beatmaker') {
        header("Location: ../login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $beatmaker = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT p.*, e.company_name, COUNT(a.application_id) as applications_count
        FROM projects p
        JOIN employers e ON p.employer_id = e.employer_id
        LEFT JOIN applications a ON p.project_id = a.project_id
        GROUP BY p.project_id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $projets = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT u.user_id, u.first_name, u.last_name, u.email, e.years_experience
        FROM users u
        JOIN editors e ON u.user_id = e.user_id
        LIMIT 4
    ");
    $stmt->execute();
    $equipe = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_projects,
            SUM(CASE WHEN status = 'terminé' THEN 1 ELSE 0 END) as completed_projects
        FROM projects
    ");
    $stmt->execute();
    $stats = $stmt->fetch();

} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Beatmaker | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #1e1e2f; /* Fond sombre pour l'ambiance studio */
            --secondary: #6a1b9a; /* Violet créatif */
            --accent: #ff9800; /* Orange vif pour les accents */
            --light: #f8f9fa;
            --dark: #121212;
            --text: #e0e0e0; /* Texte clair sur fond sombre */
            --text-light: #b0b0b0;
            --white: #ffffff;
            --success: #4caf50;
            --warning: #ff5722;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text);
            margin: 0;
            padding: 0;
            background-color: var(--primary);
            background-image: linear-gradient(to bottom, rgba(30, 30, 47, 0.9), rgba(30, 30, 47, 0.95)), 
                              url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path fill="%236a1b9a" opacity="0.05" d="M0,0 L100,0 L100,100 L0,100 Z" /><path fill="none" stroke="%23ff9800" stroke-width="0.5" stroke-dasharray="2,2" d="M0,20 Q50,40 100,20 T200,20" /></svg>');
        }

        .main-header {
            background-color: rgba(26, 26, 40, 0.9);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
            position: relative;
            z-index: 10;
        }

        .logo {
            height: 60px;
            transition: transform 0.3s;
            filter: drop-shadow(0 0 5px rgba(106, 27, 154, 0.5));
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
            padding: 0.5rem 1rem;
            border-radius: 30px;
            background: rgba(106, 27, 154, 0.3);
            border: 1px solid rgba(106, 27, 154, 0.5);
        }

        .user-profile:hover {
            color: var(--accent);
            background: rgba(106, 27, 154, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 27, 154, 0.3);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.5rem;
            border: 2px solid var(--accent);
            transition: all 0.3s;
            object-fit: cover;
        }

        .user-profile:hover .user-avatar {
            border-color: var(--white);
            transform: rotate(10deg);
        }

        .user-name {
            font-weight: 500;
        }

        .main-nav {
            background-color: rgba(18, 18, 18, 0.9);
            padding: 0;
            backdrop-filter: blur(5px);
            border-bottom: 1px solid rgba(106, 27, 154, 0.3);
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
            position: relative;
        }

        .nav-link {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .nav-link:hover {
            color: var(--accent);
        }

        .nav-link.active {
            color: var(--accent);
            font-weight: 600;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 3px;
            background-color: var(--accent);
            border-radius: 3px;
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
            background: rgba(26, 26, 40, 0.8);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            border: 1px solid rgba(106, 27, 154, 0.3);
            backdrop-filter: blur(5px);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(106, 27, 154, 0.3);
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 5px solid var(--secondary);
            box-shadow: 0 5px 15px rgba(106, 27, 154, 0.5);
            transition: all 0.3s;
        }

        .profile-card:hover .profile-avatar {
            border-color: var(--accent);
            transform: rotate(5deg);
        }

        .profile-name {
            color: var(--white);
            margin: 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 600;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .profile-title {
            color: var(--accent);
            margin: 0 0 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .beatmaker-badge {
            background: linear-gradient(45deg, var(--secondary), #9c27b0);
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 3px 10px rgba(106, 27, 154, 0.5);
        }

        .profile-info {
            text-align: left;
            margin: 1.5rem 0;
        }

        .info-item {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .info-item:hover {
            background: rgba(106, 27, 154, 0.2);
            transform: translateX(5px);
        }

        .info-icon {
            color: var(--accent);
            margin-right: 0.8rem;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .main-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .content-card {
            background: rgba(26, 26, 40, 0.8);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(106, 27, 154, 0.3);
            backdrop-filter: blur(5px);
            transition: transform 0.3s;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(106, 27, 154, 0.3);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(106, 27, 154, 0.3);
        }

        .card-title {
            color: var(--white);
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }

        .card-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--accent);
            border-radius: 3px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--secondary), #9c27b0);
            color: var(--white);
            box-shadow: 0 3px 10px rgba(106, 27, 154, 0.5);
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #9c27b0, var(--secondary));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 27, 154, 0.7);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .project-card {
            background: rgba(30, 30, 47, 0.7);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid rgba(106, 27, 154, 0.3);
            position: relative;
        }

        .project-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--accent));
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(106, 27, 154, 0.3);
        }

        .project-header {
            background: rgba(18, 18, 18, 0.7);
            color: white;
            padding: 0.8rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(106, 27, 154, 0.3);
        }

        .project-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-draft {
            background-color: var(--text-light);
            color: var(--dark);
        }

        .status-published {
            background-color: var(--success);
            color: white;
        }

        .status-in-progress {
            background-color: var(--accent);
            color: white;
        }

        .status-completed {
            background-color: var(--secondary);
            color: white;
        }

        .project-content {
            padding: 1.2rem;
        }

        .project-title {
            color: var(--white);
            margin: 0 0 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .project-meta {
            display: flex;
            justify-content: space-between;
            color: var(--text-light);
            font-size: 0.8rem;
            margin-bottom: 0.8rem;
        }

        .project-description {
            font-size: 0.9rem;
            margin: 0.8rem 0;
            color: var(--text);
            line-height: 1.5;
        }

        .project-applications {
            display: flex;
            align-items: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--accent);
            font-weight: 500;
        }

        .project-applications i {
            color: var(--accent);
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .stat-card {
            background: rgba(30, 30, 47, 0.7);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(106, 27, 154, 0.3);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(106, 27, 154, 0.2);
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }

        .stat-value {
            color: var(--white);
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0.5rem 0;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .team-member {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: rgba(30, 30, 47, 0.7);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(106, 27, 154, 0.3);
            transition: transform 0.3s;
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(106, 27, 154, 0.2);
        }

        .team-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 2px solid var(--accent);
            box-shadow: 0 3px 10px rgba(106, 27, 154, 0.3);
        }

        .team-info {
            flex: 1;
        }

        .team-name {
            font-weight: 600;
            margin: 0 0 0.2rem;
            font-size: 0.95rem;
            color: var(--white);
        }

        .team-role {
            color: var(--accent);
            font-size: 0.8rem;
            margin: 0;
            font-weight: 500;
        }

        /* Éléments spécifiques beatmaker */
        .music-wave {
            position: relative;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1rem 0;
        }

        .music-wave span {
            display: block;
            width: 3px;
            height: 10px;
            background: var(--accent);
            margin: 0 2px;
            border-radius: 3px;
            animation: wave 1.5s infinite ease-in-out;
        }

        .music-wave span:nth-child(2) {
            animation-delay: 0.2s;
            height: 15px;
        }
        .music-wave span:nth-child(3) {
            animation-delay: 0.4s;
            height: 20px;
        }
        .music-wave span:nth-child(4) {
            animation-delay: 0.6s;
            height: 25px;
        }
        .music-wave span:nth-child(5) {
            animation-delay: 0.8s;
            height: 20px;
        }
        .music-wave span:nth-child(6) {
            animation-delay: 1s;
            height: 15px;
        }
        .music-wave span:nth-child(7) {
            animation-delay: 1.2s;
            height: 10px;
        }

        @keyframes wave {
            0%, 60%, 100% { transform: scaleY(0.5); }
            30% { transform: scaleY(1); }
        }

        .beatmaker-stats {
            display: flex;
            justify-content: space-around;
            margin: 1.5rem 0;
        }

        .beat-stat {
            text-align: center;
            padding: 0.5rem;
        }

        .beat-stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--accent);
            margin-bottom: 0.3rem;
        }

        .beat-stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid, .beatmaker-stats {
                grid-template-columns: 1fr;
            }
            
            .nav-list {
                flex-wrap: wrap;
            }
            
            .nav-item {
                margin: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="logo-container">
            <a href="../../index.html" class="logo-link">
                <img src="../../pictures/logorond.png" alt="Logo CTM" class="logo">
            </a>
        </div>
        <nav class="user-nav">
            <a href="../espclient.php" class="user-profile">
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
            <h2 class="profile-name"><?php echo htmlspecialchars($beatmaker['first_name'] . ' ' . $beatmaker['last_name']); ?></h2>
            <h3 class="profile-title">
                Beatmaker
                <span class="beatmaker-badge">CTM Music</span>
            </h3>
            
            <div class="music-wave">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
            
            <div class="profile-info">
                <div class="info-item">
                    <i class="fas fa-envelope info-icon"></i>
                    <span><?php echo htmlspecialchars($beatmaker['email']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone info-icon"></i>
                    <span><?php echo htmlspecialchars($beatmaker['phone'] ?? 'Non renseigné'); ?></span>
                </div>
                
                <div class="beatmaker-stats">
                    <div class="beat-stat">
                        <div class="beat-stat-value">24</div>
                        <div class="beat-stat-label">Projets</div>
                    </div>
                    <div class="beat-stat">
                        <div class="beat-stat-value">8</div>
                        <div class="beat-stat-label">Collabs</div>
                    </div>
                    <div class="beat-stat">
                        <div class="beat-stat-value">4.8</div>
                        <div class="beat-stat-label">Rating</div>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem; padding: 1rem; background-color: rgba(106, 27, 154, 0.2); border-radius: 8px; border-left: 3px solid var(--accent);">
                    <h4 style="margin-top: 0; color: var(--accent);">Style Musical</h4>
                    <p>Hip-Hop, Trap, Lo-Fi. Spécialisé dans les instrumentales percutantes avec des mélodies atmosphériques.</p>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Mes Dernières Créations</h2>
                    </a>
                </div>
                
                <?php if (!empty($projets)): ?>
                    <div class="projects-grid">
                        <?php foreach ($projets as $projet): ?>
                            <div class="project-card">
                                <div class="project-header">
                                    <span><?php echo htmlspecialchars($projet['company_name']); ?></span>
                                    <span class="project-status status-<?php echo str_replace(' ', '-', $projet['status']); ?>">
                                        <?php echo htmlspecialchars($projet['status']); ?>
                                    </span>
                                </div>
                                <div class="project-content">
                                    <h3 class="project-title"><?php echo htmlspecialchars($projet['title']); ?></h3>
                                    <div class="project-meta">
                                        <span><?php echo htmlspecialchars($projet['project_type']); ?></span>
                                        <span>BPM: <?php echo rand(80, 160); ?></span>
                                    </div>
                                    <p class="project-description"><?php echo htmlspecialchars(substr($projet['description'], 0, 100) . '...'); ?></p>
                                    
                                    <div class="project-applications">
                                        <i class="fas fa-music"></i>
                                        <span><?php echo rand(3, 15); ?> versions</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-light);">Aucune création pour le moment. Commencez un nouveau beat!</p>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Statistiques du Studio</h2>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-music"></i></div>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['total_projects'] ?? 0); ?></div>
                        <div class="stat-label">Beats créés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-headphones"></i></div>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['completed_projects'] ?? 0); ?></div>
                        <div class="stat-label">Beats vendus</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-value"><?php echo count($equipe); ?></div>
                        <div class="stat-label">Artistes collabs</div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Artistes en Collaboration</h2>
                    <a href="../recherche.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Trouver des artistes
                    </a>
                </div>
                
                <?php if (!empty($equipe)): ?>
                    <div class="team-grid">
                        <?php foreach ($equipe as $membre): ?>
                            <div class="team-member">
                                <img src="../../pictures/photoDeProfil.png" alt="Photo de profil" class="team-avatar">
                                <div class="team-info">
                                    <h4 class="team-name"><?php echo htmlspecialchars($membre['first_name'] . ' ' . $membre['last_name']); ?></h4>
                                    <p class="team-role"><?php echo htmlspecialchars($membre['years_experience']); ?> ans dans l'industrie</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-light);">Aucune collaboration active pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

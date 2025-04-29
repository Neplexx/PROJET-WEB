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

    if (!$user || $user['user_type'] !== 'développeur') {
        header("Location: ../login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $developpeur = $stmt->fetch();

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
    <title>Espace Développeur | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #0d1117; 
            --secondary: #238636; 
            --accent: #1f6feb; 
            --light: #f0f6fc;
            --dark: #010409;
            --text: #c9d1d9; 
            --text-light: #8b949e;
            --white: #ffffff;
            --success: #3fb950;
            --warning: #d29922;
            --error: #f85149;
        }

        body {
            font-family: 'SF Mono', 'Roboto Mono', monospace;
            line-height: 1.6;
            color: var(--text);
            margin: 0;
            padding: 0;
            background-color: var(--primary);
            background-image: 
                linear-gradient(to bottom, rgba(13, 17, 23, 0.95), rgba(13, 17, 23, 0.98)),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%231f6feb" opacity="0.02"/><path d="M30,10 L10,30 M90,70 L70,90 M90,10 L70,30 M30,70 L10,90" stroke="%238b949e" stroke-width="0.5" opacity="0.1"/></svg>');
        }

        .main-header {
            background-color: rgba(13, 17, 23, 0.9);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(1, 4, 9, 0.5);
            border-bottom: 1px solid rgba(240, 246, 252, 0.1);
            position: relative;
            z-index: 10;
        }

        .logo {
            height: 60px;
            transition: transform 0.3s;
            filter: brightness(1.2);
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
            border-radius: 6px;
            background: rgba(31, 111, 235, 0.1);
            border: 1px solid rgba(31, 111, 235, 0.2);
        }

        .user-profile:hover {
            color: var(--light);
            background: rgba(31, 111, 235, 0.2);
            transform: translateY(-2px);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            margin-right: 0.5rem;
            border: 1px solid var(--accent);
            transition: all 0.3s;
            object-fit: cover;
        }

        .user-profile:hover .user-avatar {
            border-color: var(--secondary);
            transform: scale(1.1);
        }

        .user-name {
            font-weight: 500;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .main-nav {
            background-color: rgba(13, 17, 23, 0.9);
            padding: 0;
            border-bottom: 1px solid rgba(240, 246, 252, 0.1);
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
            font-size: 0.9rem;
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
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--accent);
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
            background: rgba(13, 17, 23, 0.8);
            border-radius: 6px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(1, 4, 9, 0.5);
            text-align: center;
            border: 1px solid rgba(240, 246, 252, 0.1);
            transition: all 0.3s;
        }

        .profile-card:hover {
            border-color: var(--accent);
            box-shadow: 0 8px 24px rgba(31, 111, 235, 0.2);
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 6px;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 3px solid var(--accent);
            transition: all 0.3s;
        }

        .profile-card:hover .profile-avatar {
            border-color: var(--secondary);
            transform: rotate(2deg);
        }

        .profile-name {
            color: var(--white);
            margin: 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 600;
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

        .dev-badge {
            background: rgba(35, 134, 54, 0.2);
            color: var(--secondary);
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
            border: 1px solid rgba(35, 134, 54, 0.4);
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
            border-radius: 6px;
            transition: all 0.3s;
        }

        .info-item:hover {
            background: rgba(31, 111, 235, 0.1);
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
            background: rgba(13, 17, 23, 0.8);
            border-radius: 6px;
            padding: 1.5rem;
            box-shadow: 0 8px 24px rgba(1, 4, 9, 0.5);
            border: 1px solid rgba(240, 246, 252, 0.1);
            transition: all 0.3s;
        }

        .content-card:hover {
            border-color: var(--accent);
            box-shadow: 0 8px 24px rgba(31, 111, 235, 0.2);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(240, 246, 252, 0.1);
        }

        .card-title {
            color: var(--white);
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: rgba(31, 111, 235, 0.2);
            color: var(--accent);
            border: 1px solid rgba(31, 111, 235, 0.4);
        }

        .btn-primary:hover {
            background: rgba(31, 111, 235, 0.3);
            color: var(--light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(31, 111, 235, 0.2);
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
            background: rgba(22, 27, 34, 0.8);
            border-radius: 6px;
            overflow: hidden;
            transition: all 0.3s;
            border: 1px solid rgba(240, 246, 252, 0.1);
            position: relative;
        }

        .project-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(31, 111, 235, 0.2);
        }

        .project-header {
            background: rgba(13, 17, 23, 0.9);
            color: white;
            padding: 0.8rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(240, 246, 252, 0.1);
        }

        .project-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-draft {
            background-color: rgba(139, 148, 158, 0.2);
            color: var(--text-light);
        }

        .status-published {
            background-color: rgba(63, 185, 80, 0.2);
            color: var(--success);
        }

        .status-in-progress {
            background-color: rgba(210, 153, 34, 0.2);
            color: var(--warning);
        }

        .status-completed {
            background-color: rgba(31, 111, 235, 0.2);
            color: var(--accent);
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

        .project-tech {
            display: flex;
            align-items: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--accent);
            font-weight: 500;
        }

        .project-tech i {
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
            background: rgba(22, 27, 34, 0.8);
            border-radius: 6px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(1, 4, 9, 0.3);
            border: 1px solid rgba(240, 246, 252, 0.1);
            transition: all 0.3s;
        }

        .stat-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(31, 111, 235, 0.2);
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
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
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
            background: rgba(22, 27, 34, 0.8);
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(1, 4, 9, 0.3);
            border: 1px solid rgba(240, 246, 252, 0.1);
            transition: all 0.3s;
        }

        .team-member:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(31, 111, 235, 0.2);
        }

        .team-avatar {
            width: 50px;
            height: 50px;
            border-radius: 6px;
            object-fit: cover;
            margin-right: 1rem;
            border: 1px solid var(--accent);
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

        .code-snippet {
            position: relative;
            background: rgba(13, 17, 23, 0.9);
            border-radius: 6px;
            padding: 1rem;
            margin: 1.5rem 0;
            border: 1px solid rgba(240, 246, 252, 0.1);
            font-family: 'SF Mono', 'Roboto Mono', monospace;
            font-size: 0.85rem;
            line-height: 1.5;
            overflow-x: auto;
        }

        .code-snippet::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent);
            border-radius: 6px 0 0 6px;
        }

        .code-keyword {
            color: #ff7b72;
        }
        .code-function {
            color: #d2a8ff;
        }
        .code-string {
            color: #a5d6ff;
        }
        .code-comment {
            color: #8b949e;
            font-style: italic;
        }
        .code-number {
            color: #79c0ff;
        }

        .dev-stats {
            display: flex;
            justify-content: space-around;
            margin: 1.5rem 0;
        }

        .dev-stat {
            text-align: center;
            padding: 0.5rem;
        }

        .dev-stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--accent);
            margin-bottom: 0.3rem;
        }

        .dev-stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid, .dev-stats {
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
            <h2 class="profile-name"><?php echo htmlspecialchars($developpeur['first_name'] . ' ' . $developpeur['last_name']); ?></h2>
            <h3 class="profile-title">
                Développeur
                <span class="dev-badge">CTM Tech</span>
            </h3>
            
            <div class="code-snippet">
                <span class="code-keyword">const</span> <span class="code-function">aboutMe</span> = {<br>
                &nbsp;&nbsp;<span class="code-keyword">role</span>: <span class="code-string">'Full Stack Developer'</span>,<br>
                &nbsp;&nbsp;<span class="code-keyword">skills</span>: [<span class="code-string">'React'</span>, <span class="code-string">'Node.js'</span>, <span class="code-string">'PHP'</span>],<br>
                &nbsp;&nbsp;<span class="code-keyword">experience</span>: <span class="code-number">5</span> <span class="code-comment">// années</span><br>
                };
            </div>
            
            <div class="profile-info">
                <div class="info-item">
                    <i class="fas fa-envelope info-icon"></i>
                    <span><?php echo htmlspecialchars($developpeur['email']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone info-icon"></i>
                    <span><?php echo htmlspecialchars($developpeur['phone'] ?? 'Non renseigné'); ?></span>
                </div>
                
                <div class="dev-stats">
                    <div class="dev-stat">
                        <div class="dev-stat-value">18</div>
                        <div class="dev-stat-label">Projets</div>
                    </div>
                    <div class="dev-stat">
                        <div class="dev-stat-value">7</div>
                        <div class="dev-stat-label">Contributions</div>
                    </div>
                    <div class="dev-stat">
                        <div class="dev-stat-value">4.9</div>
                        <div class="dev-stat-label">Rating</div>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem; padding: 1rem; background-color: rgba(31, 111, 235, 0.1); border-radius: 6px; border-left: 3px solid var(--accent);">
                    <h4 style="margin-top: 0; color: var(--accent);">Spécialisations</h4>
                    <p>Développement Full Stack, Architecture Cloud, DevOps. Expertise en React, Node.js et bases de données relationnelles.</p>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Mes Derniers Projets</h2>
                    <a href="../projets.php" class="btn btn-primary">
                        <i class="fas fa-code-branch"></i> Nouveau Projet
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
                                        <span>V<?php echo rand(1, 5); ?>.<?php echo rand(0, 9); ?></span>
                                    </div>
                                    <p class="project-description"><?php echo htmlspecialchars(substr($projet['description'], 0, 100) . '...'); ?></p>
                                    
                                    <div class="project-tech">
                                        <i class="fas fa-code"></i>
                                        <span>React, Node.js, MySQL</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-light);">Aucun projet pour le moment. Commencez un nouveau projet!</p>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Statistiques Techniques</h2>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-code"></i></div>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['total_projects'] ?? 0); ?></div>
                        <div class="stat-label">Projets actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['completed_projects'] ?? 0); ?></div>
                        <div class="stat-label">Projets livrés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-value"><?php echo count($equipe); ?></div>
                        <div class="stat-label">Collaborateurs</div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Équipe Technique</h2>
                    <a href="../employees.php?user_type=développeur" class="btn btn-primary">
                        <i class="fas fa-search"></i> Trouver des devs
                    </a>
                </div>
                
                <?php if (!empty($equipe)): ?>
                    <div class="team-grid">
                        <?php foreach ($equipe as $membre): ?>
                            <div class="team-member">
                                <img src="../../pictures/photoDeProfil.png" alt="Photo de profil" class="team-avatar">
                                <div class="team-info">
                                    <h4 class="team-name"><?php echo htmlspecialchars($membre['first_name'] . ' ' . $membre['last_name']); ?></h4>
                                    <p class="team-role"><?php echo htmlspecialchars($membre['years_experience']); ?> ans d'expérience</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-light);">Aucun collaborateur technique pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

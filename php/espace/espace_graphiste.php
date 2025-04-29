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

    if (!$user || $user['user_type'] !== 'graphiste') {
        header("Location: ../login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $graphiste = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT p.*, e.company_name, COUNT(a.application_id) as applications_count
        FROM projects p
        JOIN employers e ON p.employer_id = e.employer_id
        LEFT JOIN applications a ON p.project_id = a.project_id
        WHERE p.project_type IN ('publicité', 'branding', 'design')
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
        WHERE u.user_type = 'graphiste'
        LIMIT 4
    ");
    $stmt->execute();
    $equipe = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_projects,
            SUM(CASE WHEN status = 'terminé' THEN 1 ELSE 0 END) as completed_projects
        FROM projects
        WHERE project_type IN ('publicité', 'branding', 'design')
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
    <title>Espace Graphiste | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2d3436; 
            --secondary: #e84393; 
            --accent: #0984e3; 
            --light: #f5f6fa; 
            --dark: #1e272e; 
            --text: #2d3436; 
            --text-light: #636e72; 
            --white: #ffffff;
            --success: #00b894; 
            --warning: #fdcb6e; 
            --creative1: #fd79a8; 
            --creative2: #74b9ff; 
            --creative3: #55efc4; 
        }

        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text);
            margin: 0;
            padding: 0;
            background-color: var(--light);
            background-image: 
                linear-gradient(to bottom, rgba(245, 246, 250, 0.9), rgba(245, 246, 250, 1)),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%232d3436" opacity="0.03"/><circle cx="30" cy="30" r="5" fill="%23e84393" opacity="0.1"/><circle cx="70" cy="70" r="5" fill="%230984e3" opacity="0.1"/><circle cx="70" cy="30" r="5" fill="%23fd79a8" opacity="0.1"/><circle cx="30" cy="70" r="5" fill="%2355efc4" opacity="0.1"/></svg>');
        }

        .main-header {
            background-color: var(--white);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }

        .logo {
            height: 60px;
            transition: transform 0.3s;
            filter: drop-shadow(0 0 5px rgba(232, 67, 147, 0.3));
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
            color: var(--primary);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            background: linear-gradient(45deg, rgba(232, 67, 147, 0.1), rgba(9, 132, 227, 0.1));
        }

        .user-profile:hover {
            color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(232, 67, 147, 0.2);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.5rem;
            border: 2px solid var(--light);
            transition: all 0.3s;
            object-fit: cover;
        }

        .user-profile:hover .user-avatar {
            border-color: var(--secondary);
            transform: rotate(10deg);
        }

        .user-name {
            font-weight: 500;
        }

        .main-nav {
            background-color: var(--primary);
            padding: 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
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
            font-weight: 600;
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
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            border-top: 5px solid var(--secondary);
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(232, 67, 147, 0.2);
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 5px solid var(--light);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .profile-card:hover .profile-avatar {
            border-color: var(--secondary);
            transform: scale(1.05);
        }

        .profile-name {
            color: var(--primary);
            margin: 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .profile-title {
            color: var(--secondary);
            margin: 0 0 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .graphiste-badge {
            background: linear-gradient(45deg, var(--secondary), var(--creative1));
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
            box-shadow: 0 3px 10px rgba(232, 67, 147, 0.3);
        }

        .profile-info {
            text-align: left;
            margin: 1.5rem 0;
        }

        .info-item {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            padding: 0.8rem;
            border-radius: 10px;
            transition: all 0.3s;
            background-color: rgba(245, 246, 250, 0.7);
        }

        .info-item:hover {
            background: linear-gradient(45deg, rgba(232, 67, 147, 0.1), rgba(9, 132, 227, 0.1));
            transform: translateX(5px);
        }

        .info-icon {
            color: var(--secondary);
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
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            border-top: 3px solid var(--secondary);
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(232, 67, 147, 0.2);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(245, 246, 250, 0.7);
        }

        .card-title {
            color: var(--primary);
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            position: relative;
        }

        .card-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--secondary);
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
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--secondary), var(--creative1));
            color: var(--white);
            box-shadow: 0 3px 10px rgba(232, 67, 147, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, var(--creative1), var(--secondary));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(232, 67, 147, 0.4);
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
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .project-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--secondary), var(--accent));
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(232, 67, 147, 0.2);
        }

        .project-header {
            background-color: var(--primary);
            color: white;
            padding: 0.8rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(245, 246, 250, 0.2);
        }

        .project-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-draft {
            background-color: var(--text-light);
            color: var(--white);
        }

        .status-published {
            background-color: var(--success);
            color: var(--white);
        }

        .status-in-progress {
            background-color: var(--warning);
            color: var(--dark);
        }

        .status-completed {
            background-color: var(--secondary);
            color: var(--white);
        }

        .project-content {
            padding: 1.2rem;
        }

        .project-title {
            color: var(--primary);
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

        .project-creative {
            display: flex;
            align-items: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--secondary);
            font-weight: 500;
        }

        .project-creative i {
            color: var(--secondary);
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            border-top: 3px solid var(--secondary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(232, 67, 147, 0.2);
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }

        .stat-value {
            color: var(--primary);
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
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(232, 67, 147, 0.2);
        }

        .team-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 2px solid var(--light);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .team-info {
            flex: 1;
        }

        .team-name {
            font-weight: 600;
            margin: 0 0 0.2rem;
            font-size: 0.95rem;
            color: var(--primary);
        }

        .team-role {
            color: var(--secondary);
            font-size: 0.8rem;
            margin: 0;
        }

        .creative-stats {
            display: flex;
            justify-content: space-around;
            margin: 1.5rem 0;
            padding: 1rem;
            border-radius: 15px;
            background: linear-gradient(45deg, rgba(232, 67, 147, 0.1), rgba(9, 132, 227, 0.1));
        }

        .creative-stat {
            text-align: center;
            padding: 0.5rem;
        }

        .creative-stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--secondary);
            margin-bottom: 0.3rem;
        }

        .creative-stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .color-palette {
            display: flex;
            margin: 1.5rem 0;
        }

        .color-box {
            height: 30px;
            flex: 1;
            transition: all 0.3s;
        }

        .color-box:hover {
            transform: scaleY(1.5);
        }

        .color-box:nth-child(1) { background-color: var(--secondary); }
        .color-box:nth-child(2) { background-color: var(--accent); }
        .color-box:nth-child(3) { background-color: var(--creative1); }
        .color-box:nth-child(4) { background-color: var(--creative2); }
        .color-box:nth-child(5) { background-color: var(--creative3); }

        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid, .creative-stats {
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
            <h2 class="profile-name"><?php echo htmlspecialchars($graphiste['first_name'] . ' ' . $graphiste['last_name']); ?></h2>
            <h3 class="profile-title">
                Graphiste
                <span class="graphiste-badge">CTM Creative</span>
            </h3>
            
            <div class="color-palette">
                <div class="color-box"></div>
                <div class="color-box"></div>
                <div class="color-box"></div>
                <div class="color-box"></div>
                <div class="color-box"></div>
            </div>
            
            <div class="profile-info">
                <div class="info-item">
                    <i class="fas fa-envelope info-icon"></i>
                    <span><?php echo htmlspecialchars($graphiste['email']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone info-icon"></i>
                    <span><?php echo htmlspecialchars($graphiste['phone'] ?? 'Non renseigné'); ?></span>
                </div>
                
                <div class="creative-stats">
                    <div class="creative-stat">
                        <div class="creative-stat-value"><?php echo htmlspecialchars($stats['total_projects'] ?? 0); ?></div>
                        <div class="creative-stat-label">Projets</div>
                    </div>
                    <div class="creative-stat">
                        <div class="creative-stat-value"><?php echo htmlspecialchars($stats['completed_projects'] ?? 0); ?></div>
                        <div class="creative-stat-label">Réalisés</div>
                    </div>
                    <div class="creative-stat">
                        <div class="creative-stat-value"><?php echo count($equipe); ?></div>
                        <div class="creative-stat-label">Collègues</div>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem; padding: 1rem; background: linear-gradient(45deg, rgba(232, 67, 147, 0.1), rgba(9, 132, 227, 0.1)); border-radius: 15px; border-left: 3px solid var(--secondary);">
                    <h4 style="margin-top: 0; color: var(--secondary);">Spécialisations</h4>
                    <p>Identité visuelle, Motion Design, UI/UX. Passionné par les couleurs vives et les designs audacieux.</p>
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
                                        <span>Livré le <?php echo date('d/m/Y', strtotime($projet['end_date'] ?? 'now')); ?></span>
                                    </div>
                                    <p class="project-description"><?php echo htmlspecialchars(substr($projet['description'], 0, 100) . '...'); ?></p>
                                    
                                    <div class="project-creative">
                                        <i class="fas fa-palette"></i>
                                        <span><?php echo rand(3, 15); ?> versions créatives</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-light);">Aucune création pour le moment. Ajoutez votre premier projet!</p>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Mes Statistiques Créatives</h2>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-paint-brush"></i></div>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['total_projects'] ?? 0); ?></div>
                        <div class="stat-label">Projets créatifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['completed_projects'] ?? 0); ?></div>
                        <div class="stat-label">Projets livrés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-heart"></i></div>
                        <div class="stat-value"><?php echo count($equipe); ?></div>
                        <div class="stat-label">Clients satisfaits</div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Communauté Graphiste</h2>
                    <a href="../employees.php?user_type=graphiste" class="btn btn-primary">
                        <i class="fas fa-search"></i> Trouver des graphistes
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
                    <p style="text-align: center; color: var(--text-light);">Aucun collègue graphiste pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

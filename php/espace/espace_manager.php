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

    if (!$user || $user['user_type'] !== 'manager') {
        header("Location: ../login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $manager = $stmt->fetch();

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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Manager | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
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
            font-size: 1.5rem;
        }

        .profile-title {
            color: var(--secondary);
            margin: 0 0 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .manager-badge {
            background-color: var(--secondary);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
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
            background-color: #2980b9;
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

        .project-header {
            background-color: var(--primary);
            color: white;
            padding: 0.8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .project-status {
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-draft {
            background-color: var(--gray);
        }

        .status-published {
            background-color: #2ecc71;
        }

        .status-in-progress {
            background-color: #f39c12;
        }

        .status-completed {
            background-color: var(--secondary);
        }

        .project-content {
            padding: 1rem;
        }

        .project-title {
            color: var(--primary);
            margin: 0 0 0.5rem;
            font-size: 1.1rem;
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

        .project-applications {
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .project-applications i {
            color: var(--secondary);
            margin-right: 0.3rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .stat-value {
            color: var(--secondary);
            font-size: 2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .team-member {
            display: flex;
            align-items: center;
            padding: 0.8rem;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .team-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 0.8rem;
            border: 2px solid var(--light);
        }

        .team-info {
            flex: 1;
        }

        .team-name {
            font-weight: 500;
            margin: 0;
            font-size: 0.9rem;
        }

        .team-role {
            color: var(--text-light);
            font-size: 0.8rem;
            margin: 0;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
            <h2 class="profile-name"><?php echo htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']); ?></h2>
            <h3 class="profile-title">
                Manager
                <span class="manager-badge">CTM Platform</span>
            </h3>
            
            <div class="profile-info">
                <div class="info-item">
                    <i class="fas fa-envelope info-icon"></i>
                    <span><?php echo htmlspecialchars($manager['email']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone info-icon"></i>
                    <span><?php echo htmlspecialchars($manager['phone'] ?? 'Non renseigné'); ?></span>
                </div>
                
                <div style="margin-top: 1.5rem; padding: 1rem; background-color: var(--light); border-radius: 8px;">
                    <h4 style="margin-top: 0; color: var(--primary);">Rôle Manager</h4>
                    <p>Gestion des projets et coordination des équipes sur la plateforme CTM.</p>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Projets récents</h2>
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
                                        <span>Budget: <?php echo htmlspecialchars($projet['budget']); ?>€</span>
                                    </div>
                                    <p class="project-description"><?php echo htmlspecialchars(substr($projet['description'], 0, 100) . '...'); ?></p>
                                    
                                    <div class="project-applications">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo htmlspecialchars($projet['applications_count']); ?> candidatures</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucun projet disponible actuellement.</p>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Statistiques de la plateforme</h2>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-project-diagram" style="font-size: 1.5rem; color: var(--secondary);"></i>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['total_projects'] ?? 0); ?></div>
                        <div class="stat-label">Projets actifs</div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check-circle" style="font-size: 1.5rem; color: var(--secondary);"></i>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['completed_projects'] ?? 0); ?></div>
                        <div class="stat-label">Projets terminés</div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-tie" style="font-size: 1.5rem; color: var(--secondary);"></i>
                        <div class="stat-value"><?php echo count($equipe); ?></div>
                        <div class="stat-label">Monteurs actifs</div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Monteurs disponibles</h2>
                    <a href="../employees.php?user_type=manager" class="btn btn-primary">
                        <i class="fas fa-users"></i> Voir tous
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
                    <p>Aucun manager disponible actuellement.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

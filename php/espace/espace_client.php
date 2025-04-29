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

    if (!$user || $user['user_type'] !== 'client') {
        header("Location: ../login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $client = $stmt->fetch();

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
    <title>Espace Client | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50; 
            --secondary: #3498db; 
            --accent: #e74c3c; 
            --light: #ecf0f1; 
            --dark: #1a252f; 
            --text: #34495e; 
            --text-light: #7f8c8d; 
            --white: #ffffff;
            --success: #27ae60; 
            --warning: #f39c12; 
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text);
            margin: 0;
            padding: 0;
            background-color: var(--light);
            background-image: 
                linear-gradient(to bottom, rgba(236, 240, 241, 0.9), rgba(236, 240, 241, 1)),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%232c3e50" opacity="0.03"/><path d="M0,50 Q25,25 50,50 T100,50" stroke="%23e74c3c" stroke-width="0.5" opacity="0.1" fill="none"/></svg>');
        }

        .main-header {
            background-color: var(--white);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
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
            color: var(--primary);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            border: 1px solid rgba(44, 62, 80, 0.1);
        }

        .user-profile:hover {
            color: var(--secondary);
            background-color: rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
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
        }

        .user-name {
            font-weight: 500;
        }

        .main-nav {
            background-color: var(--primary);
            padding: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            border-top: 4px solid var(--secondary);
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 5px solid var(--light);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .profile-card:hover .profile-avatar {
            border-color: var(--secondary);
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

        .client-badge {
            background-color: var(--secondary);
            color: white;
            padding: 0.3rem 1rem;
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
            padding: 0.8rem;
            border-radius: 8px;
            transition: all 0.3s;
            background-color: rgba(236, 240, 241, 0.5);
        }

        .info-item:hover {
            background-color: rgba(52, 152, 219, 0.1);
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
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            border-top: 3px solid var(--secondary);
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light);
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
            background-color: var(--secondary);
            color: var(--white);
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
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
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--light);
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .project-header {
            background-color: var(--primary);
            color: white;
            padding: 0.8rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            color: var(--white);
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

        .project-progress {
            margin-top: 1rem;
        }

        .progress-bar {
            height: 6px;
            background-color: var(--light);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 0.3rem;
        }

        .progress-fill {
            height: 100%;
            background-color: var(--secondary);
            border-radius: 3px;
            width: <?php echo rand(30, 100); ?>%;
            transition: width 0.5s ease;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            border-top: 3px solid var(--secondary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .team-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 2px solid var(--light);
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

        .client-stats {
            display: flex;
            justify-content: space-around;
            margin: 1.5rem 0;
            background-color: rgba(236, 240, 241, 0.7);
            padding: 1rem;
            border-radius: 10px;
        }

        .client-stat {
            text-align: center;
            padding: 0.5rem;
        }

        .client-stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--secondary);
            margin-bottom: 0.3rem;
        }

        .client-stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .timeline {
            position: relative;
            margin: 1.5rem 0;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: var(--secondary);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--secondary);
            border: 2px solid var(--white);
        }

        .timeline-date {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 0.3rem;
        }

        .timeline-content {
            background-color: var(--white);
            padding: 0.8rem;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid, .client-stats {
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
            <h2 class="profile-name"><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></h2>
            <h3 class="profile-title">
                Client
                <span class="client-badge">CTM Premium</span>
            </h3>
            
            <div class="profile-info">
                <div class="info-item">
                    <i class="fas fa-envelope info-icon"></i>
                    <span><?php echo htmlspecialchars($client['email']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone info-icon"></i>
                    <span><?php echo htmlspecialchars($client['phone'] ?? 'Non renseigné'); ?></span>
                </div>
                
                <div class="client-stats">
                    <div class="client-stat">
                        <div class="client-stat-value"><?php echo htmlspecialchars($stats['total_projects'] ?? 0); ?></div>
                        <div class="client-stat-label">Projets</div>
                    </div>
                    <div class="client-stat">
                        <div class="client-stat-value"><?php echo htmlspecialchars($stats['completed_projects'] ?? 0); ?></div>
                        <div class="client-stat-label">Terminés</div>
                    </div>
                    <div class="client-stat">
                        <div class="client-stat-value"><?php echo count($equipe); ?></div>
                        <div class="client-stat-label">Experts</div>
                    </div>
                </div>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date">Aujourd'hui</div>
                        <div class="timeline-content">
                            Nouveau message concernant votre projet "Site e-commerce"
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">Hier</div>
                        <div class="timeline-content">
                            Version préliminaire reçue pour approbation
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">5 juin 2025</div>
                        <div class="timeline-content">
                            Début de collaboration avec notre équipe
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Mes Projets en Cours</h2>
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
                                    
                                    <div class="project-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo rand(30, 100); ?>%"></div>
                                        </div>
                                        <div class="progress-text">
                                            <span>Avancement</span>
                                            <span><?php echo rand(30, 100); ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-light);">Aucun projet en cours. Lancez votre premier projet!</p>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Statistiques</h2>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-project-diagram"></i></div>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['total_projects'] ?? 0); ?></div>
                        <div class="stat-label">Projets actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['completed_projects'] ?? 0); ?></div>
                        <div class="stat-label">Projets terminés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                        <div class="stat-value"><?php echo count($equipe); ?></div>
                        <div class="stat-label">Experts dédiés</div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Mon Équipe Dédiée</h2>
                    <a href="../employees.php?user_type=client" class="btn btn-primary">
                        <i class="fas fa-search"></i> Trouver un expert
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
                    <p style="text-align: center; color: var(--text-light);">Aucun expert assigné pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

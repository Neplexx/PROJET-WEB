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

    if (!$user || $user['user_type'] !== 'employeur') {
        header("Location: ../login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $employeur = $stmt->fetch();

    // Récupérer les informations de l'entreprise
    $stmt = $pdo->prepare("SELECT * FROM employers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $entreprise = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT p.*, COUNT(a.application_id) as applications_count
        FROM projects p
        LEFT JOIN applications a ON p.project_id = a.project_id
        WHERE p.employer_id = ?
        GROUP BY p.project_id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$entreprise['employer_id']]);
    $projets = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT u.user_id, u.first_name, u.last_name, u.email, e.years_experience
        FROM users u
        JOIN editors e ON u.user_id = e.user_id
        WHERE u.user_id IN (
            SELECT editor_id FROM applications 
            WHERE project_id IN (
                SELECT project_id FROM projects WHERE employer_id = ?
            )
        )
        LIMIT 4
    ");
    $stmt->execute([$entreprise['employer_id']]);
    $collaborateurs = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_projects,
            SUM(CASE WHEN status = 'terminé' THEN 1 ELSE 0 END) as completed_projects,
            SUM(budget) as total_budget
        FROM projects
        WHERE employer_id = ?
    ");
    $stmt->execute([$entreprise['employer_id']]);
    $stats = $stmt->fetch();

} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Employeur | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50; /* Bleu marine professionnel */
            --secondary: #3498db; /* Bleu clair */
            --accent: #e74c3c; /* Rouge pour les actions importantes */
            --light: #ecf0f1; /* Fond très clair */
            --dark: #1a252f; /* Texte foncé */
            --text: #34495e; /* Couleur de texte principale */
            --text-light: #7f8c8d; /* Texte secondaire */
            --white: #ffffff;
            --success: #27ae60; /* Vert pour les succès */
            --warning: #f39c12; /* Orange pour les avertissements */
            --corporate: #2980b9; /* Bleu d'entreprise */
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
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%232c3e50" opacity="0.03"/><path d="M0,50 L100,50 M50,0 L50,100" stroke="%233498db" stroke-width="0.5" opacity="0.1" fill="none"/></svg>');
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
            color: var(--corporate);
            background-color: rgba(41, 128, 185, 0.1);
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
            border-color: var(--corporate);
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
            border-top: 4px solid var(--corporate);
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
            border-color: var(--corporate);
        }

        .profile-name {
            color: var(--primary);
            margin: 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .profile-title {
            color: var(--corporate);
            margin: 0 0 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .employer-badge {
            background-color: var(--corporate);
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
            background-color: rgba(41, 128, 185, 0.1);
            transform: translateX(5px);
        }

        .info-icon {
            color: var(--corporate);
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
            border-top: 3px solid var(--corporate);
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
            background-color: var(--corporate);
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
            background-color: var(--corporate);
            color: var(--white);
            box-shadow: 0 3px 10px rgba(41, 128, 185, 0.3);
        }

        .btn-primary:hover {
            background-color: #2573a7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(41, 128, 185, 0.4);
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
            background-color: var(--corporate);
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

        .project-applications {
            display: flex;
            align-items: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--corporate);
            font-weight: 500;
        }

        .project-applications i {
            color: var(--corporate);
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
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            border-top: 3px solid var(--corporate);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--corporate);
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
            color: var(--corporate);
            font-size: 0.8rem;
            margin: 0;
        }

        /* Éléments spécifiques employeur */
        .company-info {
            margin: 1.5rem 0;
            padding: 1rem;
            background-color: rgba(236, 240, 241, 0.7);
            border-radius: 10px;
            border-left: 3px solid var(--corporate);
        }

        .company-name {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .company-detail {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .company-detail i {
            color: var(--corporate);
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        .employer-stats {
            display: flex;
            justify-content: space-around;
            margin: 1.5rem 0;
            background-color: rgba(236, 240, 241, 0.7);
            padding: 1rem;
            border-radius: 10px;
        }

        .employer-stat {
            text-align: center;
            padding: 0.5rem;
        }

        .employer-stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--corporate);
            margin-bottom: 0.3rem;
        }

        .employer-stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid, .employer-stats {
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
            <h2 class="profile-name"><?php echo htmlspecialchars($employeur['first_name'] . ' ' . $employeur['last_name']); ?></h2>
            <h3 class="profile-title">
                Employeur
                <span class="employer-badge">CTM Pro</span>
            </h3>
            
            <div class="profile-info">
                <div class="info-item">
                    <i class="fas fa-envelope info-icon"></i>
                    <span><?php echo htmlspecialchars($employeur['email']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone info-icon"></i>
                    <span><?php echo htmlspecialchars($employeur['phone'] ?? 'Non renseigné'); ?></span>
                </div>
                
                <div class="company-info">
                    <div class="company-name"><?php echo htmlspecialchars($entreprise['company_name'] ?? 'Entreprise non renseignée'); ?></div>
                    <div class="company-detail">
                        <i class="fas fa-industry"></i>
                        <span><?php echo htmlspecialchars($entreprise['industry'] ?? 'Secteur non renseigné'); ?></span>
                    </div>
                    <div class="company-detail">
                        <i class="fas fa-users"></i>
                        <span>Taille: <?php echo htmlspecialchars($entreprise['company_size'] ?? 'Non renseignée'); ?></span>
                    </div>
                </div>
                
                <div class="employer-stats">
                    <div class="employer-stat">
                        <div class="employer-stat-value"><?php echo htmlspecialchars($stats['total_projects'] ?? 0); ?></div>
                        <div class="employer-stat-label">Projets</div>
                    </div>
                    <div class="employer-stat">
                        <div class="employer-stat-value"><?php echo htmlspecialchars($stats['completed_projects'] ?? 0); ?></div>
                        <div class="employer-stat-label">Terminés</div>
                    </div>
                    <div class="employer-stat">
                        <div class="employer-stat-value"><?php echo htmlspecialchars($stats['total_budget'] ?? 0); ?>€</div>
                        <div class="employer-stat-label">Budget total</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Mes Projets Actifs</h2>
                    </a>
                </div>
                
                <?php if (!empty($projets)): ?>
                    <div class="projects-grid">
                        <?php foreach ($projets as $projet): ?>
                            <div class="project-card">
                                <div class="project-header">
                                    <span>Projet #<?php echo htmlspecialchars($projet['project_id']); ?></span>
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
                    <p style="text-align: center; color: var(--text-light);">Aucun projet actif. Créez votre premier projet!</p>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Statistiques Entreprise</h2>
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
                        <div class="stat-icon"><i class="fas fa-euro-sign"></i></div>
                        <div class="stat-value"><?php echo htmlspecialchars($stats['total_budget'] ?? 0); ?>€</div>
                        <div class="stat-label">Budget engagé</div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Mes Collaborateurs</h2>
                    <a href="../employees.php?user_type=employeur" class="btn btn-primary">
                        <i class="fas fa-search"></i> Trouver des talents
                    </a>
                </div>
                
                <?php if (!empty($collaborateurs)): ?>
                    <div class="team-grid">
                        <?php foreach ($collaborateurs as $collaborateur): ?>
                            <div class="team-member">
                                <img src="../../pictures/photoDeProfil.png" alt="Photo de profil" class="team-avatar">
                                <div class="team-info">
                                    <h4 class="team-name"><?php echo htmlspecialchars($collaborateur['first_name'] . ' ' . $collaborateur['last_name']); ?></h4>
                                    <p class="team-role"><?php echo htmlspecialchars($collaborateur['years_experience']); ?> ans d'expérience</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-light);">Aucun collaborateur actif pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

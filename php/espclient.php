<?php 
include '../php/theme.php';
session_start();
$servername ='localhost'; 
$username ='root'; 
$password ='root'; 
$dbname='ctmdata';
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparation de la requête de mise à jour
        $stmt = $conn->prepare("UPDATE users SET 
            first_name = :first_name, 
            last_name = :last_name, 
            email = :email, 
            phone = :phone, 
            bio = :bio,
            updated_at = NOW()
            WHERE user_id = :user_id");

        // Exécution avec les nouvelles valeurs
        $stmt->execute([
            ':first_name' => $_POST['first_name'],
            ':last_name' => $_POST['last_name'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'],
            ':bio' => $_POST['bio'],
            ':user_id' => $_SESSION['user_id']
        ]);

        // Rafraîchir les données utilisateur
        $requser = $conn->prepare('SELECT * FROM users WHERE user_id = ?');
        $requser->execute(array($_SESSION['user_id']));
        $user_info = $requser->fetch();

        $success_message = "Vos informations ont été mises à jour avec succès.";
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_SESSION['user_id'])) {
        $requser = $conn->prepare('SELECT * FROM users WHERE user_id = ?');
        $requser->execute(array($_SESSION['user_id']));
        $user_info = $requser->fetch();
    } else {
        header("Location: ../index.html");
        exit();
    }
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr" class="<?php echo htmlspecialchars($current_theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Profil utilisateur CTM">
    <meta name="author" content="GLONOU Stefen">
    <title>Profil | CTM</title>
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
            --success: #2ecc71;
            --warning: #f39c12;
            --info: #3498db;
            --danger: #e74c3c;
        }
        /* Styles pour le thème clair */
        html.light, .container.light {
            --background-color: #f8f9fa;
            --text-color: #202124;
            --section-background: #ffffff;
            --button-background: #2ecc71;
            --button-hover: #27ae60;
        }

        /* Styles pour le thème sombre */
        html.dark, .container.dark {
            --background-color: #202124;
            --text-color: #f8f9fa;
            --section-background: #2e2e2e;
            --button-background: #34495e;
            --button-hover: #2c3e50;
        }

        /* Styles pour le thème par défaut */
        html.default, .container.default {
            --background-color: #ffffff;
            --text-color: #000000;
            --section-background: #f0f0f0;
            --button-background: #3498db;
            --button-hover: #2980b9;
        }

        /* Application des variables */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            transition: background-color 0.4s, color 0.4s;
        }

        .section {
            background-color: var(--section-background);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .button {
            background-color: var(--button-background);
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .button:hover {
            background-color: var(--button-hover);
        }
        /* Base Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text);
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
        }

        /* Profile Container */
        .profile-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .profile-sidebar {
            width: 300px;
            background-color: var(--white);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .profile-card {
            padding: 2rem 1.5rem;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--light);
            margin-bottom: 1rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-info h2 {
            margin: 0.5rem 0 0;
            color: var(--primary);
            font-size: 1.5rem;
        }

        .profile-role {
            color: var(--text-light);
            margin: 0.2rem 0 1rem;
            font-size: 0.9rem;
        }

        .profile-rating {
            color: var(--warning);
            font-size: 0.9rem;
        }

        .profile-rating span {
            color: var(--text);
            margin-left: 0.5rem;
            font-weight: 500;
        }

        .profile-menu {
            margin-top: 2rem;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            margin-bottom: 0.5rem;
            color: var(--text);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .menu-item i {
            width: 24px;
            text-align: center;
            margin-right: 1rem;
            color: var(--text-light);
        }

        .menu-item:hover {
            background-color: var(--light);
            color: var(--primary);
        }

        .menu-item:hover i {
            color: var(--secondary);
        }

        .menu-item.logout {
            color: var(--danger);
        }

        .menu-item.logout:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }

        /* Main Content Styles */
        .profile-content {
            flex: 1;
            padding: 2rem;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .content-header h1 {
            color: var(--primary);
            font-size: 2rem;
            margin: 0;
        }

        .content-nav ul {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .content-nav li {
            margin-left: 1.5rem;
        }

        .content-nav a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .content-nav a:hover {
            color: var(--secondary);
        }

        .logout-link {
            color: var(--danger) !important;
        }

        /* Profile Sections */
        .profile-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .section-header h2 {
            margin: 0;
            font-size: 1.4rem;
            color: var(--primary);
        }

        .section-header i {
            font-size: 1.2rem;
            color: var(--secondary);
            margin-right: 0.8rem;
        }

        .edit-btn, .view-all-btn {
            position: absolute;
            right: 0;
            background-color: transparent;
            border: none;
            color: var(--secondary);
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 0.3rem 0.5rem;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .edit-btn:hover, .view-all-btn:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }

        .edit-btn i, .view-all-btn i {
            margin-left: 0.5rem;
            font-size: 0.8rem;
        }

        /* Personal Info Section */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .info-item {
            margin-bottom: 0.5rem;
        }

        .info-label {
            display: block;
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 0.3rem;
        }

        .info-value {
            font-weight: 500;
            color: var(--text);
        }

        /* Form Styles */
        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
        }

        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }

        .form-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            grid-column: 1 / -1;
        }

        .save-btn {
            background-color: var(--success);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .save-btn:hover {
            background-color: #27ae60;
        }

        .cancel-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .cancel-btn:hover {
            background-color: #c0392b;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        /* Projects Section */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .project-card {
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .project-card h3 {
            margin: 0 0 0.5rem;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .project-status {
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 0.8rem;
        }

        .in-progress {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--info);
        }

        .completed {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success);
        }

        .pending {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        .project-date {
            font-size: 0.85rem;
            color: var(--text-light);
            margin: 0.5rem 0;
        }

        .project-progress {
            height: 6px;
            background-color: var(--light);
            border-radius: 3px;
            margin-top: 1rem;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background-color: var(--info);
            border-radius: 3px;
        }

        .project-rating {
            color: var(--warning);
            margin-top: 0.8rem;
            font-size: 0.9rem;
        }

        .start-project-btn {
            background-color: var(--secondary);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-top: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .start-project-btn:hover {
            background-color: #c0392b;
        }

        /* Activity Feed Section */
        .activity-list {
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .activity-item {
            display: flex;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item i {
            font-size: 1.2rem;
            color: var(--secondary);
            margin-right: 1rem;
            margin-top: 0.2rem;
        }

        .activity-content p {
            margin: 0;
            font-weight: 500;
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 0.3rem;
            display: block;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .profile-container {
                flex-direction: column;
            }
            
            .profile-sidebar {
                width: 100%;
                box-shadow: none;
                border-bottom: 1px solid #eee;
            }
            
            .profile-card {
                display: flex;
                align-items: center;
                padding: 1.5rem;
            }
            
            .profile-header {
                display: flex;
                align-items: center;
                text-align: left;
                margin-bottom: 0;
                margin-right: 2rem;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
                margin-bottom: 0;
                margin-right: 1.5rem;
            }
            
            .profile-menu {
                margin-top: 0;
                display: flex;
                flex-wrap: wrap;
            }
            
            .menu-item {
                margin-bottom: 0;
                margin-right: 1rem;
            }
        }

        @media (max-width: 768px) {
            .profile-card {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
                margin-right: 0;
                margin-bottom: 1.5rem;
            }
            
            .profile-avatar {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .profile-menu {
                justify-content: center;
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .content-nav {
                margin-top: 1rem;
            }
            
            .content-nav ul {
                flex-wrap: wrap;
            }
            
            .content-nav li {
                margin: 0.3rem 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .profile-content {
                padding: 1.5rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .projects-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container" <?php echo htmlspecialchars($current_theme); ?>>
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-header">
                    <img src="../pictures/photo de profil neutre.png" alt="Photo de profil" class="profile-avatar">
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user_info['first_name']); ?></h2>
                        <p class="profile-role">Client</p>
                        <div class="profile-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <span>4.0</span>
                        </div>
                    </div>
                </div>
                <nav class="profile-menu">
                    <ul>
                        <li>
                            <a href="../html/accueil.html" class="menu-item">
                                <i class="fas fa-home"></i>
                                <span>Accueil</span>
                            </a>
                        </li>
                        <li>
                            <a href="../php/settings.php" class="menu-item">
                                <i class="fas fa-cog"></i>
                                <span>Paramètres</span>
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="menu-item logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Déconnexion</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="profile-content">
            <header class="content-header">
                <h1>Mon Profil</h1>
                <nav class="content-nav">
                    <ul>
                        <li><a href="../html/accueil.html">Accueil</a></li>
                        <li><a href="../html/recherche.html">Recherche</a></li>
                        <li><a href="logout.php" class="logout-link">Déconnexion</a></li>
                    </ul>
                </nav>
            </header>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="profile-sections">
                <section class="personal-info">
                    <div class="section-header">
                        <i class="fas fa-user-circle"></i>
                        <h2>Informations personnelles</h2>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Prénom</span>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user_info['first_name']); ?>" required>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Nom</span>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user_info['last_name']); ?>" required>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Téléphone</span>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_info['phone']); ?>">
                            </div>
                            <div class="info-item">
                                <span class="info-label">Bio</span>
                                <textarea name="bio" class="form-control"><?php echo htmlspecialchars($user_info['bio'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" name="update_profile" class="save-btn">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="recent-projects">
                    <div class="section-header">
                        <i class="fas fa-project-diagram"></i>
                        <h2>Projets récents</h2>
                        <button class="view-all-btn">
                            Voir tout <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                    <div class="projects-grid">
                        <div class="project-card">
                            <h3>Projet 1</h3>
                            <p class="project-status in-progress">En cours</p>
                            <p class="project-date">Débuté le 10/06/2024</p>
                            <div class="project-progress">
                                <div class="progress-bar" style="width: 65%"></div>
                            </div>
                        </div>
                        <div class="project-card">
                            <h3>Projet 2</h3>
                            <p class="project-status completed">Terminé</p>
                            <p class="project-date">Terminé le 05/06/2024</p>
                            <div class="project-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                        </div>
                        <div class="project-card">
                            <h3>Projet 3</h3>
                            <p class="project-status pending">En attente</p>
                            <p class="project-date">Créé le 01/06/2024</p>
                            <button class="start-project-btn">
                                Commencer
                            </button>
                        </div>
                    </div>
                </section>

                <section class="activity-feed">
                    <div class="section-header">
                        <i class="fas fa-history"></i>
                        <h2>Activité récente</h2>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <i class="fas fa-check-circle"></i>
                            <div class="activity-content">
                                <p>Projet "Site Web" marqué comme terminé</p>
                                <span class="activity-time">Il y a 2 jours</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-comment"></i>
                            <div class="activity-content">
                                <p>Nouveau message de Pierre Calvasse</p>
                                <span class="activity-time">Il y a 3 jours</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-file-upload"></i>
                            <div class="activity-content">
                                <p>Fichiers téléchargés pour le Projet 2</p>
                                <span class="activity-time">Il y a 5 jours</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>

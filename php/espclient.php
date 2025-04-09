<?php 
session_start();
$servername ='localhost'; 
$username ='root'; 
$password ='root'; 
$dbname='ctmdata';
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Profil utilisateur CTM">
    <meta name="author" content="GLONOU Stefen">
    <title>Profil | iTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../styles/espclient.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-header">
                    <img src="../pictures/photo de profil neutre.png" alt="Photo de profil" class="profile-avatar">
                    <div class="profile-info">
                        <h2><?php echo $user_info['first_name']; ?></h2>
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
                            <a href="accueil.html" class="menu-item">
                                <i class="fas fa-home"></i>
                                <span>Accueil</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="menu-item">
                                <i class="fas fa-cog"></i>
                                <span>Paramètres</span>
                            </a>
                        </li>
                        <li>
                            <a href="../index.html" class="menu-item logout">
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
                        <li><a href="accueil.html">Accueil</a></li>
                        <li><a href="recherche.html">Recherche</a></li>
                        <li><a href="../index.html" class="logout-link">Déconnexion</a></li>
                    </ul>
                </nav>
            </header>

            <div class="profile-sections">
                <section class="personal-info">
                    <div class="section-header">
                        <i class="fas fa-user-circle"></i>
                        <h2>Informations personnelles</h2>
                        <button class="edit-btn">
                            <i class="fas fa-pencil-alt"></i> Modifier
                        </button>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Nom Complet</span>
                            <span class="info-value"><?php echo $user_info['first_name']. ' ' . $user_info['last_name']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo $user_info['email'];?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Téléphone</span>
                            <span class="info-value"><?php echo $user_info['phone'];?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Adresse</span>
                            <span class="info-value"><?php echo  $user_info['address'];?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date d'inscription</span>
                            <span class="info-value"><?php echo $user_info['created_at'];?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Type de compte</span>
                            <span class="info-value">Standard</span>
                        </div>
                    </div>
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

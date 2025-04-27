<!DOCTYPE html>
<?php 
session_start();
require_once('verification.php');

// Configuration et connexion à la base de données
$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'ctmdata';
$charset = 'utf8mb4';

$dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

// Récupération du rôle de l'utilisateur
$user_id = $_SESSION['user_id'];
$query = "SELECT user_type FROM users WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user) {
    $user_role = $user['user_type'];
    $role_translations = [
        'client' => 'Client',
        'graphiste' => 'Graphiste',
        'manager' => 'Manager',
        'développeur' => 'Développeur',
        'beatmaker' => 'Beatmaker',
        'monteur' => 'Monteur',
        'employeur' => 'Employeur',
        'admin' => 'Administrateur'
    ];
    $display_role = $role_translations[$user_role] ?? $user_role;
} else {
    $display_role = 'Utilisateur';
}
?>
<style>
.user-profile-container {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.user-profile, .user-role {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #333;
    padding: 5px 0;
}

.user-role {
    font-size: 0.9em;
    color: #666;
}

.user-role:hover {
    color: #000;
    text-decoration: underline;
}
</style>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CTM - Plateforme de mise en relation entre monteurs et clients">
    <meta name="keywords" content="monteurs, emploi, services, Junia, ISEN, école d'ingénieur">
    <meta name="author" content="Detalle Mattéo et équipe">
    <link rel="icon" type="image/x-icon" href="../pictures/logo.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../styles/accueil.css">
    <title>Accueil | CTM</title>
</head>
<body>
    <header class="main-header">
        <div class="logo-container">
            <a href="../index.html" class="logo-link">
                <img src="../pictures/logorond.png" alt="Logo CTM" class="logo">
            </a>
        </div>
        <nav class="user-nav">
            <div class="user-profile-container">
                <a href="../php/espclient.php" class="user-profile">
                    <img src="../pictures/photoDeProfil.png" alt="Photo de profil" class="user-avatar">
                    <span class="user-name">Mon compte</span>
                </a>
                <a href="../php/verifroles.php" class="user-role">
                    <span>Votre espace <?php echo htmlspecialchars($display_role); ?></span>
                </a>
            </div>
        </nav>
    </header>   
     <nav class="main-nav">
        <ul class="nav-list">
            <li class="nav-item"><a href="accueil.php" class="nav-link active">Accueil</a></li>
            <li class="nav-item"><a href="recherche.php" class="nav-link">Recherche</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <section class="testimonials-section">
            <h2 class="section-title">Ce que disent nos utilisateurs</h2>
            
            <div class="testimonials-grid">
                <div class="testimonial-card admin-card">
                    <div class="testimonial-header">
                        <span class="testimonial-author">admin@ctm.com</span>
                        <span class="testimonial-date">29 Novembre 2024</span>
                    </div>
                    <div class="testimonial-content">
                        <p>Voilà la présentation de notre site. Surtout, à la moindre découverte de problème, n'hésitez pas à nous contacter via l'accès support (le bouton en bas à droite).</p>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <span class="testimonial-author">chevaldu72@yahoo.com</span>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="testimonial-date">1er Décembre 2024</span>
                    </div>
                    <div class="testimonial-content">
                        <p>Grâce à vous j'ai enfin trouvé mon premier monteur !!!</p>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <span class="testimonial-author">kak_farid@gmail.com</span>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                        <span class="testimonial-date">2 Décembre 2024</span>
                    </div>
                    <div class="testimonial-content">
                        <p>J'aime beaucoup le concept !</p>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <span class="testimonial-author">kevin@gmail.com</span>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <i class="far fa-star"></i>
                            <i class="far fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                        <span class="testimonial-date">2 Décembre 2024</span>
                    </div>
                    <div class="testimonial-content">
                        <p>Je n'ai pas trouvé de monteur.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="slider-section">
            <div class="slider-container">
                <div class="slider">
                    <div class="slide">
                        <img src="../pictures/pro1.png" alt="Projet 1">
                    </div>
                    <div class="slide">
                        <img src="../pictures/pro2.png" alt="Projet 2">
                    </div>
                    <div class="slide">
                        <img src="../pictures/pro3.png" alt="Projet 3">
                    </div>
                    <div class="slide">
                        <img src="../pictures/pro1.png" alt="Projet 4">
                    </div>
                </div>
                <div class="slider-controls">
                    <button class="slider-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
                    <button class="slider-btn next-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </section>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.querySelector('.slider');
        const slides = document.querySelectorAll('.slide');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        
        let currentSlide = 0;
        const slideCount = slides.length;
        
        function goToSlide(slideIndex) {
            if (slideIndex < 0) {
                currentSlide = slideCount - 1;
            } else if (slideIndex >= slideCount) {
                currentSlide = 0;
            } else {
                currentSlide = slideIndex;
            }
            
            slider.style.transform = `translateX(-${currentSlide * 100}%)`;
        }
        
        function nextSlide() {
            goToSlide(currentSlide + 1);
        }
        
        function prevSlide() {
            goToSlide(currentSlide - 1);
        }
        
        let slideInterval = setInterval(nextSlide, 5000);
        
        function resetInterval() {
            clearInterval(slideInterval);
            slideInterval = setInterval(nextSlide, 5000);
        }
        
        nextBtn.addEventListener('click', function() {
            nextSlide();
            resetInterval();
        });
        
        prevBtn.addEventListener('click', function() {
            prevSlide();
            resetInterval();
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight') {
                nextSlide();
                resetInterval();
            } else if (e.key === 'ArrowLeft') {
                prevSlide();
                resetInterval();
            }
        });
        
        goToSlide(0);
    });
    </script>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-links">
                <a href="support.php" class="support-btn">
                    <i class="fas fa-headset"></i> Contacter le support
                </a>
                
                <div class="social-links">
                    <a href="https://discord.gg/q6Fd6tJCaY" target="_blank" class="social-link">
                        <i class="fab fa-discord"></i>
                    </a>
                    <a href="https://github.com/Neplexx/PROJET-WEB" target="_blank" class="social-link">
                        <i class="fab fa-github"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>

<!DOCTYPE html>
<?php 
session_start();
require_once('verification.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}
?>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page de recherche de services en tout genre">
    <meta name="keywords" content="Junia, ISEN, école d'ingénieur">
    <meta name="author" content="Detalle Mattéo, ajoutez vos noms au commit">
    <link rel="icon" type="image/x-icon" href="../pictures/logo.ico">
    <link rel="stylesheet" type="text/css" href="../styles/recherche.css">
    <title>Recherche | CTM</title>
</head>
<body>
    <header>
        <a id="index" href="../index.html"><img id="indexImage" src="../pictures/logorond.png" alt="Logo CTM"></a>
        <a id="espaceClient" href="espclient.php"><img id="espaceClientImage" src="../pictures/photoDeProfil.png" alt="Photo de profil"></a>
    </header>
    <nav>
        <ul>
            <li><a class="pageNonActive" href="accueil.php">Accueil</a></li>
            <li><a id="pageActive" href="recherche.html">Recherche</a></li>
        </ul>
    </nav>
<style>
#search-section {
    width: 90%;
    max-width: 800px;
    margin: 30px auto;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.search-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.search-container {
    display: flex;
    width: 100%;
}

.search-input {
    flex: 1;
    padding: 12px 20px;
    border: 2px solid #ddd;
    border-radius: 30px 0 0 30px;
    font-size: 16px;
    outline: none;
    transition: border-color 0.3s;
}

.search-input:focus {
    border-color: #4a6fa5;
}

.search-button {
    padding: 12px 20px;
    background-color: #4a6fa5;
    color: white;
    border: none;
    border-radius: 0 30px 30px 0;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-button:hover {
    background-color: #3a5a8a;
}

.filter-options {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.filter-select {
    padding: 10px 15px;
    border: 2px solid #ddd;
    border-radius: 30px;
    font-size: 14px;
    outline: none;
    background-color: white;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-select:hover {
    border-color: #4a6fa5;
}

/* Responsive */
@media (max-width: 600px) {
    .search-container {
        flex-direction: column;
    }
    
    .search-input {
        border-radius: 30px;
        margin-bottom: 10px;
    }
    
    .search-button {
        border-radius: 30px;
    }
    
    .filter-options {
        flex-direction: column;
    }
    
    .filter-select {
        width: 100%;
    }
}
</style>
    <main>
        <h1>Nos Services Disponibles</h1>
        <section id="sectionRecherche">
            <article>
                <label for="afficher-monteur">
                    <div class="service-header">
                        <i class="fas fa-film service-icon"></i>
                        <h2>Monteur</h2>
                        <p>Un expert en montage vidéo pour vos projets</p>
                        <span class="afficheCheckbox">Voir plus <i class="fas fa-chevron-down"></i></span>
                    </div>
                </label>
                <input type="checkbox" id="afficher-monteur" class="service-checkbox"/>
                <div class="noms">
                    <ul>
                        <li><a href="../php/employees.php?keyword=Max+Castagne"><i class="fas fa-user"></i> Max Castagne</a></li>
                        <li><a href="../php/employees.php?keyword=Jack+Chrass"><i class="fas fa-user"></i> Jack Chrass</a></li>
                        <li><a href="../php/employees.php?keyword=Pierre+Calvasse"><i class="fas fa-user"></i> Pierre Calvasse</a></li>
                        <li><a href="../php/employees.php?keyword=Quentin+Michel"><i class="fas fa-user"></i> Quentin Michel</a></li>
                    </ul>
                    <label for="afficher-monteur" class="close-btn">
                        <i class="fas fa-times"></i> Fermer
                    </label>
                </div>
            </article>               
            <article>
                <label for="afficher-graphiste">
                    <div class="service-header">
                        <i class="fas fa-paint-brush service-icon"></i>
                        <h2>Graphiste</h2>
                        <p>Un expert en design graphique pour vos projets</p>
                        <span class="afficheCheckbox">Voir plus <i class="fas fa-chevron-down"></i></span>
                    </div>
                </label>
                <input type="checkbox" id="afficher-graphiste" class="service-checkbox"/>
                <div class="noms">
                    <ul>
                        <li><a href="../php/employees.php?keyword=Luc+Pliuc">Luc Pliuc</a></li>
                        <li><a href="../php/employees.php?keyword=Khaled+Prime">Khaled Prime</a></li>
                        <li><a href="../php/employees.php?keyword=Martin+Jiopo">Martin Jiopo</a></li>
                        <li><a href="../php/employees.php?keyword=Lucien+Dekune">Lucien Dekune</a></li>
                    </ul>
                    <label for="afficher-graphiste" class="close-btn">
                        <i class="fas fa-times"></i> Fermer
                    </label>
                </div>
            </article>
            <article>
                <label for="afficher-manager">
                    <div class="service-header">
                        <i class="fas fa-briefcase service-icon"></i>
                        <h2>Manager</h2>
                        <p>Un expert en gestion de projet pour vous assister</p>
                        <span class="afficheCheckbox">Voir plus <i class="fas fa-chevron-down"></i></span>
                    </div>
                </label>
                <input type="checkbox" id="afficher-manager" class="service-checkbox"/>
                <div class="noms">
                    <ul>
                        <li><a href="../php/employees.php?keyword=Julien+Martin">Julien Martin</a></li>
                        <li><a href="../php/employees.php?keyword=Élodie+Dupont">Élodie Dupont</a></li>
                        <li><a href="../php/employees.php?keyword=Antoine+Bernard">Antoine Bernard</a></li>
                        <li><a href="../php/employees.php?keyword=Claire+Lefèvre">Claire Lefèvre</a></li>
                    </ul>
                    <label for="afficher-manager" class="close-btn">
                        <i class="fas fa-times"></i> Fermer
                    </label>
                </div>
            </article>
            <article>
                <label for="afficher-developpeur">
                    <div class="service-header">
                        <i class="fas fa-code service-icon"></i>
                        <h2>Développeur</h2>
                        <p>Expert en développement de sites webs/logiciels</p>
                        <span class="afficheCheckbox">Voir plus <i class="fas fa-chevron-down"></i></span>
                    </div>
                </label>
                <input type="checkbox" id="afficher-developpeur" class="service-checkbox"/>
                <div class="noms">
                    <ul>
                        <li><a href="../php/employees.php?keyword=Lucas+Girard">Lucas Girard</a></li>
                        <li><a href="../php/employees.php?keyword=Camille+Moreau">Camille Moreau</a></li>
                        <li><a href="../php/employees.php?keyword=Thomas+Dubois">Thomas Dubois</a></li>
                        <li><a href="../php/employees.php?keyword=Sophie+Laurent">Sophie Laurent</a></li>
                    </ul>
                    <label for="afficher-developpeur" class="close-btn">
                        <i class="fas fa-times"></i> Fermer
                    </label>
                </div>
            </article> 
            <article>
                <label for="afficher-beatmaker">
                    <div class="service-header">
                        <i class="fas fa-music service-icon"></i>
                        <h2>Beatmaker</h2>
                        <p>Expert en composition musicale pour tout style</p>
                        <span class="afficheCheckbox">Voir plus <i class="fas fa-chevron-down"></i></span>
                    </div>
                </label>
                <input type="checkbox" id="afficher-beatmaker" class="service-checkbox"/>
                <div class="noms">
                    <ul>
                        <li><a href="../php/employees.php?keyword=Antoine+Perrot">Antoine Perrot</a></li>
                        <li><a href="../php/employees.php?keyword=Julie+Barbier">Julie Barbier</a></li>
                        <li><a href="../php/employees.php?keyword=Maxime+Richard">Maxime Richard</a></li>
                        <li><a href="../php/employees.php?keyword=Sarah+Gauthier">Sarah Gauthier</a></li>
                    </ul>
                    <label for="afficher-beatmaker" class="close-btn">
                        <i class="fas fa-times"></i> Fermer
                    </label>
                </div>
            </article>        
        </section>
<section id="search-section">
    <form action="../php/employees.php" method="get" class="search-form">
        <div class="search-container">
            <input type="text" name="keyword" placeholder="Rechercher un professionnel..." class="search-input">
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i> Rechercher
            </button>
        </div>
        <div class="filter-options">
            <select name="user_type" class="filter-select">
                <option value="">Tous les métiers</option>
                <option value="monteur">Monteur</option>
                <option value="graphiste">Graphiste</option>
                <option value="manager">Manager</option>
                <option value="développeur">Développeur</option>
                <option value="beatmaker">Beatmaker</option>
            </select>
        </div>
    </form>
</section>
    </main>
    <footer>
        <div id="conteneurfooter">
            <a href="support.php"><button id="supportbouton"><i class="fas fa-headset"></i> Contacter Support</button></a>
            <a id="imagediscord" href="https://discord.gg/q6Fd6tJCaY" target="_blank">
                <img src="../pictures/discord.png" alt="Logo Discord">
            </a>
            <a id="imagegithub" href="https://github.com/Neplexx/PROJET-WEB" target="_blank">
                <img src="../pictures/github.png" alt="Logo GitHub">
            </a>
        </div>
    </footer>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>


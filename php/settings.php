<?php
// Liste des thèmes autorisés
$allowed_themes = ['light', 'dark', 'default'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $theme = in_array($_POST['theme'], $allowed_themes) ? $_POST['theme'] : 'light';

    // valable 30 jours
    setcookie('theme', $theme, time() + (30 * 24 * 60 * 60), '/'); 

    header('Location: settings.php');
    exit();
}

// Lecture du thème actuel depuis le cookie
$current_theme = isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], $allowed_themes) ? $_COOKIE['theme'] : 'light';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres</title>
    <style>
        /* Variables CSS pour les thèmes */
        .container.light {
            --background-color: #f8f9fa;
            --text-color: #202124;
            --section-background: #ffffff;
            --button-background: #2ecc71;
            --button-hover: #27ae60;
        }

        .container.dark {
            --background-color: #202124;
            --text-color: #f8f9fa;
            --section-background: #2e2e2e;
            --button-background: #34495e;
            --button-hover: #2c3e50;
        }

        .container.default {
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

        .container {
            display: flex;
            height: 100vh;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .sidebar {
            width: 220px;
            background-color: var(--section-background);
            border-right: 1px solid #ddd;
            padding: 20px;
        }

        .content {
            flex: 1;
            padding: 30px;
            background-color: inherit;
        }

        .section {
            background-color: var(--section-background);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .options label {
            display: block;
            margin: 10px 0;
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
    </style>
</head>
<body>

<div class="container <?php echo htmlspecialchars($current_theme); ?>">
    <div class="sidebar">
        <h2>Paramètres</h2>
        <a href="#">Général</a><br>
        <a href="#">Confidentialité</a><br>
        <a href="#">Facturation et paiements</a><br>
        <a href="#">Applications liées</a>
    </div>
    <div class="content">
        <h1>Paramètres - Général</h1>

        <form method="POST" action="settings.php">
            <div class="section">
                <h3>Apparence</h3>
                <div class="options">
                    <label>
                        <input type="radio" name="theme" value="light" <?php if ($current_theme == 'light') echo 'checked'; ?>>
                        Clair
                    </label>
                    <label>
                        <input type="radio" name="theme" value="dark" <?php if ($current_theme == 'dark') echo 'checked'; ?>>
                        Sombre
                    </label>
                    <label>
                        <input type="radio" name="theme" value="default" <?php if ($current_theme == 'default') echo 'checked'; ?>>
                        Par défaut de l'appareil
                    </label>
                </div>
            </div>
            <button type="submit" class="button">Enregistrer les modifications</button>
        </form>
    </div>
</div>

</body>
</html>
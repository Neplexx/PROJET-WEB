<?php
session_start();
// config.php
$host = 'localhost';
$db   = 'ctmdata';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
$allowed_themes = ['light', 'dark'];
$error = '';
$success = '';

// Gestion du changement de thème
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $theme = in_array($_POST['theme'], $allowed_themes) ? $_POST['theme'] : 'light';
    setcookie('theme', $theme, time() + (30 * 24 * 60 * 60), '/'); 
    header('Location: settings.php');
    exit();
}

// Lecture du thème actuel
$current_theme = isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], $allowed_themes) ? $_COOKIE['theme'] : 'light';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération des infos utilisateur
$user_id = $_SESSION['user_id'];
$user = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des informations utilisateur.";
}

// Gestion du changement d'email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_email'])) {
    $new_email = trim($_POST['new_email']);
    
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$new_email, $user_id]);
            
            if ($stmt->fetch()) {
                $error = "Cette adresse email est déjà utilisée par un autre compte.";
            } else {
                // Mettre à jour l'email
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
                $stmt->execute([$new_email, $user_id]);
                $success = "Votre adresse email a été mise à jour avec succès.";
                $user['email'] = $new_email; // Mettre à jour l'email dans le tableau user
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de la mise à jour de l'adresse email.";
        }
    }
}

// Gestion du changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password']) && isset($_POST['new_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Vérifier le mot de passe actuel
    if (!password_verify($current_password, $user['password'])) {
        $error = "Le mot de passe actuel est incorrect.";
    } elseif (strlen($new_password) < 8) {
        $error = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Les nouveaux mots de passe ne correspondent pas.";
    } else {
        // Mettre à jour le mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            $success = "Votre mot de passe a été mis à jour avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur lors de la mise à jour du mot de passe.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styles généraux */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .back-button {
            display: inline-block;
            margin-bottom: 2rem;
            padding: 0.5rem 1rem;
            background-color: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .back-button:hover {
            background-color: #1a252f;
        }
        
        /* Header du profil */
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 2rem;
            border: 5px solid #f5f5f5;
        }
        
        .profile-basic-info h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 2rem;
        }
        
        /* Sections */
        .profile-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .profile-section {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 1rem;
            color: #e74c3c;
        }
        
        /* Formulaire */
        .settings-form {
            display: grid;
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #e74c3c;
            outline: none;
        }
        
        .radio-group {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .radio-option input {
            margin: 0;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
        
        /* Thèmes */
        .theme-preview {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .theme-light, .theme-dark {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            border: 2px solid transparent;
            transition: transform 0.3s, border-color 0.3s;
        }
        
        .theme-light {
            background: linear-gradient(135deg, #f8f9fa 50%, #ffffff 50%);
        }
        
        .theme-dark {
            background: linear-gradient(135deg, #202124 50%, #2e2e2e 50%);
        }
        
        input[type="radio"]:checked + .theme-light,
        input[type="radio"]:checked + .theme-dark {
            border-color: #e74c3c;
            transform: scale(1.05);
        }
        
        /* Messages d'erreur/succès */
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-picture {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body class="<?php echo htmlspecialchars($current_theme); ?>">
    <div class="settings-container">
        <a href="espclient.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'user-avatar.jpg'); ?>" alt="Photo de profil" class="profile-picture">
            
            <div class="profile-basic-info">
                <h1>Mes Paramètres</h1>
                <p>Personnalisez votre expérience sur notre plateforme</p>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-sections">
            <section class="profile-section">
                <h2 class="section-title"><i class="fas fa-palette"></i> Apparence</h2>
                
                <form method="POST" action="settings.php" class="settings-form">
                    <div class="form-group">
                        <label class="form-label">Thème de l'interface</label>
                        <p>Choisissez entre le thème clair ou sombre pour l'interface de la plateforme.</p>
                        
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="theme" value="light" <?php if ($current_theme == 'light') echo 'checked'; ?>>
                                Clair
                            </label>
                            
                            <label class="radio-option">
                                <input type="radio" name="theme" value="dark" <?php if ($current_theme == 'dark') echo 'checked'; ?>>
                                Sombre
                            </label>
                        </div>
                        <div>
                            <p>ㅤ</p>
                        </div>                    
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </form>
            </section>
            
            <section class="profile-section">
                <h2 class="section-title"><i class="fas fa-envelope"></i> Modifier l'email</h2>
                
                <form method="POST" action="settings.php" class="settings-form">
                    <div class="form-group">
                        <label for="current_email" class="form-label">Email actuel</label>
                        <input type="email" id="current_email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_email" class="form-label">Nouvel email</label>
                        <input type="email" id="new_email" name="new_email" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Mettre à jour l'email</button>
                </form>
            </section>
            
        </div>
    </div>
</body>
</html>

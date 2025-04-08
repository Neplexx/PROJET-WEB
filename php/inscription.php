<?php
require_once 'connexion.php';
session_start();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $userType = $_POST['user_type'];
    
    // Validation
    $errors = [];
    
    if (empty($fullName)) {
        $errors[] = "Le nom complet est requis.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (empty($userType)) {
        $errors[] = "Veuillez sélectionner un type d'utilisateur.";
    }
    
    // Séparation prénom/nom
    $nameParts = explode(' ', $fullName, 2);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
    
    if (empty($errors)) {
        try {
            // Vérifier si email existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Cet email est déjà utilisé.";
            } else {
                // Hachage du mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertion
                $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, user_type) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$email, $hashedPassword, $firstName, $lastName, $userType]);
                
                // Connexion automatique
                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['email'] = $email;
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                $_SESSION['user_type'] = $userType;
                
                // Redirection
                header("Location: ../index.php");
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - CTM Platform</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        header {
            background-color: #2c3e50;
            color: white;
            padding: 1rem 0;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        #logo {
            height: 60px;
            margin-bottom: 10px;
        }
        
        h1 {
            font-size: 1.8rem;
        }
        
        .auth-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .tabs {
            display: flex;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #ddd;
        }
        
        .tab {
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            color: #7f8c8d;
            font-weight: bold;
        }
        
        .tab.active {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }
        
        .forgot-password a {
            color: #7f8c8d;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .login-link a {
            color: #3498db;
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <a href="../index.html"><img id="logo" src="../pictures/logorond.png" alt="Logo CTM"></a> 
        <h1>CTM - La Plateforme Audiovisuelle</h1>
    </header>

    <div class="auth-container">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <?php echo htmlspecialchars($error); ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="tabs">
            <div class="tab" onclick="window.location.href='login.php'">Connexion</div>
            <div class="tab active">Inscription</div>
        </div>
        
        <div class="form-container">
            <div id="register-form" class="form-content active">
                <form action="inscription.php" method="post">
                    <div class="form-group">
                        <label for="register-name">Nom complet</label>
                        <input type="text" id="register-name" name="full_name" placeholder="Entrez votre nom complet" required
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="register-email">Email</label>
                        <input type="email" id="register-email" name="email" placeholder="Entrez votre email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="register-password">Mot de passe</label>
                        <input type="password" id="register-password" name="password" placeholder="Créez un mot de passe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="register-confirm-password">Confirmez le mot de passe</label>
                        <input type="password" id="register-confirm-password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="user-type">Type d'utilisateur</label>
                        <select id="user-type" name="user_type" required>
                            <option value="">Sélectionnez un type</option>
                            <option value="monteur" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'monteur') ? 'selected' : ''; ?>>Monteur</option>
                            <option value="graphiste" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'graphiste') ? 'selected' : ''; ?>>Graphiste</option>
                            <option value="manager" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'manager') ? 'selected' : ''; ?>>Manager</option>
                            <option value="développeur" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'développeur') ? 'selected' : ''; ?>>Développeur</option>
                            <option value="beatmaker" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'beatmaker') ? 'selected' : ''; ?>>Beatmaker</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">S'inscrire</button>
                </form>
                
                <div class="login-link">
                    Déjà un compte ? <a href="login.php">Connectez-vous</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

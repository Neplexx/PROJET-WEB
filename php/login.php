<?php
session_start();

$servername = 'localhost'; 
$username = 'root'; 
$password = 'root'; 
$dbname = 'ctmdata';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch(Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

if (isset($_SESSION['user_id'])) {
    header("Location: accueil.php");
    exit();
}
/* ce sont les clés de test de Google par défaut, réalisé par Badis */
define('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
define('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_login_attempt'] = 0;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        if (isset($_POST['g-recaptcha-response'])) {
            $recaptcha_response = $_POST['g-recaptcha-response'];
            $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_data = [
                'secret' => RECAPTCHA_SECRET_KEY,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ];

            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($recaptcha_data)
                ]
            ];

            $context = stream_context_create($options);
            $recaptcha_result = file_get_contents($recaptcha_url, false, $context);
            $recaptcha_json = json_decode($recaptcha_result);

            if (!$recaptcha_json->success) {
                $error = "Veuillez vérifier que vous n'êtes pas un robot.";
            }
        } else {
            $error = "CAPTCHA manquant. Veuillez réessayer.";
        }

        $current_time = time();
        if ($_SESSION['login_attempts'] >= 5) {
            if ($current_time - $_SESSION['last_login_attempt'] < 300) { // 5 minutes d'attente
                $error = "Trop de tentatives de connexion. Veuillez patienter 5 minutes.";
            } else {
                $_SESSION['login_attempts'] = 0;
            }
        }

        if (empty($error)) {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            
            if (empty($email) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
                try {
                    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
                    $stmt->execute([$email, $password]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        $_SESSION['login_attempts'] = 0;
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        $_SESSION['user_type'] = $user['user_type'];
                        
                        header("Location: accueil.php");
                        exit();
                    } else {
                        $_SESSION['login_attempts']++;
                        $_SESSION['last_login_attempt'] = $current_time;
                        $error = "Email ou mot de passe incorrect. Tentatives restantes : " . (5 - $_SESSION['login_attempts']);
                    }
                } catch (PDOException $e) {
                    $error = "Erreur de connexion. Veuillez réessayer plus tard.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - CTM Platform</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
        
        input[type="email"],
        input[type="password"] {
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
        
        .register-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .register-link a {
            color: #3498db;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .g-recaptcha {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <header>
        <a href="../index.html"><img id="logo" src="../pictures/logorond.png" alt="Logo CTM"></a> 
        <h1>CTM - La Plateforme Audiovisuelle</h1>
    </header>

    <div class="auth-container">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="tabs">
            <div class="tab active">Connexion</div>
            <div class="tab" onclick="window.location.href='inscription.php'">Inscription</div>
        </div>
        
        <div class="form-container">
            <div id="login-form" class="form-content active">
                <form action="login.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" name="email" placeholder="Entrez votre email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password">Mot de passe</label>
                        <input type="password" id="login-password" name="password" placeholder="Entrez votre mot de passe" required>
                    </div>
                    
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                    
                    <button type="submit" class="btn">Se connecter</button>
                    
                </form>
                
                <div class="register-link">
                    Pas encore de compte ? <a href="inscription.php">Inscrivez-vous</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

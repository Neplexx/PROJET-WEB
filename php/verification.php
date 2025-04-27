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
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit();
}

?>

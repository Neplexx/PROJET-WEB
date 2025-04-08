<?php
    $servername ='localhost'; 
    $username ='root'; 
    $password ='root'; 
    $dbname='ctmdata';

try{
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
}
catch(Exception $e){
    die('Erreur : ' . $e->getMessage());
}
?>

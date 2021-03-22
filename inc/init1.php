<?php

// Definition du fuseau horaire
date_default_timezone_set('Europe/Paris');

// Ouverture de session
session_start();

//connexion à la BDD
try{
$pdo = new PDO(
    'mysql:host=localhost;charset=utf8;dbname=room',//dsn
    'root',//login
    '',// mdp
    array(
        PDO::ATTR_ERRMODE =>PDO::ERRMODE_WARNING,
        PDO:: ATTR_DEFAULT_FETCH_MODE => PDO:: FETCH_ASSOC
    )
);
}
catch(PDOException $e){
    echo $e->getMessage() . '<br>Fichier: ' . $e->getFile() . '<br> Ligne: ' . $e->getLine(). '<br>' ;
    die('site indisponible');
}


// Constante de site
define('URL',"/room/");

// inclusion du fichier de functions
require_once('functions.php');
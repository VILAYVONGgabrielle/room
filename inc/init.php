<?php

// Definition du fuseau horaire
date_default_timezone_set('Europe/Paris');

// Ouverture de session
session_start();

//connexion à la BDD
try {
    if (preg_match('#^localhost$#', $_SERVER['HTTP_HOST'])) {
        $pdo = new PDO(
            'mysql:host=localhost;charset=utf8;dbname=room', //dsn
            'root', //login
            '', // mdp
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            )
        );

        // Constante de site
        define('URL', '/room/');

    } else {
        $pdo = new PDO(
            'mysql:host=cl1-sql11;charset=utf8;dbname=mpj39351', //dsn
            'mpj39351', //login
            '8Oravanh9', // mdp
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            )
        );

        // Connexion site
        define('URL', 'http://lotusdigitek.fr/room/');
    }
} catch (PDOException $e) {
    echo $e->getMessage() . '<br>Fichier: ' . $e->getFile() . '<br> Ligne: ' . $e->getLine() . '<br>';
    die('site indisponible');
}


// inclusion du fichier de functions
require_once('functions.php');

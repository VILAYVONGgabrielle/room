<?php

function isConnected()
{

    // existence d'1 indes 'membre' dans le tableau $_SESSION indiquera que la page de connexion s'est bien passé
    return isset($_SESSION['membre']);
}

// il faut avoir au moins le droit 1 pour accéder aux fonctions admin
function isAdmin()
{

    // existence d'1 indes 'membre' dans le tableau $_SESSION indiquera que la page de connexion s'est bien passé
    return (isConnected() && $_SESSION['membre']['statut'] == 1);
}

//******************************************************* */
// fonction avec 2 parametres : $requete et la 2è optionnelle : $params=array(), la 1 ere correspond à "SELECT*FROM table", la 2è "SELECT*FROM table WHERE...." ==> le array() correspond aux valeurs (aller voir function getMembreByPseudo($pseudo){})

function execRequete($requete, $params = array())
{
    global $pdo; // je rend accessible la variable de l'espace de global de php, recupere $pdo qui se trouve dans le fichier init.php
    $r = $pdo->prepare($requete);

    // dans le cas où $param existe, exécute la code qui suit
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $params[$key] = htmlspecialchars($value, ENT_QUOTES);
            $r->bindValue($key, $params[$key], PDO::PARAM_STR); // rajout de cette ligne supplémentaire
        }
    }

    $r->execute(); // ce ne sera plus $params ==>$r->execute($params);

    //var_dump($r->errorInfo());
    if (!empty($r->errorInfo()[2])) {
        die("Erreur rencontrée - merci de contacter l'admin");
    }
    //**** FIN du code au cas où il y a $params ** 

    return $r;
}
// Controler l'existance d'1 pseudo, le cas echeant retouner toutes les infos de ce membre

function getMembreByPseudo($pseudo)
{
    $resultat = execRequete(" SELECT*FROM membres WHERE pseudo = :pseudo", array('pseudo' => $pseudo));
    if ($resultat->rowCount() > 0) {
        return $resultat; // affiche tous les infos contenues dans $resultat
    } else {
        return false; // sinon c'est vide cad aucun pseudo pris donc dispo
    }
}

// fonction gestion des étoiles/notes
function computeStars($nbr)
{
    $totalStarsEmpty = 5 - $nbr;
    $html = '';
    $stars = '<i class="fa fa-star mr-1"></i>';
    $starsEmpty = '<i class="fa fa-star-o mr-1"></i>';

    for ($i = 0; $i < $nbr; $i += 1) {
        $html .= $stars;
    }

    for ($j = 0; $j < $totalStarsEmpty; $j += 1) {
        $html .= $starsEmpty;
    }

    return $html;
}

// fonctions liées au panier

function createshopping()
{
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = array(
            'id_produit' => array(), //liste d'id pdt
            'id_salle' => array(), // liste des qtes
            'date_arrivee' => array(), // liste des qtes
            'date_depart' => array(), // liste des qtes
            'prix' => array()
        );
    }
}

function ajoutPanier($id_produit, $salle, $datearrivee, $datedepart, $prix)
{
    createshopping();
    $position_produit = array_search($id_produit, $_SESSION['panier']['id_produit']); // array search envoye 1 index ***array_search — Recherche dans un tableau la clé associée à la première valeur ***Retourne la clé pour needle si elle est trouvée dans le tableau, sinon pas trouvé affichera 'false' .
    if ($position_produit === false) {
        // nouveau pdt dans le panier
        $_SESSION['panier']['id_produit'][] = $id_produit;
        $_SESSION['panier']['id_salle'][] = $salle;
        $_SESSION['panier']['date_arrivee'][] = $datearrivee;
        $_SESSION['panier']['date_depart'][] = $datedepart;
        $_SESSION['panier']['prix'][] = $prix;
    }
}

function suppPanier($id_produit)
{
    $position_produit = array_search($id_produit, $_SESSION['panier']['id_produit']); // si id_pdt = 2 va chercher dans $_session ['panier']['id_produit'] la valeur correspondant à 2 
    // !==false ==> trouvé une correspondance
    //var_dump($position_produit);
    if ($position_produit !== false) {
        if ($_SESSION['panier']['id_produit'][$position_produit]) {
            // retrait complet de la ligne du panier **array_splice — Efface et remplace une portion de tableau
            array_splice($_SESSION['panier']['id_produit'], $position_produit, 1);
            array_splice($_SESSION['panier']['prix'], $position_produit, 1);
        }
    }
}

function countPanier()
{
    $total = 0;
    for ($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++) {
        $total += $_SESSION['panier']['prix'][$i];
    }
    return $total;
}

function nbProduits()
{
    $nb = '';
    if (!empty($_SESSION['panier']['id_produit'])) {
        $nb = '<span class="badge badge-primary">' . count($_SESSION['panier']['id_produit']) . '</span>';
    }
    return $nb;
}

function viderPanier()
{
    unset($_SESSION['panier']);
}

<?php
require_once('inc/init.php');

$title = 'Panier';

//var_dump($_SESSION);
//var_dump($_POST['id_produit']);

// valider
if (isset($_GET["action"]) && $_GET["action"] == 'valider') {

    for ($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++) {
        $resultat = execRequete(" SELECT*FROM produits WHERE id_produit=:id_produit", array('id_produit' => $_SESSION['panier']['id_produit'][$i]));

        $produit1 = $resultat->fetch();
        //var_dump($_SESSION['membre']);
        var_dump($produit1);
        //var_dump($_SESSION['panier']);
        
        // alimenter la table commande
        $id_membre = $_SESSION['membre']['id_membre'];// id client
        $montantTotal = countPanier();
        execRequete(" INSERT INTO commandes VALUES (NULL, :id_membre, :id_produit, NOW())",array('id_membre'=> $id_membre, 'id_produit' => $_SESSION['panier']['id_produit'][$i]));
           // $id_commande = $pdo->lastInsertId();

//mettre à jour le stock
execRequete("UPDATE produits SET etat = 'reservation' WHERE id_produit= :id_produit", array(
    'id_produit'=>$id_produit
));

        //vider le panier
            unset($_SESSION['panier']);
            header('location:'.URL. 'commandes.php');
            exit();

    }
}


// CREATION/ AJOUT
if (isset($_POST['ajout_panier'])) {
    //on verifie un index de post nous permettant d'identifier le formulaire de provenance
    $panierPdt = execRequete('SELECT * FROM produits WHERE id_produit = :id_produit', array('id_produit' => $_POST['id_produit']));
    if ($panierPdt->rowCount() == 1) {
        $produit = $panierPdt->fetch();
        //var_dump($produit);
        ajoutPanier($produit['id_produit'],  $produit['id_salle'], $produit['date_arrivee'], $produit['date_depart'], $produit['prix']);
        header('location:' . URL . 'ficheProduit.php?id_produit=' . $_POST['id_produit'] . '&sp=ok');
        exit();
    }
}

// supprimer
if (isset($_GET['action']) && $_GET['action'] == 'supligne' && !empty($_GET['id_produit'])) {
    $position_produit = array_search($_GET['id_produit'], $_SESSION['panier']['id_produit']);
    suppPanier($_GET['id_produit']);
    //var_dump($position_produit);
}

//viderPanier($produit);
//session_destroy();


require_once('inc/header.php');
if (empty($_SESSION['panier']['id_produit'])) {
?>
    <div class="alert alert-info gdSalles">Votre panier est vide</div>

<?php


} else {
?>
    <h2 class="gdSalles">Votre panier: </h2>
    <hr>

    <?php if (!empty($errors)) : // si $errors n'est pas vide cad qu'il y ades erreurs alors affiche l'alerte

    ?>
        <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <tr>
            <th>id produit</th>
            <th>Salle</th>
            <th>Date d'arrivée</th>
            <th>Date de départ</th>
            <th>Prix</th>
            <th>Action</th>
        </tr>
        <tr>
            <?php
            for ($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++) {
                $resultat = execRequete(" SELECT*FROM produits WHERE id_produit=:id_produit", array('id_produit' => $_SESSION['panier']['id_produit'][$i]));
                $produit = $resultat->fetch();
                $message = '';
            ?>
                <td><?php echo $produit['id_produit'] ?></td>
                <td><?php echo $produit['id_salle'] ?></td>
                <td><?php echo $produit['date_arrivee'] ?></td>
                <td><?php echo $produit['date_depart'] ?></td>
                <td><?php echo number_format($produit['prix'], 2, ',', '&nbsp;') ?> &euro;</td>

                <td><a href="?action=supligne&id_produit=<?php echo $produit['id_produit'] ?>"><i class="fas fa-trash"></i></a></td>
        </tr>
    <?php
            }
    ?>

<?php
}
?>
<tr>
    <th colspan="4" class="text-right">Montant Total</th>
    <th colspan="2"><?php  if(isset($_SESSION['panier'])){echo number_format(countPanier(), 2, ',', '&nbsp;');} else{echo '';}  ?>&euro;</th>
</tr>
    </table>
    <?php

    if (isConnected()) {
    ?>
        <div class="d-flex justify-content-end">
            <a href="?action=valider" class="btn btn-primary">commander</a>
        </div>
    <?php
    } else {
    ?>
        <p class="alert alert-info">
            Veuillez vous <a href="<?php echo URL . 'inscription.php' ?>">inscrire</a> ou vous <a href="<?php echo URL . 'connexion.php' ?>">connecter</a> afin de valider votre commande
        </p>
    <?php

    }

    require_once('inc/footer.php');

<?php

// inclusion de init
require_once('../inc/init.php');


// titre de la page
$title = 'Gestion des commandes';

// verif de l'admin
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}


// delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_commande']) && is_numeric($_GET['id_commande'])) {

    // supp du pdt en BDD
    execRequete(" DELETE FROM commandes WHERE id_commande=:id_commande", array('id_commande' => $_GET['id_commande']));

    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}



// inclusion du header
require_once('../inc/header.php');

$resultats = execRequete(" SELECT *, cde.date_enregistrement AS date_enregistrement
FROM commandes cde
INNER JOIN membres mb ON cde.id_membre= mb.id_membre
INNER JOIN produits pdt ON cde.id_produit = pdt.id_produit
INNER JOIN salles sal ON pdt.id_salle = sal.id_salle
");
?>
<h1 class="gdSalles">Gestion des commandes</h1>
<?php // var_dump($commande);
?>
<hr>
<?php
if ($resultats->rowCount() == 0) {
?>
    <div class="alert alert-info mt-3">pas de commande</div>
<?php
} else {
?>
    <div class="row">
        <div class='col-md-12'>
            <table class="table table-bordered table-striped  mt-5 table-hover" id="tabcommandes">
                <tr>
                    <th>N°</th>
                    <th>Membres</th>
                    <th>Produits</th>
                    <th>Prix</th>
                    <th>Date_enregistrement</th>
                    <th>actions</th>
                </tr>
                <?php
                // Les données

                while ($commande = $resultats->fetch()) {


                    $datecmd = new DateTime($commande['date_enregistrement']);
                    //var_dump($commande);
                ?>
                    <tr>
                        <td><?php echo $commande['id_commande'] ?></td>
                        <td><?php echo $commande['id_membre'] .'-'.$commande['email'] ?></td>
                        <td><?php echo $commande['titre'] ?></td>

                        <td><?php echo number_format($commande['prix'], 2, ',', '&nbsp;') ?>&euro;</td>
                        <td><?php echo date('d/m/Y à H:i:s', strtotime($commande['date_enregistrement'])) ?></td>
                        <td>
                            <!-- class confirm: voir function.js-->
                            <a href="?action=delete&id_commande=<?php echo $commande['id_commande'] ?>" class="confirm"><i class="far fa-trash-alt"></i></a>
                        </td>
                    </tr>

                <?php
                }

                ?>
            </table>

        <?php
    }


    require_once('../inc/footer.php');

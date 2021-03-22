<?php
require_once('inc/init.php');

$title = 'Mes commandes';

if(!isConnected()){
    header('location:'. URL. 'connexion.php');
    exit();
}

// commandes de l'utilisateur connecté ** 'c' est un alias
$id_membre = $_SESSION['membre']['id_membre'];
 
$commandes = execRequete(" SELECT *
                    FROM commandes cde
                    INNER JOIN produits pdt ON pdt.id_produit = cde.id_produit
                    INNER JOIN salles sal ON sal.id_salle = pdt.id_salle
                    WHERE cde.id_membre = :id_membre
                    ORDER BY cde.date_enregistrement DESC
                    ", array(
                        'id_membre' =>$id_membre
));


require_once('inc/header.php');
//var_dump($_SESSION['membre']);
if($commandes->rowCount()>0){
    // afficher les cdes
?>
<h1 class="gdSalles">Mes commandes</h1>
<hr>
<table class="table table-border table-striped">
    <?php
        $lastcmd = 0;
        while ($commande = $commandes->fetch()): 
            
            // ligne d'entete d'1 cde (on la repete 1 fois)
            if($commande['id_commande']!=$lastcmd){
                ?>
                <tr class="thead-dark">
                    <th>Commande n°</th>
                    <th>Date commande </th>
                    <th>Commande produit°<?php echo $commande['id_produit']?></th>
                    <th>Lieux</th>
                    <th>Date d'arrivée</th>
                    <th>Date de depart</th>
                    <th>Montant total</th>
                </tr>
                <?php
           }
            // détails
                    ?>
                    <tr>
                        <td><?php echo $commande['id_commande']?></td>
                        <td><?php echo date('d/m/Y à H:i:s',strtotime($commande['date_enregistrement']))?></td>
                        <td>Salle: <?php echo $commande['titre']?></td>
                        <td><?php echo $commande['ville']?></td>
                        <td><?php echo date('d/m/Y',strtotime($commande['date_arrivee']))?></td>
                        <td><?php echo date('d/m/Y',strtotime($commande['date_depart']))?></td>
                        <td> <?php echo number_format($commande['prix'],2,',','&nbsp;')?>&euro;</td>
                    </tr>

                    <?php

            $lastcmd = $commande['id_commande'];
        endwhile;
    ?>
</table>
<?php

}
else{
?>
    <div class="alert alert-info">Vous n'avez pas encore passé de commande</div>
<?php 
}


require_once('inc/footer.php');
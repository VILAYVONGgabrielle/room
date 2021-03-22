<?php

require_once('../inc/init.php');


$title = 'Statistiques';
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}
/*** */


// top 5 des salles les mieux notés
$noteSalle = execRequete("SELECT ROUND(AVG(note),2) as note_salle,titre
FROM avis av
INNER JOIN salles sal ON av.id_salle = sal.id_salle
GROUP BY titre
ORDER BY note_salle DESC
LIMIT 5
");

//var_dump($noteSalle->fetchAll());

// top 5 des salles les + cdées
$cdeSallePlus = execRequete(" SELECT titre,  COUNT(id_commande) AS best_salle
FROM salles sal 
INNER JOIN produits pdt ON sal.id_salle = pdt.id_salle
INNER JOIN commandes cde ON pdt.id_produit = cde.id_produit
GROUP BY titre
ORDER BY best_salle DESC
LIMIT 5
");


// top 5 des membres qui achetent le +
$achatMembre = execRequete("SELECT COUNT(id_commande) as achat_membre,nom,civilite,statut
FROM membres mbr
INNER JOIN commandes cde ON mbr.id_membre = cde.id_membre
GROUP BY nom
ORDER BY achat_membre DESC
LIMIT 5
");


// top 5 des membres qui achetent les + cher
$cherMembres = execRequete(" SELECT nom,civilite,  SUM(prix) AS prix_membre
FROM membres mbr 
INNER JOIN commandes cde ON mbr.id_membre = cde.id_membre
INNER JOIN produits pdt ON pdt.id_produit = cde.id_produit
GROUP BY nom
ORDER BY prix_membre DESC
LIMIT 5
");


// inclusion du header
require_once('../inc/header.php');
?>
<h1 class="gdSalles">Statistiques</h1>
<hr>

<div>
    <?php
    $count = $noteSalle->rowCount();
    ?>
    <p id=>Top <?php echo $count ?>
        des salles les mieux notés</p>
    <?php
    $i = 1;
    while ($bestNote = $noteSalle->fetch()) {
        echo $i++ . ' - ' . " salle " . $bestNote['titre'] . ' - note ' . $bestNote['note_salle'] . "<br>";
    }
    ?>
    <hr>
</div>
<div>
<?php

    $count = $cdeSallePlus->rowCount();
    //var_dump($count);
    ?>
    <p id=>Top<?php echo $count ?> des salles les plus commandées</p>
    <?php
    
    $i = 1;
    while ($bestSalle = $cdeSallePlus->fetch()) {
        //var_dump($bestSalle);
        echo $i++ . ' - ' . " salle " . $bestSalle['titre'] . ' - la plus commandée ' . $bestSalle['best_salle'] . "<br>";
    }
    ?>
    <hr>
</div>
<div>
<?php

    $count = $achatMembre->rowCount();
   // var_dump($count);
    ?>
    <p id=>Top<?php echo $count ?> des membres qui ont acheté le plus</p>
    <?php
    
    $i = 1;
    while ($bestMembre = $achatMembre->fetch()) {
        //var_dump($bestMembre);
        echo $i++ . ' - ' . " nom " . $bestMembre['nom'] . ' - civilité ' . $bestMembre['civilite'] . ' - statut ' . $bestMembre['statut'] . ' - nbr d\'achat ' . $bestMembre['achat_membre'] . "<br>";
    }
    ?>
    <hr>
</div>
<div>
<?php

    $count = $cherMembres->rowCount();
   // var_dump($count);
    ?>
    <p id=>Top<?php echo $count ?> des membres qui ont acheté le plus cher</p>
    <?php
    
    $i = 1;
    while ($cherMembre = $cherMembres->fetch()) {
        //var_dump($cherMembre);
        echo $i++ . ' - ' . " nom " . $cherMembre['nom'] . ' - civilité ' . $cherMembre['civilite'] . ' - prix ' . $cherMembre['prix_membre'] . "<br>";
    }
    ?>
    <hr>
</div>


<?php
// inclusion du footer
require_once('../inc/footer.php');

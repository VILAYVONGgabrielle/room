<?php
require_once('inc/init.php');

$title = 'Fiche produit';
/**  SELECT *, sal.id_salle AS id_salle FROM... ==> force selection id_salle de la table salle et non la derniere colonne id_salle */
if (!empty($_GET['id_produit'])) {
    $fichePdt = execRequete(" SELECT *, sal.id_salle AS id_salle 
    FROM salles sal
    INNER JOIN produits pdt ON sal.id_salle = pdt.id_salle 
    LEFT JOIN avis av ON sal.id_salle = av.id_salle
    WHERE id_produit=:id_produit", array('id_produit' => $_GET['id_produit']));

    if ($fichePdt->rowCount() == 0) {
        $error[] = 'produit inexistant';
    } else {
        $infos = $fichePdt->fetch();
        $title .= ': ' . $infos['titre']; // affiche dans l url le titre
    }
} else {
    header('location:' . URL);
    exit();
}

//if(isset($infos['note'])) teste l'existance de $infos['note']  *** if($infos['note']) teste l'existance de la valeur
if (isset($infos['note'])) {

    $star = execRequete(" SELECT round(AVG(note),2) AS notemoyenne
    FROM avis av
    INNER JOIN salles sal ON sal.id_salle = av.id_salle 
    WHERE sal.id_salle=:id_salle", array('id_salle' => $infos['id_salle']));
    $avis = $star->fetch()['notemoyenne'];
}

/*$r = execRequete( "SELECT * FROM avis, salles WHERE avis.id_salle = salles.id_salle ");
$avis = $r->fetch();*/


require_once('inc/header.php');
?>
<h1 class="gdSalles">Fiche du produit</h1>
<hr>
<?php
//echo' INFO :'.var_dump($infos);
//echo' AVIS :'.var_dump($avis);

if (!empty($errors)) : ?>
    <div class="alert alert-danger mt-3"><?php echo implode('<br>', $errors) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col">
        <?php
        if (!empty($infos) && isset($infos)) : ?>
            <div class="row">
                <h1 class="page-header mt-5 px-5"><?php echo $infos['titre'] ?></h1>
                <h5 class="page-header mt-5 "><?php echo (isset($avis)) ? computeStars($avis) : ''; ?></h5>
            </div>
            <div class="container-fluid">
                <div class="form-row">
                <div class="col-md-7">
                    <img class="photoFichpdt" src="<?php echo URL . 'photos/' . $infos['photo'] ?>" alt="<?php echo $infos['titre'] ?>" class="img-fluid">
                </div>
                <div class="col-md-5 fichePdtDesc">
                    <!--Formulaire d'ajout au panier-->
                    <form action="panier.php" method="post">
                        <input type="hidden" name="id_produit" value="<?php echo $infos['id_produit'] ?>">
                        <div class="form-group col-md-4">
                            <button type="submit" name="ajout_panier" class="btn btn-primary">Reservation</button>
                        </div>
                    </form>
                    <h5>Description</h5>
                    <p><?php echo $infos['description'] ?></p>

                    <h5>Localisation</h5>
                    <iframe class="fichePdtIframe" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d83998.75786964477!2d2.2768487070123093!3d48.85895057667116!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e1f06e2b70f%3A0x40b82c3688c9460!2sParis!5e0!3m2!1sfr!2sfr!4v1614272221723!5m2!1sfr!2sfr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
                </div>
            </div>

            <div class="container-fluid">
                <h5 class="mt-2">Informations complementaires</h5>


                <div class="row mt-5">

                    <h6 class="col-md-4">
                        <p><?php echo '<strong>Date d\'arrivee:   </strong>' . $infos['date_arrivee'] ?></p>
                        <p><?php echo '<strong>Date depart:   </strong>'  . $infos['date_depart'] ?></p>

                    </h6>
                    <h6 class="col-md-4">
                        <p><?php echo '<strong>capacite:   </strong>' . $infos['capacite'] ?></p>
                        <p><?php echo '<strong>categorie:  </strong>' . $infos['categorie'] ?></p>
                    </h6>
                    <h6 class="col-md-4">
                        <p><?php echo '<strong>adresse: </strong>' . $infos['adresse'] ?></p>
                        <p><?php echo '<strong>tarif: </strong>' . number_format($infos['prix'], 2, ',', '&nbsp;') ?>&euro;</p>
                    </h6>
                </div>
            </div>
            <div class="form-group col-md-6">
                <a class="col-md-4" href="<?php echo URL . 'avis.php?id_salle=' . $infos['id_salle'] . '&id_produit=' . $infos['id_produit'] ?>"> <button type="button" class="btn btn-info">Laisser un avis</button></a>
            </div>

        <?php endif; ?>


    </div>
</div>

<?php

if (isset($_GET['sp']) && $_GET['sp'] == 'ok') {
?>
    <div class="modal fade" id="modalConfirm" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">le pdt a été ajouté au panier</h4>
                </div>
                <div class="modal-body">
                    <a class="btn btn-primary" href="<?php echo URL . 'panier.php' ?>">voir le panier</a>
                    <a class="btn btn-primary" href="<?php echo URL ?>">continuer mes achats</a>
                </div>

            </div>

        </div>
    </div>

<?php

}



require_once('inc/footer.php');

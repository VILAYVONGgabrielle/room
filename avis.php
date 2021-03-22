<?php
require_once('inc/init.php');

$title = 'Avis';

// si je ne suis âs connecté, je suis invité à le faire pour accéder à mon compte
if (!isConnected()) {
    header('location:' . URL . 'connexion.php');
    exit();
}
// commandes de l'utilisateur connecté ** 'c' est un alias
$id_membre = $_SESSION['membre']['id_membre'];

/** */

// Traitement du formulaire
if (!empty($_POST) && isset($_POST)) {

    $errors = array();

    // Controles avant l'insertion en BDD
    $nb_champs_vides =  0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(trim($value));
        if ($_POST[$key] == '') $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errors[] = 'il manque ' . $nb_champs_vides . ' information(s)';
    }

    // controle le commentaire
    if (
        iconv_strlen(trim($_POST['commentaire'])) < 2 ||
        iconv_strlen(trim($_POST['commentaire'])) > 500
    ) {
        $errors[] = 'champs commentaire invalide';
    }

    /**   SI AUCUNE ERREEUR ALORS ON PROCEDE 0 L EXECUTION DE LA REQUETE **/
    if (empty($errors)) {
        $_POST['id_membre'] = $id_membre;
        $_POST['id_salle'] = $_GET['id_salle'];

        var_dump($_POST);
        // Insertion en BDD
        execRequete("INSERT INTO avis VALUES (NULL,:id_membre,:id_salle,:commentaire,:note,NOW())", $_POST);


        header('location:' . $_SERVER['REQUEST_URI']);
        // header("location:" . URL . 'backoffice/gestionProduits.php');
        exit();
    }
}


if (!isset($_GET['id_salle'])) header('location:' . URL);


require_once('inc/header.php');
?>
<h1 class="gdSalles">Avis</h1>
<hr>
<?php
//var_dump($_GET);
//var_dump($_POST);
//var_dump($id_membre);
?>
<ul class="nav nav-tabs nav-justified">
    <li class="nav-item"><a class="nav-link <?php echo (!isset($_GET['action'])
                                                || (isset($_GET['action']) && $_GET['action'] == 'affichage')) ? 'active' : '' ?>
                                                " href="?action=affichage<?php echo (isset($_GET['id_salle'])) ? '&id_salle=' . $_GET['id_salle'] . '&id_produit=' . $_GET['id_produit'] : '' ?>">Affichage des avis</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (isset($_GET['action'])
                                                && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) ? 'active' : '' ?>" href="?action=ajout<?php echo (isset($_GET['id_salle'])) ? '&id_salle=' . $_GET['id_salle'] . '&id_produit=' . $_GET['id_produit'] : '' ?>">Laisser un avis</a></li>
</ul>
<?php
if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) {

    // Affichage des avis
    $resultats = execRequete("SELECT * FROM avis av
    INNER JOIN membres mbr ON av.id_membre = mbr.id_membre
    INNER JOIN salles sal ON av.id_salle = sal.id_salle
    WHERE av.id_salle=:id_salle", array('id_salle' => $_GET['id_salle']));
    if ($resultats->rowCount() == 0) {
?>
        <div class="alert alert-info mt-5">Il n'y a pas encore d'avis enregistré</div>
    <?php
    } else {
    ?>
        <p>Il y a <?php echo $resultats->rowCount() ?> avis</p>
        <table class="table table-bordered table-striped table-responsive-lg">
            
            <tr>
                <th>N°</th>
                <th>Membres</th>
                <th>Salles</th>
                <th>Commentaire</th>
                <th>Note</th>
                <th>date d'enregistrement</th>

            
            </tr>
            <?php
            // Les données
            while ($ligne = $resultats->fetch()) {
                // var_dump($ligne);
            ?>
                <tr>

                    <td><?php echo $ligne['id_avis'] ?></td>
                    <td><?php echo $ligne['id_membre'] . ' - ' . $ligne['email'] ?></td>
                    <td><?php echo $ligne['id_salle'] . ' - ' . $ligne['titre'] ?></td>
                    <td><?php echo $ligne['commentaire'] ?></td>

                    <td><?php
                        $stars = array(1 => '*', 2 => '**', 3 => '***', 4 => '****', 5 => '*****');
                        foreach ($stars as $star => $value) {
                            if ($ligne['note'] == $star) {
                                echo $value;
                            }
                        }
                        ?>
                    </td>

                    <td><?php echo $ligne['date_enregistrement'] ?></td>
                </tr>
            <?php
            }
            ?>

        </table>
    <?php
    }
    ?>
    <div class="form-group col-md-4">
        <a class="col-md-4" href="<?php if (isset($_GET['id_produit'])) {
                                        echo URL . 'ficheProduit.php?id_produit=' . $_GET['id_produit'];
                                    }  ?>"> <button type="button" class="btn btn-info">Retour Fiche produit</button></a>
    </div>
<?php
}
?>
<?php

if (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) {

    // Cas d'un formulaire d'edition d'un avis existant ** is_numeric — Détermine si une variable est un nombre ou une chaîne numérique
    if ($_GET['action'] == 'edit' && !empty($_GET['id_avis']) && is_numeric($_GET['id_avis'])) {
        $resultat = execRequete("SELECT * FROM avis WHERE id_avis=:id_avis", array(
            'id_avis' => $_GET['id_avis']
        ));
        $avis_courant = $resultat->fetch();
        //var_dump($avis_courant);
    }

    // Formulaire d'edition de salle
?>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post" class="py-5">
        <?php if (!empty($avis_courant['id_avis'])) : ?>
            <input type="hidden" name="id_avis" value="<?php echo $avis_courant['id_salle'] ?>">
            <!--hidden-->
        <?php endif; ?>

        <?php if (!empty($avis_courant['id_salle'])) : ?>
            <input type="hidden" name="id_salle" value="<?php echo $avis_courant['id_salle'] ?>">
        <?php endif; ?>

        <div class="form-row">

            <div class="form-group col-md-6">
                <p>Salle: <?php echo $_GET['id_salle']; ?></p>
            </div>
            <div class="form-group col-md-6">
                <label for="commentaire">Commentaire</label>
                <textarea class="form-control" id="commentaire" name="commentaire" rows="7"><?php echo $_POST['commentaire'] ?? $avis_courant['commentaire'] ?? '' ?></textarea>
            </div>
        </div>
        <!--pays à catégo-->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="note">Note</label>
                <select class="form-control" id="note" name="note">
                    <?php
                    $star = array(1 => '*', 2 => '**', 3 => '***', 4 => '****', 5 => '*****');
                    foreach ($star as $key => $note) {
                    ?>
                        <option value="<?php echo $key ?>" <?php echo (
                                                                (isset($_POST['note']) && $_POST['note'] == $note)
                                                                ||
                                                                (isset($avis_courant['note']) && $avis_courant['note'] == $note)) ? 'selected' : '' ?>>
                            <?php echo $note ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
<?php

}

require_once('inc/footer.php');

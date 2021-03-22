<?php

// inclusion de init
require_once('../inc/init.php');


$title = 'AVIS';
// verif de l'admin
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// commandes de l'utilisateur connecté ** 'c' est un alias
$id_membre = $_SESSION['membre']['id_membre'];

// Traitement du formulaire
if (!empty($_POST)) {

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
        $errors[] = 'Nom invalide';
    }

    //**   SI AUCUNE ERREEUR ALORS ON PROCEDE 0 L EXECUTION DE LA REQUETE 
    if (empty($errors)) {
        //$_POST['id_membre'] = $id_membre;
        var_dump($_POST);

        if (isset($_POST['id_avis'])) {
            // update  avec date enregistrement
            execRequete("UPDATE avis SET id_membre = :id_membre, id_salle = :id_salle, commentaire = :commentaire, note = :note WHERE id_avis = :id_avis", $_POST);
        }

        header('location:' . $_SERVER['PHP_SELF']);
        //header("location:" . URL . 'backoffice/gestionMembres.php');
        exit();
    }
}

// delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_avis']) && is_numeric($_GET['id_avis'])) {

    // supp du pdt en BDD
    execRequete(" DELETE FROM avis WHERE id_avis=:id_avis", array('id_avis' => $_GET['id_avis']));

    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

// inclusion du header
require_once('../inc/header.php');
?>


<h1 class="gdMembres">Gestion des avis</h1>
<hr>

<ul class="nav nav-tabs nav-justified">
    <li class="nav-item"><a class="nav-link <?php echo (!isset($_GET['action'])
                                                || (isset($_GET['action']) && $_GET['action'] == 'affichage')) ? 'active' : '' ?>
                                                " href="?action=affichage<?php echo (isset($_GET['id_salle'])) ? '&id_salle=' . $_GET['id_salle'] . '&id_produit=' . $_GET['id_produit'] : '' ?>">Affichage des avis</a></li>

</ul>
<?php

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) {

    // Affichage des avis
    $resultats = execRequete("SELECT * FROM avis av
    INNER JOIN membres mbr ON av.id_membre = mbr.id_membre
    INNER JOIN salles sal ON av.id_salle = sal.id_salle
    ");
    if ($resultats->rowCount() == 0) {
?>
        <div class="alert alert-info mt-5">Il n'y a pas encore de membres enregistrées</div>
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
                <th colspan="2">Actions</th>
            </tr>
            <?php
            // Les données
            while ($ligne = $resultats->fetch()) {
                //var_dump($ligne);
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
                    <td>
                        <a href="?action=edit&id_avis=<?php echo $ligne['id_avis'] ?>"><i class="far fa-edit"></i></a>
                    </td>
                    <td>
                        <!-- class confirm: voir function.js-->
                        <a href="?action=delete&id_avis=<?php echo $ligne['id_avis'] ?>" class="confirm"><i class="far fa-trash-alt"></i></a>
                    </td>
                </tr>
            <?php
            }
            ?>
        </table>
    <?php
    }
}

if (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) {

    // Cas d'un formulaire d'edition d'un avis existant ** is_numeric — Détermine si une variable est un nombre ou une chaîne numérique
    if ($_GET['action'] == 'edit' && !empty($_GET['id_avis']) && is_numeric($_GET['id_avis'])) {
        $resultat = execRequete("SELECT * 
        FROM avis av
        INNER JOIN salles sal ON av.id_salle = sal.id_salle
        INNER JOIN membres mbr ON av.id_membre = mbr.id_membre
        WHERE id_avis=:id_avis", array(
            'id_avis' => $_GET['id_avis']
        ));
        $avis_courant = $resultat->fetch();
        //var_dump($avis_courant);
    }


    // Formulaire d'edition de membres
    ?>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="py-5">
        <?php if (!empty($avis_courant['id_avis'])) : ?>
            <input type="hidden" name="id_avis" value="<?php echo $avis_courant['id_avis'] ?>"> <!-- 'hidden' permet aux développeurs web d'inclure des données qui ne peuvent pas être vues sur la page ou modifiées lorsque le formulaire est envoyé-->
        <?php endif; ?>

        <div class="form-row">

            <div class="form-group col-md-6">
                <p>Membre: <?php echo $avis_courant['nom'] ?></p>
                <?php if (!empty($avis_courant['id_membre'])) : ?>
                    <input type="hidden" name="id_membre" value="<?php echo $avis_courant['id_membre'] ?>">
                <?php endif; ?>
            </div>

            <div class="form-group col-md-6">
                <p>Salle: <?php echo $avis_courant['titre'] ?></p>
                <?php if (!empty($avis_courant['id_salle'])) : ?>
                    <input type="hidden" name="id_salle" value="<?php echo $avis_courant['id_salle'] ?>">
                <?php endif; ?>
            </div>
            <div class="form-group col-md-6">
                <label for="commentaire">Commentaire</label>
                <textarea class="form-control" id="commentaire" name="commentaire" rows="7"><?php echo $_POST['commentaire'] ?? $avis_courant['commentaire'] ?? '' ?></textarea>
            </div>
        </div>
        <!--note-->
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

require_once('../inc/footer.php');

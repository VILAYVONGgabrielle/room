<?php

require_once('../inc/init.php');


$title = 'Gestion des salles';
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}
/*** */


if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_salle']) && is_numeric($_GET['id_salle'])) {
    //recuperation du pdt en BDD pour obtenir le nom du fichier de la photo
    $salle_asup = execRequete(" SELECT photo FROM salles WHERE id_salle = :id_salle", array('id_salle' => $_GET['id_salle']));
    //var_dump('')
    if ($salle_asup->rowCount() == 1) {
        $infos = $salle_asup->fetch();
        $photo = $infos['photo'];
        // supp du fichier 
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $photo)) {
            //supp du fichier (unlink)
            unlink($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $photo);
        }

        // supp du pdt en BDD
        execRequete(" DELETE FROM salles WHERE id_salle=:id_salle", array('id_salle' => $_GET['id_salle']));

        header('location:' . $_SERVER['PHP_SELF']);
        exit();
    }
}


//echo '$server: '; var_dump($_SERVER['DOCUMENT_ROOT'] );
// Traitement du formulaire
if (!empty($_POST)) {
    //var_dump($_POST); // array avec cle/valeur => ref, categorie, titre, description....
    //var_dump($_FILES); // array ['photo'] contenant ['name','type','tmp_name', 'error','size'] ==> idem cle/valeur
    // controles
    $nb_champs_vides =  0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(trim($value));
        if ($_POST[$key] == '') $nb_champs_vides++;
    }

    // controle du code postal
    if (!preg_match('#^[0-9]{5}$#', $_POST['cp'])) 
    {
        $errors[] = 'Code postal invalide, entrer 5 chiffres ';
    }

    // controle l' Adresse'
    if (
        iconv_strlen(trim($_POST['adresse'])) < 2 ||
        iconv_strlen(trim($_POST['adresse'])) > 50
    ) {
        $errors[] = 'Adresse invalide, entrer entre 2 et 50 caractères';
    }

    // Si je souhaite rendre la photo obligatoire 
    if (empty($_FILES['photo']['name'])) {

        // si j'ai une photo actuelle (pdt en édition) je ne considere pas que c'est un champ vide
        if (empty($_POST['photo_actuelle'])) {
            $nb_champs_vides++;
        }
    } else {

        // Lorsque le fichier est renseigné , je controle son extension
        $mimeAutorises = array('image/jpeg', 'image/png', 'image/webp');

        if (!in_array($_FILES['photo']['type'], $mimeAutorises)) {
            $errors[] = 'Format incorrect  : ' . $_FILES['photo']['type'] . '<br>Fichiers JPEG, PNG et WEBP seulement';
        }
    }


    if ($nb_champs_vides > 0) {
        $errors[] = "Il manque $nb_champs_vides information(s)";
    }

    //si la variable $errors ne contient pas d'erreurs cad champ vide !=0 , signifie qu'il y a des données
    if (empty($errors)) {
        // si photo existe 
        if (!empty($_FILES['photo']['name'])) {

            // si j'avais deja une photo
            if (isset($_POST['photo_actuelle']) && file_exists($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $_POST['photo_actuelle'])) {
                //supp du fichier (unlink)
                unlink($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $_POST['photo_actuelle']);
            }

            // gérer la photo (copie physique du fichier)
            $nomPhotoBDD = $_POST['titre'] . '_' . $_FILES['photo']['name'];
            $dossierPhotos = $_SERVER['DOCUMENT_ROOT'] . URL . 'photos/';
            // $_SERVER['DOCUMENT_ROOT'] = 'C:/wamp64/www' *** URL = define('URL',"/boutique/"); *** dossier photos

            // On deplace le fichier temporaire vers le dossier photos sous un nom unique composé de la référence et du nom original du fichier
            move_uploaded_file($_FILES['photo']['tmp_name'], $dossierPhotos . $nomPhotoBDD);
            // deplacer 'move' le $_FILES photo depuis tmp_name ==> chemin et nom temporaire, vers le nouvel emplacement

        } else {
            $nomPhotoBDD = $_POST['photo_actuelle'];
        }
        unset($_POST['photo_actuelle']); // supprime la photo actuelle
        $_POST['photo'] = $nomPhotoBDD; // remplace par la nouvelle

 
        // cas UPDATE
        if (isset($_POST['id_salle'])) {
            // update
            execRequete("UPDATE salles SET titre = :titre, description = :description, photo = :photo, pays = :pays, ville = :ville, adresse = :adresse, cp = :cp, capacite= :capacite, categorie= :categorie WHERE id_salle = :id_salle", $_POST);
        } else {
            // Insertion en BDD
            execRequete("INSERT INTO salles VALUES (NULL,:titre,:description,:photo,:pays,:ville,:adresse,:cp,:capacite,:categorie)", $_POST);
            // On force le mode affichage des salles
            // $_GET['action'] = 'affichage';
            // header('location:'.URL.'admin/gestion_salles.php');
        }
        header('location:' . $_SERVER['PHP_SELF']);
        exit();
    }
}


require_once('../inc/header.php');
?>
<h1 class="gdSalles">Gestion des salles</h1>
<hr>

<ul class="nav nav-tabs nav-justified">
    <li class="nav-item"><a class="nav-link <?php echo (!isset($_GET['action'])
                                                || (isset($_GET['action']) && $_GET['action'] == 'affichage')) ? 'active' : '' ?>" href="?action=affichage">Affichage des salles</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (isset($_GET['action'])
                                                && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) ? 'active' : '' ?>" href="?action=ajout">Ajouter/Editer une salle</a></li>
</ul>

<?php

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) {

    // Affichage des salles
    $resultats = execRequete('SELECT * FROM salles');
    if ($resultats->rowCount() == 0) {
?>
        <div class="alert alert-info mt-5">Il n'y a pas encore de salles enregistrées</div>
    <?php
    } else {
    ?>
        <p>Il y a <?php echo $resultats->rowCount() ?> salle(s)</p>
        <table class="table table-bordered table-striped table-responsive-lg">
            <tr>
                <?php
                // les entêtes de colonne
                for ($i = 0; $i < $resultats->columnCount(); $i++) {
                    $colonne = $resultats->getColumnMeta($i);
                    // var_dump(get_class_methods($resultats));
                ?>
                    <th><?php echo ucfirst($colonne['name']) ?></th>
                <?php
                }
                ?>
                <th colspan="2">Actions</th>
            </tr>
            <?php
            // Les données
            while ($ligne = $resultats->fetch()) {
                //var_dump($ligne);
            ?>
                <tr>
                    <?php
                    foreach ($ligne as $key => $value) {

                        switch ($key) {
                            case 'photo':

                                if (!empty($value)) {
                                    $value = '<img class="img-fluid" src="' . URL . 'photos/' . $value . '" alt="' . $ligne['titre'] . '">';
                                }
                                break;
                            case 'description':
                                //$value =(iconv_strlen($value)>30) ? substr($value,0,30). '...' : $value;
                                $extrait = (iconv_strlen($value) > 30) ? substr($value, 0, 30) . '...' : $value; // ternaire
                                // pour eviter de couper un mot 
                                if ($extrait != $value) {
                                    $slastSpace = strrpos($extrait, ' '); // renvoie la position de la derniere occurence
                                    $value = substr($extrait, 0, $slastSpace) . '...';
                                }
                                break;
                        }

                    ?>
                        <td><?php echo $value; ?></td>
                    <?php
                    }
                    ?>
                    <td>
                        <a href="?action=edit&id_salle=<?php echo $ligne['id_salle'] ?>"><i class="far fa-edit"></i></a>
                    </td>
                    <td>
                        <!-- class confirm: voir function.js-->
                        <a href="?action=delete&id_salle=<?php echo $ligne['id_salle'] ?>" class="confirm"><i class="far fa-trash-alt"></i></a>
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

    // Cas d'un formulaire d'edition d'un salle existant ** is_numeric — Détermine si une variable est un nombre ou une chaîne numérique
    if ($_GET['action'] == 'edit' && !empty($_GET['id_salle']) && is_numeric($_GET['id_salle'])) {
        $resultat = execRequete("SELECT * FROM salles WHERE id_salle=:id_salle", array(
            'id_salle' => $_GET['id_salle']
        ));
        $salle_courant = $resultat->fetch();
        //var_dump($salle_courant);
    }

    // Formulaire d'edition de salle
    ?>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="py-5">
        <?php if (!empty($salle_courant['id_salle'])) : ?>
            <input type="hidden" name="id_salle" value="<?php echo $salle_courant['id_salle'] ?>"> <!-- 'hidden' permet aux développeurs web d'inclure des données qui ne peuvent pas être vues sur la page ou modifiées lorsque le formulaire est envoyé-->
        <?php endif; ?>

        <div class="form-row">

            <div class="form-group col-md-6">
                <label for="titre">Titre</label>
                <input type="text" class="form-control" id="titre" name="titre" value="<?php echo $_POST['titre'] ?? $salle_courant['titre'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="7"><?php echo $_POST['description'] ?? $salle_courant['description'] ?? '' ?></textarea>
            </div>
        </div>
        <!--photos-->
        <div class="form-row">
            <div class="col-md-4"></div>
            <div class="form-group col-md-4">
                <label for="photo"><i class="fas fa-camera-retro fa-3x"></i></label>

                <input type="file" class="form-control d-none" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
                <div id="preview">
                    <!-- preview voir function.js-->
                    <?php
                    if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($salle_courant['photo'])) {
                    ?>
                        <img src="<?php echo URL . 'photos/' . $salle_courant['photo'] ?>" alt="<?php echo $salle_courant['titre'] ?>" class="img-fluid vignette" id="placeholder">
                    <?php
                    } else {
                    ?>
                        <img src="<?php echo URL . 'img/placeholder.png' ?> " alt="placeholder" class="img-fluid vignette" id="placeholder">
                    <?php
                    }
                    ?>
                </div>
                <!--preview-->
            </div>
            <?php
            // on memorise le nom du fichier actuel pour un pdt en edition
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($salle_courant['photo'])) {
            ?>
                <input type="hidden" name="photo_actuelle" value="<?php echo $salle_courant['photo'] ?>">
            <?php
            }
            ?>
            <div class="col-md-4"></div>
        </div>
        <!--pays à catégo-->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="pays">Pays</label>
                <select class="form-control" id="pays" name="pays">
                    <?php
                    $country = array('France');
                    foreach ($country as $pays) {
                    ?>
                        <option <?php echo (
                                    (isset($_POST['pays']) && $_POST['pays'] == $pays)
                                    ||
                                    (isset($salle_courant['pays']) && $salle_courant['pays'] == $pays)) ? 'selected' : '' ?>>
                            <?php echo $pays ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="ville">Ville</label>
                <select class="form-control" id="ville" name="ville">
                    <?php
                    $city = array('Paris', 'Lyon', 'Lille', 'Marseille');
                    foreach ($city as $ville) {
                    ?>
                        <option <?php echo (
                                    (isset($_POST['ville']) && $_POST['ville'] == $ville)
                                    ||
                                    (isset($salle_courant['ville']) && $salle_courant['ville'] == $ville)) ? 'selected' : '' ?>>
                            <?php echo $ville ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="adresse">Adresse</label>
                <input type="text" class="form-control <?php echo (!empty($_POST) &&
                                                            (empty(trim($_POST['adresse'])) ||
                                                                iconv_strlen(trim($_POST['adresse'])) < 2 ||
                                                                iconv_strlen(trim($_POST['adresse'])) > 50)) ? 'is-invalid' : '' ?>" id="adresse" name="adresse" value="<?php echo $_POST['adresse'] ?? '' ?>">
                <div class="invalid-feedback">
                    merci d'entrer entre 2 et 50 caractères
                </div>
            </div>
            <div class="form-group col-6">
                <label for="cp">Code postal</label>
                <input type="text" class="form-control  <?php
                                                        echo (
                                                            (!empty($_POST))
                                                            && !preg_match('#^[0-9]{5}$#', $_POST['cp'])) ? 'is-invalid' : '' ?>" id="cp" name="cp" value="<?php echo $_POST['cp'] ??
                                                                                                                                                                $salle_courant['cp'] ?? '' ?>">
                <div class="invalid-feedback">
                    merci de renseigner le code postal francais valide (5 chiffres)
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="capacite">Capacité</label>
                <select class="form-control" id="capacite" name="capacite">
                    <?php
                    $capacity = array(2, 4, 6, 8, 10);
                    foreach ($capacity as $capacite) {
                    ?>
                        <option <?php echo (
                                    (isset($_POST['capacite']) && $_POST['capacite'] == $capacite)
                                    ||
                                    (isset($salle_courant['capacite']) && $salle_courant['capacite'] == $capacite)) ? 'selected' : '' ?>>
                            <?php echo $capacite ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="categorie">Catégorie</label>
                <select class="form-control" id="categorie" name="categorie">
                    <?php
                    $category = array('reunion', 'bureau', 'formation');
                    foreach ($category as $categorie) {
                    ?>
                        <option <?php echo (
                                    (isset($_POST['categorie']) && $_POST['categorie'] == $categorie)
                                    ||
                                    (isset($salle_courant['categorie']) && $salle_courant['categorie'] == $categorie)) ? 'selected' : '' ?>>
                            <?php echo $categorie ?>
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

<?php

require_once('../inc/init.php');
$title = 'Gestion des produits';

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

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

    // controle tarif
    if (
        !preg_match('#^[0-9]+[,.]?[0-9]{1,2}$#', $_POST['prix'])
    ) {
        $errors[] = 'prix invalide';
    }

    /**   SI AUCUNE ERREEUR ALORS ON PROCEDE 0 L EXECUTION DE LA REQUETE **/
    if (empty($errors)) {

        var_dump($_POST);
        $_POST['date_arrivee'] = date('Y/m/d', strtotime($_POST['date_arrivee']));
        $_POST['date_depart'] = date('Y/m/d', strtotime($_POST['date_depart']));
        $_POST['etat'] = 'libre';
        var_dump($_POST);

        if (isset($_POST['id_produit'])) {
            // update  
            execRequete("UPDATE produits SET id_salle = :id_salle, date_arrivee = :date_arrivee, date_depart = :date_depart, prix = :prix, etat = :etat WHERE id_produit = :id_produit", $_POST);
        } else {
            // Insertion en BDD
            execRequete("INSERT INTO produits VALUES (NULL,:id_salle,:date_arrivee,:date_depart,:prix,:etat)", $_POST);
        }

        header('location:' . $_SERVER['PHP_SELF']);
        // header("location:" . URL . 'backoffice/gestionProduits.php');
        exit();
    }
}

// delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {

    // supp du pdt en BDD
    execRequete(" DELETE FROM produits WHERE id_produit=:id_produit", array('id_produit' => $_GET['id_produit']));

    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

/************************ */

require_once('../inc/header.php');
?>
<h1 class="gdSalles">Gestion des produits</h1>
<hr>
<?php
//var_dump($_POST); 

/*$resultats = execRequete('SELECT * FROM salles');
var_dump($resultats);
while ($ligne = $resultats->fetch()) {
    var_dump($ligne);
    foreach ($ligne as $key => $value) {
       //echo $value;
    }
    echo $ligne['id_salle'];
}*/
?>


<ul class="nav nav-tabs nav-justified">
    <li class="nav-item"><a class="nav-link <?php echo (!isset($_GET['action'])
                                                || (isset($_GET['action']) && $_GET['action'] == 'affichage')) ? 'active' : '' ?>" href="?action=affichage">Affichage des produits</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (isset($_GET['action'])
                                                && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) ? 'active' : '' ?>" href="?action=ajout">Ajouter/Editer un produit</a></li>
</ul>

<?php

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) {

    // Affichage des produits
    $resultats = execRequete("SELECT * FROM produits pdt
    INNER JOIN salles sal ON pdt.id_salle = sal.id_salle
    ");
    $afficheSalles = $resultats->fetchall();
    //var_dump($afficheSalles);
    //exit;

    if ($resultats->rowCount() == 0) {
?>
        <div class="alert alert-info mt-5">Il n'y a pas encore de produits enregistrées</div>
    <?php
    } else {
    ?>
        <p>Il y a <?php echo $resultats->rowCount() ?> produit(s)</p>
        <table class="table table-bordered table-striped table-responsive-lg">
            <tr>
                <th>id produit</th>
                <th>Date d'arrivee</th>
                <th>Date de départ</th>
                <th>id salle</th>
                <th>Prix</th>
                <th>Etat</th>
                <th colspan="2">Actions</th>
            </tr>
            <?php
            // Les données
            ?>
            <tr>
                <?php
                foreach ($afficheSalles as $key) {

                    if (!empty($key['photo'])) {
                        $value = '<img class="photoGpdt" src="' . URL . 'photos/' . $key['photo'] . '" alt="' . $key['titre'] . '">';
                    }
                    //var_dump($value);
                ?>
            <tr>
                <td><?php echo $key['id_produit'] ?></td>
                <td><?php echo  date('d/m/Y', strtotime($key['date_arrivee'])) ?></td>
                <td><?php echo date('d/m/Y', strtotime($key['date_depart'])) ?></td>
                <td><?php echo $key['id_salle'] . '-' . $key['titre'] . '<br>' . $value ?></td>
                <td><?php echo number_format($key['prix'], 2, ',', '&nbsp;') ?>&euro;</td>
                <td><?php echo $key['etat'] ?></td>
                <td>
                    <!-- editer un pdt-->
                    <a href="?action=edit&id_produit=<?php echo $key['id_produit'] ?>"><i class="far fa-edit"></i></a>
                </td>
                <td>
                    <!-- supprimer un pdt  ***  class confirm: voir function.js-->
                    <a href="?action=delete&id_produit=<?php echo $key['id_produit'] ?>" class="confirm"><i class="far fa-trash-alt"></i></a>
                </td>
            </tr>
        <?php
                }
        ?>

        </tr>


        </table>
    <?php
    }
}
// page Ajout/editer un produit
if (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) {

    // Cas d'un formulaire d'edition d'un salle existant ** is_numeric — Détermine si une variable est un nombre ou une chaîne numérique
    if ($_GET['action'] == 'edit' && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
        $resultat = execRequete("SELECT * FROM produits WHERE id_produit=:id_produit", array(
            'id_produit' => $_GET['id_produit']
        ));
        $produit_courant = $resultat->fetch();
        //var_dump($produit_courant);
    }

    // Formulaire d'ajout/edition de produit
    ?>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="py-5">

        <?php if (!empty($produit_courant['id_produit'])) : ?>
            <input type="hidden" name="id_produit" value="<?php echo $produit_courant['id_produit'] ?>"> <!-- 'hidden' permet aux développeurs web d'inclure des données qui ne peuvent pas être vues sur la page ou modifiées lorsque le formulaire est envoyé-->
        <?php endif; ?>

        <div class="form-row">

            <div class="form-group col-md-6">

                <?php
                if (!isset($_GET['action']) || (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit'))) {
                ?>
                    <label for="id_salle">Salle</label>
                    <select class="form-control" id="id_salle" name="id_salle">
                        <?php
                        $resultats = execRequete('SELECT * FROM salles');
                        //var_dump($resultats);
                        while ($ligne = $resultats->fetch()) {
                            /*var_dump($ligne);*/
                        ?>
                            <option value="<?php echo $ligne['id_salle'] ?>" <?php echo (
                                                                                    (isset($_POST['id_salle']) && $_POST['id_salle'] == $ligne)
                                                                                    ||
                                                                                    (isset($produit_courant['id_salle']) && $produit_courant['id_salle'] == $ligne)) ? 'selected' : '' ?>>
                                <?php echo $ligne['id_salle'] . ' - ' . $ligne['titre'] . ' - ' . $ligne['adresse'] . ' - ' . $ligne['cp'] . ' - ' . $ligne['ville'] . ' - ' . $ligne['capacite'] . ' pers. ' . ' - ' . $ligne['categorie'] ?>
                            </option>
                    <?php
                        }
                    }
                    ?>
                    </select>
            </div>
            <div class="form-group col-md-6">
                <label for="prix">Tarif</label>
                <input type="text" class="form-control" id="prix" name="prix" value="<?php echo $_POST['prix'] ?? $produit_courant['prix'] ?? '' ?>">
            </div>
        </div>
        </div>
        <!--date arrivée et sortie-->
        <div class="form-row resaFromTo">
            <div class="form-group col-md-6">
                <label for="date_arrivee">Date d'arrivée</label>
                <input type="text" class="form-control" id="date_arrivee" name="date_arrivee" placeholder="jj/mm/aa" required value="<?php echo $_POST['date_arrivee'] ?? $produit_courant['date_arrivee'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="date_depart">Date de départ</label>
                <input type="text" class="form-control" id="date_depart" name="date_depart" placeholder="jj/mm/aa" required value="<?php echo $_POST['date_depart'] ?? $produit_courant['date_depart'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="etat">Etat</label>
                <select class="form-control" id="etat" name="etat">
                    <?php
                    $status = array('libre', 'reservation');
                    foreach ($status as $etat) {
                    ?>
                        <option <?php echo (
                                    (isset($_POST['etat']) && $_POST['etat'] == $etat)
                                    ||
                                    (isset($produit_courant['etat']) && $produit_courant['etat'] == $etat)) ? 'selected' : '' ?>>
                            <?php echo $etat ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
    <!-- feuille jquery -->
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="<?php echo URL ?>libraries/jquery-ui-1.12.1/jquery-ui.min.css" />
    <div class="col-md-3">
    <?php

}

require_once('../inc/footer.php');

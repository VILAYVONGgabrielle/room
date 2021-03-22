<?php

// inclusion de init
require_once('../inc/init.php');


$title = 'Membres';
// verif de l'admin
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}



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

    // controle le pseudo
    if (
        iconv_strlen(trim($_POST['pseudo'])) < 2 ||
        iconv_strlen(trim($_POST['pseudo'])) > 20
    ) {
        $errors[] = 'Pseudo invalide';
    }

    // controle du mot de passe
    if (
        !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@])[\w$!\-\@]{8,20}$#', $_POST['mdp'])
    ) {
        $errors[] = 'Complexite du mot de passe non respectée';
    }

    // controle de l'émail
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'format de mail invalide';
    }

    // controle le nom
    if (
        iconv_strlen(trim($_POST['nom'])) < 2 ||
        iconv_strlen(trim($_POST['nom'])) > 20
    ) {
        $errors[] = 'Nom invalide';
    }

    // controle le prenom
    if (
        iconv_strlen(trim($_POST['prenom'])) < 2 ||
        iconv_strlen(trim($_POST['prenom'])) > 20
    ) {
        $errors[] = 'Prenom invalide';
    }
    /**   SI AUCUNE ERREEUR ALORS ON PROCEDE 0 L EXECUTION DE LA REQUETE **/
    if (empty($errors)) {
        // aucune erreurs, je peux procéder à l'inscription
        $_POST['mdp'] = password_hash($_POST['mdp'], PASSWORD_DEFAULT);


        //var_dump($_POST);
        if (isset($_POST['id_membre'])) {
            // update  avec date enregistrement version 3 
            execRequete("UPDATE membres SET pseudo = :pseudo, mdp = :mdp, nom = :nom, prenom = :prenom, email = :email, civilite = :civilite, statut = :statut WHERE id_membre = :id_membre", $_POST);
        } else {
            // Insertion en BDD
            execRequete("INSERT INTO membres VALUES (NULL,:pseudo,:mdp,:nom,:prenom,:email,:civilite,:statut,NOW())", $_POST);

        }

        //header('location:' . $_SERVER['PHP_SELF']);
        header("location:" . URL . 'backoffice/gestionMembres.php');
        exit();
    }
}
// aller chercher les membres en BDD
$_POST['id_membre'] = $_SESSION['membre']['id_membre'];

$membres = execRequete(" SELECT * FROM membres ORDER BY nom, prenom");
//var_dump($membres);

// delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_membre']) && is_numeric($_GET['id_membre'])) {

        // supp du pdt en BDD
        execRequete(" DELETE FROM membres WHERE id_membre=:id_membre", array('id_membre' => $_GET['id_membre']));

        header('location:' . $_SERVER['PHP_SELF']);
        exit();   
}




// inclusion du header
require_once('../inc/header.php');
?>


<h1 class="gdMembres">Gestion des Membres</h1>
<hr>

<ul class="nav nav-tabs nav-justified">
    <li class="nav-item"><a class="nav-link <?php echo (!isset($_GET['action'])
                                                || (isset($_GET['action']) && $_GET['action'] == 'affichage')) ? 'active' : '' ?>" href="?action=affichage">Affichage des membres</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (isset($_GET['action'])
                                                && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) ? 'active' : '' ?>" href="?action=ajout">Ajouter/Editer un membre</a></li>
</ul>
<?php

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) {

    // Affichage des membres
    $resultats = execRequete('SELECT * FROM membres');
    if ($resultats->rowCount() == 0) {
?>
        <div class="alert alert-info mt-5">Il n'y a pas encore de membres enregistrées</div>
    <?php
    } else {
    ?>
        <p>Il y a <?php echo $resultats->rowCount() ?> membre(s)</p>
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
                <th colspan="3">Actions</th>
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
                           case 'civilite':

                                $civilite = array(
                                    'm' => 'Mr',
                                    'f' => 'Mme',
                                );
                                $value = $civilite[$value];
                                break;

                            case 'statut':

                                $statut = array(
                                    0 => 'Membre',
                                    1 => 'Admin',
                                );
                                $value = $statut[$value];
                                break;

                                case 'mdp':
                                    
                                    $value = 'confidentiel';
                                    break;
                        }
                        
                    ?>
                        <td><?php echo $value; ?></td>
                    <?php
                    }
                    ?>
                    <td>
                        <a href="?action=edit&id_membre=<?php echo $ligne['id_membre'] ?>"><i class="far fa-edit"></i></a>
                    </td>
                    <td>
                        <!-- class confirm: voir function.js-->
                        <a href="?action=delete&id_membre=<?php echo $ligne['id_membre'] ?>" class="confirm"><i class="far fa-trash-alt"></i></a>
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

    // Cas d'un formulaire d'edition d'un membre existant ** is_numeric — Détermine si une variable est un nombre ou une chaîne numérique
    if ($_GET['action'] == 'edit' && !empty($_GET['id_membre']) && is_numeric($_GET['id_membre'])) {
        $resultat = execRequete("SELECT * FROM membres WHERE id_membre=:id_membre", array(
            'id_membre' => $_GET['id_membre']
        ));
        $membre_courant = $resultat->fetch();
        //var_dump($membre_courant);
    }

    // Formulaire d'edition de membres
    ?>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="py-5">
        <?php if (!empty($membre_courant['id_membre'])) : ?>
            <input type="hidden" name="id_membre" value="<?php echo $membre_courant['id_membre'] ?>"> <!-- 'hidden' permet aux développeurs web d'inclure des données qui ne peuvent pas être vues sur la page ou modifiées lorsque le formulaire est envoyé-->
        <?php endif; ?>

        <div class="form-row">

            <div class="form-group col-md-6">
                <label for="pseudo">Pseudo</label>
                <input type="text" class="form-control" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo'] ?? $membre_courant['pseudo'] ?? '' ?>">
            </div>
      
            <div class="form-group col-md-6">
                <label for="mdp">Mot de passe</label>
                <input type="text" class="form-control" id="mdp" name="mdp" value="<?php /*echo $_POST['mdp'] ?? $membre_courant['mdp'] ?? '' */?>">
            </div>
     
        </div>
        </div>
        <!--pays à catégo-->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="nom">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $_POST['nom'] ?? $membre_courant['nom'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="prenom">Prenom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $_POST['prenom'] ?? $membre_courant['prenom'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="email">Email</label>
                <input type="text" class="form-control" id="email" name="email" value="<?php echo $_POST['email'] ?? $membre_courant['email'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="civilite">Civilite</label>
                <select class="form-control" id="civilite" name="civilite">
                    <?php
                    $civility = array(
                        'm'=> 'Mr',
                        'f'=>'Mme',
                    );
                    foreach ($civility as $civilite=>$value) {
                    ?>
                        <option value="<?php if($value == 'Mr'){echo 'm';}else{echo'f';}?>" <?php echo (
                                    (isset($_POST['civilite']) && $_POST['civilite'] == $civilite)
                                    ||
                                    (isset($membre_courant['civilite']) && $membre_courant['civilite'] == $civilite)) ? 'selected' : '' ?>>
                            <?php echo $value ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="statut">Statut</label>
                <select class="form-control" id="statut" name="statut">
                    <?php
                   $status = array(
                        0 => 'Membre',
                        1 => 'Admin',
                    );
                    foreach ($status as $statut=>$value) {
                    ?>
                        <option value="<?php if($value == 'Membre'){echo 0;}else{echo 1;}?>"<?php echo (
                                    (isset($_POST['statut']) && $_POST['statut'] == $statut)
                                    ||
                                    (isset($membre_courant['statut']) && $membre_courant['statut'] == $statut)) ? 'selected' : '' ?>>
                            <?php echo $value ?>
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

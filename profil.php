<?php
require_once('inc/init.php');
//var_dump($_SESSION['membre']);

$title = 'Profil';

// si je ne suis pâs connecté, je suis invité à le faire pour accéder à mon compte
if (!isConnected()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

if (isset($_POST['modifcoord'])) {

    // on retire de post le btn qui nous a servi à identifier le formulaire
    unset($_POST['modifcoord']);

    //formulaire de mise à jour des données utilisateur
    $errorscoord = array();

    // Controles avant l'insertion en BDD
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errorscoord[] = 'il manque ' . $nb_champs_vides . ' information(s)';
    }
    // controle de l'émail
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errorscoord[] = 'format de mail invalide';
    }


    if (empty($errorscoord)) {

        $_POST['id_membre'] = $_SESSION['membre']['id_membre'];

        execRequete(" UPDATE membres 
                        SET civilite = :civilite,
                        nom = :nom,
                        prenom=:prenom,
                        email = :email
                        WHERE id_membre = :id_membre", $_POST);

        // changer aussi la session
        $_SESSION['membre']['civilite'] = $_POST['civilite'];
        $_SESSION['membre']['prenom'] = $_POST['prenom'];
        $_SESSION['membre']['nom'] = $_POST['nom'];
        $_SESSION['membre']['email'] = $_POST['email'];


        $_SESSION['message'] = 'coordonnées mises à jour';

        header('location:' . $_SERVER['PHP_SELF']); // permet que lorsqu'on fait F5 on ne renvoit pas encore une fois les meme données ==> eviter les doublons
        exit();
    }
}
if (isset($_POST['modifmdp'])) {
    //formulaire de chgt mdp
    // on retire de post le btn qui nous a servi à identifier le formulaire
    unset($_POST['modifmdp']);

    //formulaire de mise à jour des données utilisateur
    $errorsmdp = array();

    // Controles avant l'insertion en BDD
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errorsmdp[] = 'il manque ' . $nb_champs_vides . ' information(s)';
    }

    // verif du mot de passe actuel
    if (!empty($_POST['mdp']) && !password_verify($_POST['mdp'], $_SESSION['membre']['mdp'])) {
        $errorsmdp[] = "Mot de passe actuel incorrect";
    }

    // verification de la complexite du nouveau mdp
    if (!empty($_POST['newmdp']) && !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@])[\w$!\-\@]{8,20}$#', $_POST['newmdp'])) {
        $errorsmdp[] = 'le nouveau mdp doit etre compris entre 8 et 20 caracteres comprenant au moins 1 maj + 1 min + 1 chif + 1 caractere special: $ ! - _ @';
    }

    if (!empty($_POST['confirm']) && $_POST['newmdp'] !== $_POST['confirm']) {
        $errorsmdp[] = "la confirmation ne concorde pas avec le nouveau mdp";
    }

    if (!empty($_POST['mdp']) && $_POST['newmdp'] === $_POST['mdp']) {
        $errorsmdp[] = "le nx mdp doit etre diff du mdp actuel";
    }

    if (empty($errorsmdp)) {

        $newmdp = password_hash($_POST['newmdp'], PASSWORD_DEFAULT);
        execRequete(" UPDATE membres SET mdp = :newmdp WHERE id_membre = :id_membre", array(
            'newmdp' => $newmdp,
            'id_membre' => $_SESSION['membre']['id_membre']

        ));
        $_SESSION['membre']['mdp'] = $newmdp;

        $_SESSION['message2'] = 'mot de passe chngé avec succès';

        header('location:' . $_SERVER['PHP_SELF']);
        exit();
    }
}




require_once('inc/header.php');
//var_dump($_SESSION['membre']);

?>
<div class="row">
    <div class="identProfil col-md-6">
        <form method="post">

            <h2>Identifiant</h2>
            <hr>
            <p>pseudo:<strong> <?php echo $_SESSION['membre']['pseudo'] ?></strong></p>

            <?php if (!empty($errorscoord)) : // si $errors n'est pas vide cad qu'il y a des erreurs alors affiche l'alerte
            ?>
                <div class="alert alert-danger"><?php echo implode('<br>', $errorscoord) ?></div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['message'])) : ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['message'] ?>
                </div>
            <?php
                unset($_SESSION['message']);
            endif; ?>


            <div class="form-group">
                <label for="emal">Email</label>
                <input type="email" class="form-control <?php
                                                        echo (!empty($_POST['email'])
                                                            && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?php echo $_POST['email'] ?? $_SESSION['membre']['email'] ?>">
                <div class="invalid-feedback">
                    merci de saisir une adresse mail valide
                </div>
            </div>
            <br>
            <h2>Profil</h2>
            <hr>
            <div class="form-row">
                <div class="form-row">
                    <div class="form-group col-3">
                        <label for="civilite">Civilité</label>
                        <select name="civilite" id="civilite" class="form-control">
                            <option value="m" <?php
                                                echo (!empty($_POST['civilite'])
                                                    && $_POST['civilite'] == 'm')
                                                    ? "selected" : ''; ?>>Mr</option>
                            <option value="f" <?php
                                                echo ((!empty($_POST['civilite'])
                                                    && $_POST['civilite'] == 'f') ||
                                                    $_SESSION['membre']['civilite'] == 'f') ? "selected" : ''; ?>>Mme</option>
                        </select>
                    </div>
                    <div class="form-group col">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control <?php echo (!empty($_POST['nom']) &&
                                                                    (empty(trim($_POST['nom'])) ||
                                                                        iconv_strlen(trim($_POST['nom'])) < 2
                                                                        /**iconv_strlen = Retourne le nombre de caractères d'une chaîne*/
                                                                        ||
                                                                        iconv_strlen(trim($_POST['nom'])) > 20)) ? 'is-invalid' : '' ?>" id="nom" name="nom" value="<?php echo $_POST['nom'] ?? $_SESSION['membre']['nom'] //les ?? faut condition de départ (isset $_POST) existe, si existe le 1er ? c'est voir $_POST['nom] sinon le 2è ? va me chercher ''  
                                                                                                                                                                    ?>">
                        <div class="invalid-feedback">
                            merci de renseigner le nom
                        </div>
                    </div>
                    <div class="form-group col">
                        <label for="prenom">Prenom</label>
                        <input type="text" class="form-control <?php echo (!empty($_POST['prenom']) &&
                                                                    (empty(trim($_POST['prenom'])) ||
                                                                        iconv_strlen(trim($_POST['prenom'])) < 2 ||
                                                                        iconv_strlen(trim($_POST['prenom'])) > 20)) ? 'is-invalid' : '' ?>" id="prenom" name="prenom" value="<?php echo $_POST['prenom'] ?? $_SESSION['membre']['prenom'] ?>">
                        <div class="invalid-feedback">
                            merci de renseigner le prenom
                        </div>
                    </div>

                </div>
            </div>
            <button type="submit" name="modifcoord" class="btn btn-primary">Mettre à jour</button>
        </form>
    </div>
    <div class="col-md-6 modifMdpProfil">
        <h2>Changer le mot de passe</h2>
        <hr>

        <?php if (!empty($errorsmdp)) : // si $errors n'est pas vide cad qu'il y ades erreurs alors affiche l'alerte
        ?>
            <div class="alert alert-danger"><?php echo implode('<br>', $errorsmdp) ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['message2'])) : ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['message2'] ?>
            </div>
        <?php
            unset($_SESSION['message2']);
        endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="mdp">Mot de passe actuelle</label>
                <input type="password" class="form-control" id="mdp" name="mdp">
            </div>
            <div class="form-group">
                <label for="newmdp">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="newmdp" name="newmdp">
            </div>
            <div class="form-group">
                <label for="confirm">Confirmation</label>
                <input type="password" class="form-control" id="confirm" name="confirm">
            </div>
            <button type="submit" name="modifmdp" class="btn btn-primary">Valider</button>
            <br><br><br>
        </form>
    </div>
</div>
<?php


require_once('inc/footer.php');

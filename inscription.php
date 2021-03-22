<?php
require_once('inc/init.php');

$title = 'Inscription';

if (!empty($_POST)) {

    $errors = array();

    // Controles avant l'insertion en BDD
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
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
    // controle de l'unite du pseudo
    if (getMembreByPseudo($_POST['pseudo'])) {
        $errors[] = 'Pseudo indisponible. Merci d\'en choisir un autre';
    }

    /**************************************************** */
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

        execRequete("INSERT INTO membres VALUES(NULL,:pseudo,:mdp,:nom,:prenom,:email,:civilite, 0,NOW())", $_POST); // '0' correspond à statut

        //$membre = getMembreByPseudo($_POST['pseudo'])->fetch();
        //$_SESSION = $membre;

        $_SESSION['membre'] = getMembreByPseudo($_POST['pseudo'])->fetch(); // pas besoin de préciser car voir init.php

        header("location:" . URL . 'profil.php');
        exit();
    }
}
 
require_once('inc/header.php');
?>
<h1 class="inscription">Inscription</h1>
<hr>
<?php if (!empty($errors)) : // si $errors n'est pas vide cad qu'il y ades erreurs alors affiche l'alerte
?>
    <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
<?php endif; ?>

<form method="post" class="pb-4">
    <fieldset>
        <legend>Identifiants</legend>

        <!--***** Pseudo ******-->
        <div class="form-group col-6">
            <label for="pseudo">Pseudo</label>
            <input type="text" class="form-control <?php echo (!empty($_POST) &&
                                                        (empty(trim($_POST['pseudo'])) ||
                                                            iconv_strlen(trim($_POST['pseudo'])) < 2 ||
                                                            iconv_strlen(trim($_POST['pseudo'])) > 20)) ? 'is-invalid' : '' ?>" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo'] ?? '' ?>">
            <div class="invalid-feedback">
                merci de renseigner le pseudo
            </div>
        </div>
        <!--***** MDP ******-->
        <div class="form-group col-6">
            <label for="mdp">Mot de passe</label>
            <input type="password" class="form-control <?php

                                                        echo (!empty($_POST)
                                                            && !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@])[\w$!\-\@]{8,20}$#', $_POST['mdp'])) ? 'is-invalid' : '' ?>       
            " id="mdp" name="mdp">
            <div class="invalid-feedback">
                merci de saisir un mot de passe compris entre 8 et 20 caracteres comportant au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractere special ($ ! - _ @)
            </div>
        </div>
        <!--***** nom ******-->
        <div class="form-group col-6">
            <label for="nom">Nom</label>
            <input type="text" class="form-control <?php echo (!empty($_POST) &&
                                                        (empty(trim($_POST['nom'])) ||
                                                            iconv_strlen(trim($_POST['nom'])) < 2
                                                            /**iconv_strlen = Retourne le nombre de caractères d'une chaîne*/
                                                            ||
                                                            iconv_strlen(trim($_POST['nom'])) > 20)) ? 'is-invalid' : '' ?>" id="nom" name="nom" value="<?php echo $_POST['nom'] ?? '' //les ?? faut condition de départ (isset $_POST) existe, si existe le 1er ? c'est voir $_POST['nom] sinon le 2è ? va me chercher ''  
                                                                                                                                                        ?>">
            <div class="invalid-feedback">
                merci de renseigner le nom
            </div>
        </div>
        <!--***** prenom ******-->
        <div class="form-group col-6">
            <label for="prenom">Prenom</label>
            <input type="text" class="form-control <?php echo (!empty($_POST) &&
                                                        (empty(trim($_POST['prenom'])) ||
                                                            iconv_strlen(trim($_POST['prenom'])) < 2 ||
                                                            iconv_strlen(trim($_POST['prenom'])) > 20)) ? 'is-invalid' : '' ?>" id="prenom" name="prenom" value="<?php echo $_POST['prenom'] ?? '' ?>">
            <div class="invalid-feedback">
                merci de renseigner le prenom
            </div>
        </div>
        <!--***** Email ******-->
        <div class="form-group col-6">
            <label for="email">Email</label>
            <input type="email" class="form-control <?php
                                                    echo (!empty($_POST)
                                                        && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>
            " id="email" name="email" value="<?php echo $_POST['email'] ?? '' ?>">
            <div class="invalid-feedback">
                merci de saisir une adresse mail valide
            </div>
        </div>
        <!--***** civilite ******-->
        <div class="form-group col-3">
            <label for="civilite">Civilité</label>
            <select name="civilite" id="civilite" class="form-control">
                <option value="m" <?php
                                    echo (!empty($_POST['civilite'])
                                        && $_POST['civilite'] == 'm')
                                        ? "selected" : ''; ?>>Mr</option>
                <option value="f" <?php
                                    echo (!empty($_POST)
                                        && $_POST['civilite'] == 'f')
                                        ? "selected" : ''; ?>>Mme</option>
            </select>
        </div>

    </fieldset>

    <button type="submit" class="btn btn-primary">S'inscrire</button>
    <br><br><br>
</form>

<?php
require_once('inc/footer.php');

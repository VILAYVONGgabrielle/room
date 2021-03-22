<?php
require_once('inc/init.php');

// Gerer la déconnexion   // reverif que c'est bien 'déconnexion' de la page header.php
if (isset($_GET['action']) && $_GET['action'] == 'deconnexion') {

    // Destruction totale de la Session

    //session_destroy();

    // destruction de la variable membre (on conserve un éventuel panier)
    unset($_SESSION['membre']);
    header('location:' . URL . 'connexion.php'); // rediriger vers page connexion
    //header('location:'. URL); // rediriger vers page d'accueil
    exit();
}


// si le formulaire est posté
if (!empty($_POST)) {
    if (empty($_POST['pseudo']) || empty($_POST['mdp'])) {
        $errors[] = 'Merci de remplir tous les champs';
    }
    if (empty($errors)) {
        // Controler l'existence de l'utilisateur
        if ($membre = getMembreByPseudo($_POST['pseudo'])) {
            // controler le mdp
            $info = $membre->fetch();
            if (password_verify($_POST['mdp'], $info['mdp'])) {
                //ok
                $_SESSION['membre'] = $info;
                header('location:' . URL . 'profil.php');
                exit(); //stop le déroulement du script ultérieur lorsqu'il fait un location c'est pour cela qu'on place ce code avant le header.php
            } else {
                $errors[] = 'erreur sur les identifiants';
            }
        } else {
            $errors[] = 'erreur sur les identifiants';
        }
    }
}

$title = 'Connexion';

require_once('inc/header.php');

?>
<h1 class="connexion">Connexion</h1>
<hr>
<?php if (!empty($errors)) : // si $errors n'est pas vide cad qu'il y ades erreurs alors affiche l'alerte
?>
    <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
<?php endif; ?>

<form method="post" class="pb-4">
    <!--.form-group*2>label+input.form-control-->
    <div class="form-group col-6">
        <label for="pseudo">Pseudo</label>
        <input type="text" class="form-control <?php echo (!empty($_POST) && empty(trim($_POST['pseudo']))) ? 'is-invalid' : '' ?>" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo'] ?? '' ?>">
        <div class="invalid-feedback">
            merci de renseigner ce champ
        </div>
    </div>


    <div class="form-group col-6">
        <label for="mdp">Mot de passe</label>
        <input type="text" class="form-control <?php echo (!empty($_POST) && empty(trim($_POST['mdp']))) ? 'is-invalid' : '' ?>" id="mdp" name="mdp">
        <div class="invalid-feedback">
            merci d'indiquer votre mot de passe'
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Se connecter</button>
    <br><br><br>
</form>
<?php

require_once('inc/footer.php');

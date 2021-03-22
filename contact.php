<?php
require_once('inc/init.php');

$title = 'Contact';


require_once('inc/header.php');
?>
<h1 class="contact">Contact</h1>
<hr>
<?php
$message = '';
if (!empty($_POST)) {

    $errors = array();

    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errors[] = 'il manque ' . $nb_champs_vides . ' information(s)';
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
    // controle societe
    if (
        iconv_strlen(trim($_POST['societe'])) < 2 ||
        iconv_strlen(trim($_POST['societe'])) > 20
    ) {
        $errors[] = 'champs invalide';
    }
    // controle sujet
    if (
        iconv_strlen(trim($_POST['sujet'])) < 2 ||
        iconv_strlen(trim($_POST['sujet'])) > 20
    ) {
        $errors[] = 'Prenom invalide';
    }
    // controle de l'émail
    if (!filter_var($_POST['expediteur'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'format de mail invalide';
    }
    // controle du message
    if (
        iconv_strlen(trim($_POST['message'])) < 2 ||
        iconv_strlen(trim($_POST['message'])) > 300
    ) {
        $errors[] = 'champs message invalide';
    }

    /**   SI AUCUNE ERREEUR ALORS ON PROCEDE 0 L EXECUTION DE LA REQUETE **/
    if (empty($errors)) {

        // entête email
        $destinataire = 'gabriellev@lotusdigitek.fr, postmaster@lotusdigitek.fr';
        $expediteur = $_POST['expediteur'];
        $sujet = $_POST['sujet'];
        $headers = 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/html; charset=ISO-8859-1' . "\n";
        $headers .= 'Reply-To: ' . $expediteur . "\n";
        $headers .= 'From: "' . ucfirst(substr($expediteur, 0, strpos($expediteur, '@'))) . '"<' . $expediteur . '>' . "\n";
        $headers .= 'Delivered-to: '.$destinataire. "\n";
        $message = "Nom : " . $_POST['nom'] . "\nPrenom : " . $_POST['prenom'] . "\nSociete : " . $_POST['societe'] . "\nMessage : " .$_POST['message'];
        if(mail($destinataire, $sujet, $message, $headers)){
            $message = "<div class=\"alert alert-success\" role=\"alert\">Votre message a été envoyé!</div>";
        }else{
            $message = "<div class=\"alert alert-warning\" role=\"alert\">Votre message n'a pas pu être envoyé!</div>";
        }
    }
}
?> <div class="row justify-content-center ">
    <div class="col-9 bg-faded">
        <?= $message ?>

        <form method="post" action="">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nom">Nom</label><br>
                    <input name="nom" id="nom" placeholder="Nom" type="text" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="prenom">Prenom</label><br>
                    <input name="prenom" id="prenom" placeholder="Prénom" type="text" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="societe">Société</label><br>
                    <input name="societe" id="societe" placeholder="Sociéte" type="text" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="expediteur">Expediteur</label><br>
                    <input type="text" name="expediteur" id="expediteur" placeholder="aaa@gmail.com" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="sujet">Sujet</label><br>
                    <input type="text" name="sujet" id="sujet" placeholder="Titre du sujet" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="message">Message</label><br>
                    <textarea name="message" placeholder="Laissez votre message " class="form-control" required></textarea>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-9">
                    <input type="submit" value="Valider" class="btn btn-info">
                </div>
            </div>
    </div>
</div>
<?php

require_once('inc/footer.php');

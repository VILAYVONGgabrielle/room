<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room | <?php echo $title ?></title>
    <!-- bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <!--font aweson-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <!-- feuille style -->
    <link rel="stylesheet" href="<?php echo URL ?>inc/css/style.css">
    <!-- feuille jquery -->
    <link rel="stylesheet" href="<?php echo URL ?>libraries/jquery-ui-1.12.1/jquery-ui.min.css" />
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
    <!--   script datepicker-->
    <script src="<?php echo URL ?>inc/js/functionJqueryDatepicker.js"></script>
    <!-- script perso -->
    <script src="<?php echo URL ?>inc/js/functionsJquery.js"></script>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top p-5 bgColor">
            <a class="navbar-brand" href="<?php echo URL ?>">Room</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item <?php echo ($title == 'Accueil') ? 'active' : '' ?>">
                        <a class="nav-link" href="<?php echo URL ?>">Accueil <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item <?php echo ($title == 'About') ? 'active' : '' ?>">
                        <a class="nav-link" href="<?php echo URL ?>about.php">Qui sommes nous <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item <?php echo ($title == 'Contact') ? 'active' : '' ?>">
                        <a class="nav-link" href="<?php echo URL ?>contact.php">Contact <span class="sr-only">(current)</span></a>
                    </li>
                    <!--membre non connecté-->
                    <?php if (!isConnected()) : ?>
                        <li class="nav-item  <?php echo ($title == 'Inscription'/* inscription vient du title de la page inscription.php */) ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>inscription.php">Inscription</a>
                        </li>
                        <li class="nav-item  <?php echo ($title == 'Connexion') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>connexion.php">Connexion</a>
                        </li>
                    <?php endif; ?>

                    <!--membre connecté-->
                    <?php if (isConnected()) : ?>
                        <li class="nav-item  <?php echo ($title == 'Compte') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>profil.php">Mon Profil</a>
                        </li>
                        <li class="nav-item  <?php echo ($title == 'Mes commandes') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>commandes.php">Mes commandes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URL ?>connexion.php? action=deconnexion">Se déconnecter</a>
                        </li>
                    <?php endif; ?>

                    <!-- Administrateur-->
                    <?php if (isAdmin()) : ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="menuadmin" data-toggle="dropdown">Admin</a>
                            <div class="dropdown-menu" aria-labelledby="menuadmin">
                                <a class="dropdown-item" href="<?php echo URL ?>backoffice/gestionMembres.php">Gestion des membres</a>
                                <a class="dropdown-item" href="<?php echo URL ?>backoffice/gestionSalles.php">Gestion des salles</a>
                                <a class="dropdown-item" href="<?php echo URL ?>backoffice/gestionProduits.php">Gestion des produits</a>
                                <a class="dropdown-item" href="<?php echo URL ?>backoffice/gestionCommandes.php">Gestion des commandes</a>
                                <a class="dropdown-item" href="<?php echo URL ?>backoffice/gestionAvis.php">Gestion des avis</a>
                                <a class="dropdown-item" href="<?php echo URL ?>backoffice/statistiques.php">Statistiques</a>
                            </div>
                        </li>
                    <?php endif; ?>

                </ul>
                <!-- Membre connecté-->
                <div class="iconpanier"><?php if(isset($_SESSION['membre']['pseudo'])){echo $_SESSION['membre']['pseudo'];}else{echo '';}?></div>
                <!--Panier-->
                <div class="nav-item <?php echo ($title == 'Panier') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?php echo URL ?>panier.php"><i class="fas fa-shopping-cart fa-lg iconpanier"></i> <?php echo nbProduits() ?></a>

                </div>
            </div>
        </nav>
    </header>
    <main class="container">
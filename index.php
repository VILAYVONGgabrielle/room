<?php
require_once('inc/init.php');

$title = 'Accueil';

require_once('inc/header.php');



$categories = execRequete(" SELECT DISTINCT categorie FROM salles ORDER BY categorie");
$villes = execRequete(" SELECT DISTINCT ville FROM salles ORDER BY ville");
$whereclause = "WHERE etat = 'libre'";
$args = array();
// gerer l'eventuel filtre sur la categorie
if (!empty($_GET)) {
    foreach ($_GET as $key => $value) {
        switch ($key) {
            case 'capacite':
                if ($_GET['capacite'] !== '' && is_numeric($_GET['capacite'])) {
                    $whereclause .= ' AND ' . $key . '>=:' . $key;
                    $args[$key] = $value;
                } else {
                    $whereclause .= ' AND ' .  $key . '>= 0';
                }
                break;
            case 'prix':
                if ($_GET['prix'] !== '' && is_numeric($_GET['prix'])) {
                    $whereclause .= ' AND ' . $key . '<=:' . $key;
                    $args[$key] = $value;
                } else {
                    $whereclause .= ' AND ' .  $key . '>= 0';
                }
                break;
            case 'date_arrivee':
                if ($_GET['date_arrivee'] !== '') {
                    $whereclause .= ' AND ' . $key . '>=:' . $key;
                    $args[$key] = date('Y/m/d', strtotime($value));
                } else {
                    $whereclause .= ' AND ' .  $key . ">= NOW()";
                }
                break;
            case 'date_depart':
                if ($_GET['date_depart'] !== '') {
                    $whereclause .= ' AND ' . $key . '<=:' . $key;
                    $args[$key] = date('Y/m/d', strtotime($value));
                } else {
                    $whereclause .= ' AND ' .  $key . ">= NOW()";
                }
                break;
            case 'categorie': /* valeur de la value est index  //  si la value est !=0 on entre dans la condition et passe par la whereclause*/
                if ($_GET['categorie'] !== '0') {
                    $whereclause .= ' AND ' . $key . '=:' . $key;
                    $args[$key] = $value;
                }

                break;
            case 'ville':
                if ($_GET['ville'] !== '0') {
                    $whereclause .= ' AND ' . $key . '=:' . $key;
                    $args[$key] = $value;
                }
                break;
        }
    }
}

//var_dump($whereclause);
// SELECT actionné par le btn formulaire
$produits = execRequete(" SELECT * 
FROM salles sal1
INNER JOIN produits pdt1 ON pdt1.id_salle = sal1.id_salle
$whereclause", $args);
//var_dump($produits);
//var_dump($args);
//var_dump($_GET);

// corps de la page
?>
<h1 class="accueil">Accueil</h1>
<hr>
<!--.row>.col-md-3+.col-md-9-->
<div class="row">
    <div class="col-md-3">
        <form method="get">
            <!--lead = entete-->
            <p class="lead pt-3">Catégories</p>
            <div class="list-group">
                <select class="form-control" name="categorie">
                    <option value="0" class="list-group-item">Toutes</option> <!-- value par default prend la valeur entre balise option ==> ca alimente la condition dans le switch-->
                    <?php
                    while ($categorie = $categories->fetch()) {
                    ?>
                        <option class="list-group-item">
                            <?php echo ucfirst($categorie['categorie']) ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <p class="lead pt-3">Villes</p>
            <div class="list-group">
                <select class="form-control" name="ville">
                    <option value="0" class="list-group-item">Toutes</option>
                    <?php
                    while ($ville = $villes->fetch()) {
                    ?>
                        <option class="list-group-item">
                            <?php echo ucfirst($ville['ville']) ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <p class="lead pt-3">Capacite</p>
            <div class="list-group">
                <input type="number" class="form-control" id="capacite" name="capacite" placeholder="20">
            </div>
            <p class="lead pt-3">Prix</p>
            <div class="list-group">
                <input type="range" class="range" id="prix" name="prix" min="100" max="20000" value="20000" step="100">
                <output></output>
            </div>

            <p class="lead pt-3">Période</p>
            <div class="list-group">
                <label for="date_arrivee">Date d'arrivée</label>
                <input type="text" class="form-control" id="date_arrivee" name="date_arrivee" placeholder="00/00/00">
            </div>
            <div class="list-group">
                <label for="date_depart">Date de départ</label>
                <input type="text" class="form-control" id="date_depart" name="date_depart" placeholder="00/00/0000">
            </div>
            <br>
            <button type="submit" class="btn btn-primary">valider</button>
        </form>
    </div>

    <div class="col-md-9">
        <?php
        if ($produits->rowCount() == 0) {
        ?>
            <div class="alert alert-info mt-5">Pas encore de pdts dans la boutique. Revenez bientot</div>
        <?php
        } else {
        ?>
            <div class="row">
                <?php
                while ($produit = $produits->fetch()) :

                ?>
                    <div class="col-md-4 p-1">
                        <div class="border">

                            <div class="thumbnail">
                                <a href="ficheProduit.php?id_produit=<?php echo $produit['id_produit'] ?>">
                                    <img src="<?php echo URL . 'photos/' . $produit['photo'] ?>" alt="<?php echo $produit['titre'] ?>" class="img-fluid"></a>
                            </div>
                            <div class="caption m-2">
                                <h6 class="float-right "><?php echo number_format($produit['prix'], 2, ',', ' ') ?>€
                                </h6>
                                <h6>
                                    <a href="ficheProduit.php?id_produit=<?php echo $produit['id_produit'] ?>">
                                        <?php echo $produit['categorie'] . '-' . $produit['titre'] ?>
                                    </a>
                                </h6>
                                <?php  //var_dump($produit); 
                                if ($produit['description']) {
                                    $value = $produit['description'];
                                    //$value =(iconv_strlen($value)>30) ? substr($value,0,30). '...' : $value;
                                    $extrait = (iconv_strlen($produit['description']) > 30) ? substr($value, 0, 30) : $produit['description']; // ternaire
                                    // pour eviter de couper un mot 
                                    if ($extrait != $produit['description']) {
                                        $slastSpace = strrpos($extrait, ' '); // renvoie la position de la derniere occurence
                                        $value = substr($extrait, 0, $slastSpace) . '...';
                                    }
                                }
                                echo $value;
                                ?>
                                </h6>
                                <h6>
                                    <?php echo date('d/m/Y', strtotime($produit['date_arrivee'])) . ' au ' . date('d/m/Y', strtotime($produit['date_depart'])) ?>
                                </h6>
                                <div class="row">
                                   
                                    <a class="col-md-4" href="<?php echo URL . 'avis.php?id_salle=' . $produit['id_salle'] . '&id_produit=' . $produit['id_produit'] ?>"><i class="fas fa-search"> avis </i></a>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php
                endwhile;
                ?>
            </div>
        <?php
        }
        ?>
    </div>
</div>
<?php
require_once('inc/footer.php');

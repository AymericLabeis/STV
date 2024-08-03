<?php

declare(strict_types=1);
session_start();
// Vérifie si le paramètre "logout" est présent dans l'URL
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
  // Détruit toutes les variables de session
  session_unset();
  session_destroy();
  header("Location: index.php");
  exit();
}
require_once('database.php');
$pdo = connectDB();
require_once('session_function.php');
updateLastAccess();

$moisIndex = date('n');
$moisList = "";
// Requête préparée récupérant les fruits et légumes du mois en cours
$query = 'SELECT flm.id_fruits_legumes, fl.libelle, fl.img, fl.img_dispo, fl.prix, fl.kilo_piece ,fl.vitamines, fl.mineraux
          FROM fruits_legumes_mois as flm
          INNER JOIN fruits_legumes as fl ON fl.id_fruits_legumes = flm.id_fruits_legumes
          WHERE id_mois = :moisIndex
          ORDER BY fl.libelle ASC';

$fruit_legume = $pdo->prepare($query);
$fruit_legume->bindParam(':moisIndex', $moisIndex, PDO::PARAM_INT);
$fruit_legume->execute();
$fruits_legumes = $fruit_legume->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['carte'])) {
  // Requête SQL pour rechercher des recettes en fonction du terme de recherche
  $query = 'SELECT * FROM fruits_legumes WHERE libelle LIKE :searchTerm ORDER BY libelle ASC';
  $fruit_legume = $pdo->prepare($query);

  // Paramètre du terme de recherche avec le caractère joker "%" pour rechercher des correspondances partielles
  $searchTerm = '%' . $_GET['carte'] . '%';
  $fruit_legume->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
  $fruit_legume->execute();
  $fruits_legumes = $fruit_legume->fetchAll(PDO::FETCH_ASSOC);

  // Renvoie les suggestions au format JSON
  /*  $suggestionQuery = 'SELECT libelle FROM fruits_legumes WHERE libelle LIKE :searchTerm ORDER BY libelle ASC';
    $suggestionStmt = $pdo->prepare($suggestionQuery);
    $suggestionStmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    $suggestionStmt->execute();
    $suggestions = $suggestionStmt->fetchAll(PDO::FETCH_ASSOC);

    // Renvoie les suggestions au format JSON
    echo json_encode($suggestions);
    exit();*/
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['F_L'])) {
    $moisList = $_POST['mois'];
    $action = $_POST['F_L'];

    $query = 'SELECT flm.id_fruits_legumes, fl.libelle, fl.img, fl.img_dispo, fl.prix, fl.kilo_piece, fl.vitamines, fl.mineraux
                  FROM fruits_legumes_mois as flm
                  INNER JOIN fruits_legumes as fl ON fl.id_fruits_legumes = flm.id_fruits_legumes
                  WHERE id_mois = :moisList';

    if ($action === 'Fruit') {
      $query .= ' AND type="fruit"';
    } elseif ($action === 'Legume') {
      $query .= ' AND type="legume"';
    }

    $query .= ' ORDER BY fl.libelle ASC';

    //usleep(500000);
    $fruit_legume = $pdo->prepare($query);
    $fruit_legume->bindParam(':moisList', $moisList, PDO::PARAM_INT);
    $fruit_legume->execute();
    $fruits_legumes = $fruit_legume->fetchAll(PDO::FETCH_ASSOC);
  }

  if (isset($_POST['FL'])) {
    $actionFL = $_POST['FL'];
    $moisList = $_POST['mois'];

    $query = 'SELECT flm.id_fruits_legumes, fl.libelle, fl.img, fl.img_dispo, fl.prix, fl.kilo_piece, fl.vitamines, fl.mineraux
          FROM fruits_legumes_mois as flm
          INNER JOIN fruits_legumes as fl ON fl.id_fruits_legumes = flm.id_fruits_legumes
          WHERE id_mois = :moisList
          ORDER BY fl.libelle ASC';

    usleep(500000);
    $fruit_legume = $pdo->prepare($query);
    $fruit_legume->bindParam(':moisList', $moisList, PDO::PARAM_INT);
    $fruit_legume->execute();
    $fruits_legumes = $fruit_legume->fetchAll(PDO::FETCH_ASSOC);
  }
}
?>
<!DOCTYPE html>
<html lang="fr-FR">

<head>


  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Fusion Craft" content="fruits et légumes de saison">
  <title>Acceuil</title>
  <link href="https://fonts.googleapis.com/css2?family=Spicy+Rice&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Marck+Script&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Paprika&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <header>

    <div class="logo_title">
      <a href="index.php" id="InitialMonth"><img class="logo" src="ressources/logo2.png" alt="Image du logo"></a>
      <h1 class="titre">Fusion Craft</h1>
    </div>
  </header>
  <nav>
    <div class="menu">
      <div class="recherche_FL">
        <form class="formSearch" method="get">
          <input type="text" id="carte" name="carte" placeholder="Rechercher un fruit ou un légume" value="<?= htmlspecialchars($_GET['carte'] ?? '') ?>">
          <ul id="suggestionsList">

          </ul>
          <button type="submit" class="rechercher">Rechercher</button>
        </form>
        <button class="btn_compte" onclick=" myAccount()">≡</button>
        <ul id="compte">
          <?php
          if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            echo '<a href="compte.php"><li>Mon compte</li></a>';
            echo '<a href="mesRecettes.php"><li>Mes recettes</li></a>';
            echo '<a href="mesFiches.php"><li>Mes fiches</li></a>';
            echo '<a href="edit_categories.php"><li>Categories</li></a>';
            echo '<a href="index.php?logout=true"><li>Déconnexion</li></a>';
          } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'DEFAULT') {
            echo '<a href="compte.php"><li>Mon compte</li></a>';
            echo '<a href="mesRecettes.php"><li>Mes recettes</li></a>';
            echo '<a href="storytelling.php"><li>Storytelling</li></a>';
            echo '<a href="contact.php"><li>Contact</li></a>';
            echo '<a href="index.php?logout=true"><li>Déconnexion</li></a>';  
          } else {
            echo '<a href="connexion.php"><li id="compte-li-connexion">Connexion</li></a>';
            echo '<a href="storytelling.php"><li>Storytelling</li></a>'; 
            echo '<a href="contact.php"><li>Contact</li></a>';            
          }
          ?>
        </ul>
      </div>

      <form class="formFL" method="post">
        <div class="button_FL">
          <button type="submit" class="flecheG" name="FL" onclick="rotateRoue(-30)"></button>
          <select id="mois" name="mois">
            <option value="01">Janvier</option>
            <option value="02">Février</option>
            <option value="03">Mars</option>
            <option value="04">Avril</option>
            <option value="05">Mai</option>
            <option value="06">Juin</option>
            <option value="07">Juillet</option>
            <option value="08">Août</option>
            <option value="09">Septembre</option>
            <option value="10">Octobre</option>
            <option value="11">Novembre</option>
            <option value="12">Décembre</option>
          </select>
          <button type="submit" id="btnFLR" class="button_F" name="F_L" value="Fruit">Fruits</button>
          <button type="submit" id="btnFLR" class="button_L" name="F_L" value="Legume">Légumes</button>
          <button type="submit" class="flecheD" name="FL" onclick="rotateRoue(30)"></button>
        </div>
      </form>

      <div class="button_Recettes">
        <a href="Recettes.php" id="btnFLR" class="btn_Recettes">Recettes</a>
      </div>
      <div class="RoueSelect" id="moisRoue">
        <button class="buttonR janvier" value="01" draggable="true" ondragstart="dragStart(event)">
          <h2>Janvier</h2>
        </button>
        <button class="buttonR fevrier" value="02" draggable="true" ondragstart="dragStart(event)">
          <h2>Février</h2>
        </button>
        <button class="buttonR mars" value="03" draggable="true" ondragstart="dragStart(event)">
          <h2>Mars</h2>
        </button>
        <button class="buttonR avril" value="04" draggable="true" ondragstart="dragStart(event)">
          <h2>Avril</h2>
        </button>
        <button class="buttonR mai" value="05" draggable="true" ondragstart="dragStart(event)">
          <h2>Mai</h2>
        </button>
        <button class="buttonR juin" value="06" draggable="true" ondragstart="dragStart(event)">
          <h2>Juin</h2>
        </button>
        <button class="buttonR juillet" value="07" draggable="true" ondragstart="dragStart(event)">
          <h2>Juillet</h2>
        </button>
        <button class="buttonR aout" value="08" draggable="true" ondragstart="dragStart(event)">
          <h2>Août</h2>
        </button>
        <button class="buttonR septembre" value="09" draggable="true" ondragstart="dragStart(event)">
          <h2>Septembre</h2>
        </button>
        <button class="buttonR octobre" value="10" draggable="true" ondragstart="dragStart(event)">
          <h2>Octobre</h2>
        </button>
        <button class="buttonR novembre" value="11" draggable="true" ondragstart="dragStart(event)">
          <h2>Novembre</h2>
        </button>
        <button class="buttonR decembre" value="12" draggable="true" ondragstart="dragStart(event)">
          <h2>Decembre</h2>
        </button>
      </div>
    </div>
  </nav>
  <main>
    <div class="container_carte">
      <?php if (empty($fruits_legumes)) : ?>
        <h3>Aucune carte de fruit ou de légume trouvée</h3>
      <?php else : ?>
        <?php foreach ($fruits_legumes as $fruit_legume) : ?>
          <div class="carte">
            <div class="double-face">
              <div class="face">
                <img src="ressources/FL/<?= $fruit_legume['img'] ?>" alt="<?= $fruit_legume['img'] ?>">
                <h2><?= $fruit_legume['libelle'] ?></h2>
              </div>
              <div class="arriere">
                <img src="ressources/img_dispo/<?= $fruit_legume['img_dispo'] ?>">
                <div class="carte_info">
                  <div class="info">
                    <p id="prix"><?= $fruit_legume['prix'] ?><?= $fruit_legume['kilo_piece'] ?></p>
                  </div>
                  <div class="info">
                    <h3>Vitamines:</h3>
                    <p><?= $fruit_legume['vitamines'] ?></p>
                  </div>
                  <div class="info">
                    <h3>Mineraux:</h3>
                    <p> <?= $fruit_legume['mineraux'] ?></p>
                  </div>
                </div>
                <a href="recettes.php?carte=<?= $fruit_legume['libelle'] ?>">Idée Recette</a>
              </div>
            </div>
          </div>
        <?php endforeach ?>
      <?php endif; ?>
    </div>
  </main>
  <footer class="footerMobil">
    <div class="footerL">
      <a href="index.php">Accueil</a>
    </div>
    <div class="footerR">
      <a href="Recettes.php">Recettes</a>
    </div>
  </footer>
  <footer class="footerDesktop">
    <h2>Aymeric LABEIS copyright 2023</h2>
  </footer>

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="app.js"></script>
</body>

</html>
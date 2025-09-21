<?php include 'imports.php' ?>
<?php include 'session.php' ?>
<?php

if (isset($_GET['userId'])) {

    // $sql = "SELECT Utilisateur_Id, Photo, Pseudo FROM utilisateur WHERE Utilisateur_Id = ?";
    // $stmt = $pdo->prepare($sql);
    // $stmt->execute([$_GET['userId']]);
    // $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user = Utilisateur::loadUserImageFromId($_GET['userId'], $pdo);

    // Définir l'en-tête du type de contenu
    header("Content-Type: image/jpeg");

    // // Définir l'en-tête de la taille du contenu
    header("Content-Length: " . strlen($user['Photo']));

    // Afficher les données binaires de l'image
    echo $user['Photo'];
    // echo $_GET['userId'];
}

?>
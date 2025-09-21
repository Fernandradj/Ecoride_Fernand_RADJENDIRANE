<?php include 'imports.php' ?>
<?php include 'session.php' ?>
<?php

$userIsPassager = true;
$userIsEmployee = false;

$resultat = null;

if ($userIsEmployee) {

    if (isset($_GET['avisId'])) {
        try {

            // Requête pour récupérer tous les utilisateurs
            $sql = "SELECT Avis_Id, avis.Utilisateur_Id, Commentaire, avis.Note, avis.Statut, avis.Covoiturage_Id, covoiturage.Statut, Ville_arrivee, Ville_depart,  Date_arrivee , utilisateur.Nom, utilisateur.Prenom, utilisateur.Email, chauffeur.Nom nomChauffeur, chauffeur.Prenom prenomChauffeur, chauffeur.Email emailChauffeur  FROM avis JOIN utilisateur ON utilisateur.Utilisateur_Id = avis.Utilisateur_Id JOIN covoiturage ON avis.Covoiturage_Id = covoiturage.Covoiturage_Id JOIN voiture ON covoiturage.Voiture_Id = voiture.Voiture_Id JOIN utilisateur chauffeur ON voiture.Utilisateur_Id = chauffeur.Utilisateur_Id WHERE Avis_Id = " . $_GET['avisId'] . " LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
            // echo $resultat;

        } catch (PDOException $e) {
            echo "Erreur de connexion : " . $e->getMessage();
        }
    }
}

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // $id_trajet = $_POST['id_trajet'];
        // $note = $_POST['note'];
        // $commentaire = $_POST['commentaire'];

        // // Insertion dans la table des évaluations
        // $sql = "INSERT INTO evaluations (id_trajet, note, commentaire, date_evaluation) VALUES (?, ?, ?, NOW())";
        // $stmt = $pdo->prepare($sql);
        // $stmt->execute([$id_trajet, $note, $commentaire]);

        // echo "Évaluation soumise avec succès !";
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
<?php
// Connexion à la base de données (comme ci-dessus)
// ...

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_trajet = $_POST['id_trajet'];
    $action = $_POST['action'];

    // if ($action == 'valider') {
    //     // Mettre à jour le statut du trajet dans la base de données
    //     $sql = "UPDATE trajets SET statut = 'validé' WHERE id = ?";
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->execute([$id_trajet]);
    //     echo "Trajet validé.";
    // } elseif ($action == 'refuser') {
    //     // Mettre à jour le statut du trajet
    //     $sql = "UPDATE trajets SET statut = 'refusé' WHERE id = ?";
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->execute([$id_trajet]);
    //     echo "Trajet refusé.";
    // }
}
?>
<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>
    <title>Avis</title>
</head>

<body>

    <?php include 'header.php' ?>


    <!-- main -->
    <main>

        <form action="traitement_evaluation.php" method="post">
            <div class="avis_note_section">
                <label for="note">Note :</label>
                <?php if ($userIsPassager): ?>
                    <select name="note" id="note">
                        <option value="5">⭐⭐⭐⭐⭐</option>
                        <option value="4">⭐⭐⭐⭐</option>
                        <option value="3">⭐⭐⭐</option>
                        <option value="2">⭐⭐</option>
                        <option value="1">⭐</option>
                    </select>
                <?php endif; ?>
                <?php
                if ($userIsEmployee) {
                    echo '<img class="avis_star" src="images/star_' . $resultat['Note'] . '.png" alt="' . $resultat['Note'] . ' sur 5">';
                }
                ?>
            </div>
            <br>
            <label for="commentaire">Commentaire :</label>
            <textarea name="commentaire" id="commentaire" rows="4" cols="50" <?php if ($userIsEmployee) {
                echo "disabled";
            } ?>></textarea>
            <br>
            <?php if ($userIsPassager): ?>
                <input type="hidden" name="id_trajet" value="123">
                <button type="submit">Soumettre</button>
            <?php endif; ?>

        </form>
        <?php if ($userIsEmployee): ?>
            <form action="traitement_trajet.php" method="post">
                <input type="hidden" name="id_trajet" value="123">
                <button type="submit" name="action" value="valider">Valider</button>
                <button type="submit" name="action" q="refuser">Refuser</button>
            </form>
        <?php endif; ?>

    </main>

    <?php include 'footer.php' ?>

</body>

</html>
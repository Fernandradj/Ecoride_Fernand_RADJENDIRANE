<?php include 'imports.php' ?>
<?php include 'session.php' ?>

<?php

if (isset($_GET['userId'])) {
    try {

        // Requête pour récupérer tous les voyages
        $sql = "SELECT Covoiturage_Id, Date_depart, Heure_depart, Ville_depart, Date_arrivee, Heure_arrivee, Ville_arrivee, covoiturage.Statut, covoiturage.Nb_place, Duree FROM covoiturage JOIN voiture ON voiture.Voiture_id = covoiturage.Voiture_id JOIN utilisateur ON voiture.Utilisateur_id = utilisateur.Utilisateur_id WHERE utilisateur.Utilisateur_id = " . $_GET['userId'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Erreur de connexion : " . $e->getMessage();
    }
}
?>
<?php
if (isset($_POST['action']) && isset($_POST['voyages_selectionnes'])) {
    $action = $_POST['action'];
    $voyages_selectionnes = $_POST['voyages_selectionnes'];

    // Convertir le tableau en une chaîne pour la clause IN de la requête SQL
    $placeholders = implode(',', array_fill(0, count($voyages_selectionnes), '?'));


    /* try {

        if ($action === 'valider') {
            // Logique pour valider les voyages sélectionnés
            $stmt = $pdo->prepare("UPDATE voyages SET Statut = 'Validé' WHERE ID_voyage IN ($placeholders)");
            $stmt->execute($voyages_selectionnes);
            echo "Les voyages sélectionnés ont été validés.";

        } elseif ($action === 'annuler') {
            // Logique pour annuler les voyages sélectionnés
            $stmt = $pdo->prepare("UPDATE voyages SET Statut = 'Annulé' WHERE ID_voyage IN ($placeholders)");
            $stmt->execute($voyages_selectionnes);
            echo "Les voyages sélectionnés ont été annulés.";
        }

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    } */
} else {
    // echo "Aucune action ou aucun voyage sélectionné.";
}
?>


<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>
    <title>Eco Ride</title>
</head>

<body>

    <?php include 'header.php' ?>


    <!-- main -->
    <main>
        <div class="liste_voyages_container">
            <h1>Mes voyages</h1>
            <form action="traitement.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th>Date départ</th>
                            <th>Heure départ</th>
                            <th>Ville départ</th>
                            <th>Date arrivée</th>
                            <th>Heure arrivée</th>
                            <th>Ville arrivée</th>
                            <th>Statut</th>
                            <th>Nombre de places</th>
                            <th>Durée</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($resultats)) {
                            foreach ($resultats as $voyage) {
                                echo "<tr>";
                                echo "<td><input type='checkbox' name='voyages_selectionnes[]' value='" . htmlspecialchars($voyage['Covoiturage_Id']) . "'></td>";
                                echo "<td><a href=\"detail_voyage.php?voyageId=" . htmlspecialchars($voyage['Covoiturage_Id']) . "\">Détail</a></td>";
                                echo "<td>" . htmlspecialchars($voyage['Date_depart']) . "</td>";
                                echo "<td>" . htmlspecialchars($voyage['Heure_depart']) . "</td>";
                                echo "<td>" . htmlspecialchars($voyage['Ville_depart']) . "</td>";
                                echo "<td>" . htmlspecialchars($voyage['Date_arrivee']) . "</td>";
                                echo "<td>" . htmlspecialchars($voyage['Heure_arrivee']) . "</td>";
                                echo "<td>" . htmlspecialchars($voyage['Ville_arrivee']) . "</td>";
                                echo "<td>" . htmlspecialchars($voyage['Statut']) . "</td>";
                                echo "<td>" . htmlspecialchars($voyage['Nb_place']) . "</td>";
                                echo "<td>" . htmlspecialchars($voyage['Duree']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'>Aucun voyage trouvé.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <br>
                <div class="btn_group">
                    <button class="action-btn" type="submit" name="action" value="valider">Valider</button>
                    <button class="action-btn" type="submit" name="action" value="annuler">Annuler</button>
                </div>
            </form>
        </div>
    </main>

    <?php include 'footer.php' ?>

</body>

</html>
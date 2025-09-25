<?php include 'imports.php' ?>
<?php include 'session.php' ?>

<?php

if (isset($_SESSION['id']) || isset($_SESSION['role'])) {

    $voyagesPassager = Voyage::loadVoyagesForPassager($_SESSION['id'], $pdo);
    $voyagesChauffeur = Voyage::loadVoyagesForChauffeur($_SESSION['id'], $pdo);

    $userIsPassager = false;
    $userIsChauffeur = false;
    if (str_contains($_SESSION['role'], Utilisateur::USER_ROLE_PASSAGER)) {
        $userIsPassager = true;
    }
    if (str_contains($_SESSION['role'], Utilisateur::USER_ROLE_CHAUFFEUR)) {
        $userIsChauffeur = true;
    }

}
?>
<?php
if (isset($_POST['action']) && isset($_POST['voyages_selectionnes'])) {
    // $action = $_POST['action'];
    // $voyages_selectionnes = $_POST['voyages_selectionnes'];

    // // Convertir le tableau en une chaîne pour la clause IN de la requête SQL
    // $placeholders = implode(',', array_fill(0, count($voyages_selectionnes), '?'));


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
            <form action="traitement.php" method="post">

                <?php if ($userIsChauffeur): ?>
                    <h1>Mes voyages (chauffeur)</h1>
                    <?php if (isset($voyagesChauffeur) && (!empty($voyagesChauffeur))): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>N° Voyage</th>
                                    <th>Ville départ</th>
                                    <th>Date/Heure de départ</th>
                                    <th>Ville arrivée</th>
                                    <th>Date/Heure arrivée</th>
                                    <th>Statut</th>
                                    <th>Durée</th>
                                    <th>Nombre de participants</th>
                                    <th>Voiture</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($voyagesChauffeur)) {
                                    foreach ($voyagesChauffeur as $voyage) {
                                        echo "<tr>";
                                        // echo "<td><input type='checkbox' name='voyages_selectionnes[]' value='" . htmlspecialchars($voyage['Covoiturage_Id']) . "'></td>";
                                        echo "<td><a href=\"edition_voyage.php?voyageId=" . htmlspecialchars($voyage['voyageId']) . "\">Détail</a></td>";
                                        echo "<td>" . htmlspecialchars($voyage['voyageId']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Date_depart']) . " - " . htmlspecialchars($voyage['Heure_depart']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Ville_depart']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Date_arrivee']) . " - " . htmlspecialchars($voyage['Heure_arrivee']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Ville_arrivee']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Statut']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Duree']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['nbParticipants']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['immatriculation']) . " (" . htmlspecialchars($voyage['marque']) . " - " . htmlspecialchars($voyage['modele']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='10'>Aucun voyage trouvé.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <div class="form-actions">
                            <a href="edition_voyage.php" class="btn btn-primary">Nouveau voyage</a>
                        </div>
                    <?php else: ?>
                        <h6>Aucun voyage trouvé !</h6>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($userIsPassager): ?>
                    <h1>Mes voyages (passager)</h1>
                    <?php if (isset($voyagesPassager) && (!empty($voyagesPassager))): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th></th>
                                    <!-- <th></th> -->
                                    <th>N° Voyage</th>
                                    <th>Ville départ</th>
                                    <th>Date/Heure de départ</th>
                                    <th>Ville arrivée</th>
                                    <th>Date/Heure arrivée</th>
                                    <th>Statut</th>
                                    <th>Durée</th>
                                    <th>Chauffeur</th>
                                    <th>Voiture</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($voyagesPassager)) {
                                    foreach ($voyagesPassager as $voyage) {
                                        echo "<tr>";
                                        // echo "<td><input type='checkbox' name='voyages_selectionnes[]' value='" . htmlspecialchars($voyage['Covoiturage_Id']) . "'></td>";
                                        echo "<td><a href=\"detail_voyage.php?voyageId=" . htmlspecialchars($voyage['voyageId']) . "\">Détail</a></td>";
                                        echo "<td>" . htmlspecialchars($voyage['voyageId']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Date_depart']) . " - " . htmlspecialchars($voyage['Heure_depart']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Ville_depart']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Date_arrivee']) . " - " . htmlspecialchars($voyage['Heure_arrivee']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Ville_arrivee']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Statut']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['Duree']) . "</td>";
                                        echo "<td>" . htmlspecialchars($voyage['nomChauffeur']) . " (" . htmlspecialchars($voyage['emailChauffeur']) . " - " . htmlspecialchars($voyage['telChauffeur']) . ")</td>";
                                        echo "<td>" . htmlspecialchars($voyage['immatriculation']) . " (" . htmlspecialchars($voyage['marque']) . " - " . htmlspecialchars($voyage['modele']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='10'>Aucun voyage trouvé.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <h6>Aucun voyage trouvé !</h6>
                    <?php endif; ?>
                <?php endif; ?>
                <!-- <br>
                <div class="btn_group">
                    <button class="action-btn" type="submit" name="action" value="valider">Valider</button>
                    <button class="action-btn" type="submit" name="action" value="annuler">Annuler</button>
                </div> -->
            </form>
        </div>
    </main>

    <?php include 'footer.php' ?>

</body>

</html>
<?php include 'imports.php' ?>
<?php include 'session.php' ?>

<?php

if (isset($_GET['userId'])) {
    try {

        // Requête pour récupérer tous les utilisateurs
        $sql = "SELECT Avis_Id, avis.Utilisateur_Id, Commentaire, avis.Note, avis.Statut, avis.Covoiturage_Id, covoiturage.Statut, Ville_arrivee, Ville_depart,  Date_arrivee , utilisateur.Nom, utilisateur.Prenom, utilisateur.Email, chauffeur.Nom nomChauffeur, chauffeur.Prenom prenomChauffeur, chauffeur.Email emailChauffeur  FROM avis JOIN utilisateur ON utilisateur.Utilisateur_Id = avis.Utilisateur_Id JOIN covoiturage ON avis.Covoiturage_Id = covoiturage.Covoiturage_Id JOIN voiture ON covoiturage.Voiture_Id = voiture.Voiture_Id JOIN utilisateur chauffeur ON voiture.Utilisateur_Id = chauffeur.Utilisateur_Id WHERE covoiturage.statut = 'Terminé' AND avis.statut = 'En cours de validation'" ; 
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Erreur de connexion : " . $e->getMessage();
    }
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
            <h1>Avis à valider</h1>
            <form action="traitement.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th>N° Voyage</th>
                            <th>Passager</th>
                            <th>Chauffeur</th>
                            <th>Information Voyage</th>
                            <th>Commentaire</th>
                            <th>Note</th>
                            <th>Statut</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($resultats)) {
                            foreach ($resultats as $avis) {
                                echo "<tr";
                                if ($avis['Note'] < MIN_NOTE_AVIS) {
                                    echo " class=\"avis_negatif\"";
                                }
                                echo ">";
                                echo "<td><input type='checkbox' name='voyages_selectionnes[]' value='" . htmlspecialchars($avis['Covoiturage_Id']) . "'></td>";
                                echo "<td><a href=\"detail_avis.php?avisId=" . htmlspecialchars($avis['Avis_Id']) . "\">Détail</a></td>";
                                echo "<td>" . htmlspecialchars($avis['Covoiturage_Id']) . "</td>";
                                echo "<td>" . htmlspecialchars($avis['Nom']) ." ".htmlspecialchars($avis['Prenom'])." (". htmlspecialchars($avis['Email']).")</td>";
                                echo "<td>" . htmlspecialchars($avis['nomChauffeur']) ." ".htmlspecialchars($avis['prenomChauffeur'])." (". htmlspecialchars($avis['emailChauffeur']).")</td>";
                                echo "<td>" . htmlspecialchars($avis['Ville_depart']) ." à ".htmlspecialchars(string:$avis['Ville_arrivee'])." le ".htmlspecialchars($avis['Date_arrivee'])."</td>";
                                echo "<td>" . htmlspecialchars($avis['Commentaire']) . "</td>";
                                echo "<td>" . htmlspecialchars($avis['Note']) . "</td>";
                                echo "<td>" . htmlspecialchars($avis['Statut']) . "</td>";
                               
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
                    <button class="action-btn" type="submit" name="action" value="refuser">Refuser</button>
                </div>
            </form>
        </div>
    </main>

    <?php include 'footer.php' ?>

</body>

</html>
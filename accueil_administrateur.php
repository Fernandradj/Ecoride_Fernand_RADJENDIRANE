<?php include 'imports.php' ?>
<?php include 'session.php' ?>

<?php

if (isset($_GET['userId'])) {
    try {

        // Requête pour récupérer tous les utilisateurs
        $sql = "SELECT utilisateur.Utilisateur_Id, Nom, Prenom, Email, Telephone, Note, GROUP_CONCAT(Libelle SEPARATOR ', ') AS roles FROM utilisateur JOin utilisateur_role ON utilisateur.Utilisateur_Id = utilisateur_role.Utilisateur_Id JOIN Role ON utilisateur_role.Role_Id = role.Role_Id WHERE statut = 'Actif' GROUP BY utilisateur.Utilisateur_Id";
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
        <div>graphique</div>
        <a href="creation_compte.php">Créer un nouvel employé</a>

        <div class="liste_voyages_container">
            <h1>Listes des utilisateurs</h1>
            <form action="traitement.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Note</th>
                            <th>Role(s)</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($resultats)) {
                            foreach ($resultats as $user) {
                                echo "<tr>";
                                echo "<td><input type='checkbox' name='voyages_selectionnes[]' value='" . htmlspecialchars($user['Utilisateur_Id']) . "'></td>";
                                echo "<td>" . htmlspecialchars($user['Nom']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['Prenom']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['Email']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['Telephone']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['Note']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['roles']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'>Aucun utilisateur trouvé.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <br>
                <div class="btn_group">
                    <button class="action-btn" type="submit" name="action" value="susprendre">Suspendre</button>
                </div>
            </form>
        </div>
    </main>

    <?php include 'footer.php' ?>

</body>

</html>
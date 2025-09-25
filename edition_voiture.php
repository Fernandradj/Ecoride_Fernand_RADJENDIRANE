<?php include 'imports.php' ?>
<?php include 'session.php' ?>

<?php

if (isset($_SESSION['id'])) {

    $user = new Utilisateur($_SESSION['id'], $pdo);
    $voitures = $user->loadVoitures($pdo);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (isset($_POST["valider"])) {
            $marque = $_POST['marque'];
            $modele = $_POST['modele'];
            $immatriculation = $_POST['immatriculation'];
            $couleur = $_POST['couleur'];
            $dateImmmatriculation = $_POST['dateImmmatriculation'];
            $nbPlace = $_POST['nbPlace'];
            $energie = $_POST['energie'];

            $result = null;
            $result = Voiture::enregistrerVoiture($immatriculation, $dateImmmatriculation, $marque, $modele, $couleur, $nbPlace, $energie, $user->getId(), $pdo);
            if ($result->getSucceeded()) {
                $successMsg = $result->getMessage();
                header("Refresh:2");
            } else {
                $errorMsg = $result->getMessage();
            }
        }
    }
}
?>

<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>
    <title>Créer un voyage</title>
</head>

<body>

    <?php include 'header.php' ?>


    <!-- main -->
    <main>
        <div class="container">

            <?php if (!empty($successMsg)): ?>
                <div class="success-msg"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMsg)): ?>
                <div class="error-msg"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <h1>Mes voitures</h1>
            <?php if (isset($voitures) && (!empty($voitures))): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Plaque d'immatriculation</th>
                            <th>Date de première immatriculation</th>
                            <th>Marque</th>
                            <th>Modèle</th>
                            <th>Couleur</th>
                            <th>Energie</th>
                            <th>Nombre de place(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($voitures)) {
                            foreach ($voitures as $voiture) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($voiture['Immatriculation']) . "</td>";
                                echo "<td>" . htmlspecialchars($voiture['Date_premiere_immatriculation']) . "</td>";
                                echo "<td>" . htmlspecialchars($voiture['Marque']) . "</td>";
                                echo "<td>" . htmlspecialchars($voiture['Modele']) . "</td>";
                                echo "<td>" . htmlspecialchars($voiture['Couleur']) . "</td>";
                                echo "<td>" . htmlspecialchars($voiture['Energie']) . "</td>";
                                echo "<td>" . htmlspecialchars($voiture['Nb_place']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'>Aucune voiture trouvé.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php else: ?>
                <h6>Aucun voyage trouvé !</h6>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
                <h1>Nouvelle voiture</h1>
                <div>
                    <label for="immatriculation">Plaque d'immatriculation :</label>
                    <input type="text" id="immatriculation" name="immatriculation" placeholder="AA-000-BB" required>
                </div>
                <div>
                    <label for="dateImmmatriculation">Date de première immatriculation :</label>
                    <input type="date" id="dateImmmatriculation" name="dateImmmatriculation" required>
                </div>
                <div>
                    <label for="marque">Marque :</label>
                    <input type="text" id="marque" name="marque" required>
                </div>
                <div>
                    <label for="modele">Modèle :</label>
                    <input type="text" id="modele" name="modele" required>
                </div>
                <div>
                    <label for="couleur">Couleur :</label>
                    <input type="text" id="couleur" name="couleur" required>
                </div>
                <div>
                    <label for="energie">Energie :</label>
                    <select id="energie" name="energie" required>
                        <option value="Electrique">Electrique</option>
                        <option value="Essence">Essence</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Hybride">Hybride</option>
                    </select>
                </div>
                <div>
                    <label for="nbPlace">Nombre de place(s) :</label>
                    <input type="number" min="1" id="nbPlace" name="nbPlace" required>
                </div>

                <button class="action-btn" type="submit" name="valider">Sauvegarder</button>
            </form>
        </div>

    </main>

    <?php include 'footer.php' ?>

</body>

</html>
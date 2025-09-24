<?php include 'imports.php' ?>
<?php include 'session.php' ?>
<?php

if (isset($_SESSION['id']) && isset($_SESSION['role'])) {

    $user = new Utilisateur($_SESSION['id'], $pdo);

    $userIsPassager = false;
    $userIsEmploye = false;
    if ($user->userIsPassager() || $user->userIsPassagerChauffeur()) {
        $userIsPassager = true;
    } else if ($user->userIsEmploye()) {
        $userIsEmploye = true;
    }

    $editMode = false;

    if (isset($_GET['voyageId'])) {
        $avis = new Avis($user->getId(), $_GET['voyageId'], $pdo);
        $voyage = $avis->getVoyage();

        echo $avis->getComments();
        echo $avis->getNote();

        if ($userIsPassager && ($avis->isAvisOpen())) {
            $editMode = true;
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            if (isset($_POST["soumettre"])) {
                $note = $_POST['note'];
                $commentaire = $_POST['commentaire'];
                $result = $avis->soumettreAvis($note, $commentaire, $pdo);
                if ($result->getSucceeded()) {
                    $succesdMsg = $result->getMessage();
                } else {
                    $errorMsg = $result->getMessage();
                }
            } else if (isset($_POST["valider"])) {
                $result = $avis->validerAvis($user->getId(), $pdo);
                if ($result->getSucceeded()) {
                    $succesdMsg = $result->getMessage();
                } else {
                    $errorMsg = $result->getMessage();
                }
            } else if (isset($_POST["rejeter"])) {
                $result = $avis->rejeterAvis($user->getId(), $pdo);
                if ($result->getSucceeded()) {
                    $succesdMsg = $result->getMessage();
                } else {
                    $errorMsg = $result->getMessage();
                }
            }
        }
    }
}
?>
<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css">
    <title>Avis</title>
</head>

<body>

    <?php include 'header.php' ?>

    <!-- main -->
    <main>

        <div class="profile-container">

            <?php if (!empty($succesdMsg)): ?>
                <div class="success-msg"><?php echo $succesdMsg; ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMsg)): ?>
                <div class="error-msg"><?php echo $errorMsg; ?></div>
            <?php endif; ?>


            <div class="container">
                <div class="main-content">
                    <div class="left-panel">
                        <div class="header">
                            <h1><?php echo $voyage->getPlaceDescription(); ?></h1>
                            <h2>Détails du voyage</h2>
                        </div>

                        <div class="detail-item">
                            <span class="label">Départ :</span>
                            <span
                                class="value"><?php echo $voyage->getDepartureDate() . " à " . $voyage->getDepartureTime(); ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">Arrivée :</span>
                            <span
                                class="value"><?php echo $voyage->getArrivalDate() . " à " . $voyage->getArrivalTime(); ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">Durée :</span>
                            <span class="value"><?php echo $voyage->getDuree() . ' min'; ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">Prix :</span>
                            <span class="value price-value"><?php echo $voyage->getPrice(); ?> €</span>
                        </div>
                    </div>

                    <h2>Donnez-nous votre avis</h2>

                    <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
                        <div class="avis_note_section">
                            <label for="note">Note :</label>
                            <?php if ($editMode): ?>
                                <!-- Inspired by: https://codepen.io/jamesbarnett/pen/vlpkh -->
                                <div class="rating">
                                    <input type="radio" id="star5" name="note" value="5" />
                                    <label class="star" for="star5" title="Awesome" aria-hidden="true"></label>
                                    <input type="radio" id="star4" name="note" value="4" />
                                    <label class="star" for="star4" title="Great" aria-hidden="true"></label>
                                    <input type="radio" id="star3" name="note" value="3" />
                                    <label class="star" for="star3" title="Very good" aria-hidden="true"></label>
                                    <input type="radio" id="star2" name="note" value="2" />
                                    <label class="star" for="star2" title="Good" aria-hidden="true"></label>
                                    <input type="radio" id="star1" name="note" value="1" />
                                    <label class="star" for="star1" title="Bad" aria-hidden="true"></label>
                                </div>
                            <?php endif; ?>
                            <?php
                            if (!$editMode) {
                                echo '<img class="avis_star" src="images/star_' . $avis->getNote() . '.png" alt="' . $avis->getNote() . ' sur 5">';
                            }
                            ?>
                        </div>

                        <br>
                        <label for="commentaire">Commentaire :</label>
                        <textarea name="commentaire" value="<?php echo $avis->getComments(); ?>" id="commentaire"
                            rows="4" cols="50" <?php if (!$editMode) {
                                echo "disabled";
                            } ?>>
                            <?php echo $avis->getComments(); ?>
                            </textarea>
                        <br>
                        <?php if ($editMode): ?>
                            <!-- <button type="submit" name="soumettre">Soumettre</button> -->
                        <?php endif; ?>

                        <?php if ($userIsEmploye && (!$editMode)): ?>
                            <form action="traitement_trajet.php" method="post">
                                <button type="submit" name="valider">Valider</button>
                                <button type="submit" name="rejeter">Rejeter</button>
                            </form>
                        <?php endif; ?>

                    </form>

                </div>
            </div>
        </div>

    </main>

    <?php include 'footer.php' ?>

</body>

</html>
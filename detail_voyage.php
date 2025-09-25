<?php include 'imports.php' ?>
<?php include 'session.php' ?>

<?php
if (isset($_GET['voyageId'])) {

    $showMessageVisiteur = false;
    $successMsg = "";
    $errorMsg = "";

    $voyage = new Voyage($_GET['voyageId'], $pdo);
    $participants = $voyage->getParticipations($pdo);
    $participantsIds = [];
    foreach ($participants as $participant) {
        array_push($participantsIds, $participant['Utilisateur_Id']);
    }

    $userIsDriver = false;
    $displayParticiper = false;
    $displayAnnulerPassager = false;
    $displayValider = false;

    if (str_contains($_SESSION["role"], Utilisateur::USER_ROLE_CHAUFFEUR) && ($voyage->getDriver()->getId() == $_SESSION["id"])) {
        $userIsDriver = true;
    }

    if (!isset($_SESSION["id"])) {
        $showMessageVisiteur = true;
    } else if ($voyage->isVoyageOuvert()) {
        if ((!$userIsDriver) && str_contains($_SESSION["role"], Utilisateur::USER_ROLE_PASSAGER)) {
            if (in_array($_SESSION["id"], $participantsIds)) {
                $displayAnnulerPassager = true;
            } else if ($voyage->getNbPlace() >= 1) {
                $displayParticiper = true;
            }
        }
    } else if ($voyage->isVoyageTermine()) {
        if (str_contains($_SESSION["role"], Utilisateur::USER_ROLE_PASSAGER) && in_array($_SESSION["id"], $participantsIds)) {
            $displayValider = true;
        }
    }

}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION["id"])) {
        if (isset($_POST["participer"])) {
            $result = $voyage->participer($_SESSION["id"], $pdo);
            if ($result->getSucceeded()) {
                $successMsg = $result->getMessage();
                header("Refresh:2");
            } else {
                $errorMsg = $result->getMessage();
            }
        } else if (isset($_POST["annulerPassager"])) {
            $result = $voyage->annulerPassager($_SESSION["id"], $pdo);
            if ($result->getSucceeded()) {
                $successMsg = $result->getMessage();
                header("Refresh:2");
            } else {
                $errorMsg = $result->getMessage();
            }
        } else if (isset($_POST["valider"])) {
            header("location: detail_avis.php?voyageId=" . $voyage->getId());
        }
    }
}

?>

<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>
    <title>Détail du voyage</title>
</head>

<body>

    <?php include 'header.php' ?>

    <!-- main -->
    <main>
        <a href=javascript:history.go(-1)>Retour</a>

        <div class="container">
            <div class="main-content">
                <?php if (!empty($successMsg)): ?>
                    <div class="success-msg"><?php echo $successMsg; ?></div>
                <?php endif; ?>
                <?php if (!empty($errorMsg)): ?>
                    <div class="error-msg"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                <?php if (!empty($voyage->isVoyageAnnule())): ?>
                    <div class="warning-msg">Ce voyage a été annulé.</div>
                <?php endif; ?>
                <?php if (!empty($voyage->isVoyageEnCours())): ?>
                    <div class="warning-msg">Ce voyage est en cours.</div>
                <?php endif; ?>
                <?php if (!empty($voyage->isVoyageTermine())): ?>
                    <div class="warning-msg">Ce voyage est terminé.</div>
                <?php endif; ?>

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
                        <span class="label">Nombre de place(s) disponible(s) :</span>
                        <span class="value"><?php echo $voyage->getNbPlace(); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Animal accepté :</span>
                        <span class="value"><?php echo $voyage->getDriver()->getPreferenceAnimalString(); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Fumeur accepté :</span>
                        <span class="value"><?php echo $voyage->getDriver()->getPreferenceFumeurString(); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Péférences du chauffeur :</span>
                        <span class="value"><?php echo $voyage->getDriver()->getPreference(); ?></span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Prix par personne :</span>
                        <span class="value price-value"><?php echo $voyage->getPrice(); ?> €</span>
                    </div>

                    <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
                        <div class="btn_group">

                            <?php if ($displayParticiper): ?>
                                <button class="action-btn" name="participer" type="submit">Participer</button>
                            <?php endif; ?>

                            <?php if ($displayAnnulerPassager): ?>
                                <button class="action-btn" name="annulerPassager" type="submit">Annuler (Passager)</button>
                            <?php endif; ?>

                            <?php if ($displayValider): ?>
                                <button class="action-btn" name="valider" type="submit">Valider</button>
                            <?php endif; ?>

                            <?php if ($showMessageVisiteur): ?>
                                <p><a href="connexion.php">Connectez-vous</a> pour participer !</p>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="right-panel">
                    <?php if (!$userIsDriver): ?>
                        <div class="driver-card">
                            <h3>Chauffeur</h3>
                            <img src="<?php echo "[PHOTO HERE]" ?>" alt="Driver's photo" class="driver-photo">
                            <p><strong><?php echo $voyage->getDriver()->getFullName(); ?></strong></p>
                            <p class="review-stars">[review starts here]</p>
                            <p>Tél: <?php echo $voyage->getDriver()->getTelephone(); ?></p>
                            <p>E-mail: <?php echo $voyage->getDriver()->getEmail(); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="car-card">
                        <h3>Voiture</h3>
                        <ul class="car-detail-list">
                            <li><strong>Marque:</strong> <?php echo $voyage->getVoiture()->getCarMake(); ?></li>
                            <li><strong>Modèle:</strong> <?php echo $voyage->getVoiture()->getCarModel(); ?></li>
                            <li><strong>Année:</strong> <?php echo $voyage->getVoiture()->getCarYear(); ?></li>
                            <li><strong>Plaque:</strong> <?php echo $voyage->getVoiture()->getCarPlate(); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include 'footer.php' ?>

</body>

</html>
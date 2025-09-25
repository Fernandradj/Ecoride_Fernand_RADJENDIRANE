<?php include 'imports.php' ?>
<?php include 'session.php' ?>

<?php

if (isset($_SESSION['id'])) {

    function calculerDureeEnMinutes($date_depart, $heure_depart, $date_arrivee, $heure_arrivee)
    {
        try {
            $datetime_depart = new DateTimeImmutable($date_depart . ' ' . $heure_depart);
            $datetime_arrivee = new DateTimeImmutable($date_arrivee . ' ' . $heure_arrivee);
        } catch (Exception $e) {
            return false;
        }
        $interval = $datetime_depart->diff($datetime_arrivee);
        return ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
    }

    $user = new Utilisateur($_SESSION['id'], $pdo);
    $voitures = $user->loadVoitures($pdo);

    $date_depart = "";
    $heure_depart = "";
    $adresse_depart = "";
    $ville_depart = "";
    $date_arrivee = "";
    $heure_arrivee = "";
    $adresse_arrivee = "";
    $ville_arrivee = "";
    $nb_place = null;
    $prix_personne = null;
    $id_voiture = null;

    $editMode = false;
    $readOnly = false;
    $disabled = "";
    $voyageId = null;
    $voyage = null;
    if (isset($_GET["voyageId"])) {
        $editMode = true;
        $voyageId = $_GET['voyageId'];
        $voyage = new Voyage($_GET["voyageId"], $pdo);

        if ($voyage->isVoyageAnnule() || $voyage->isVoyageEnCours() || $voyage->isVoyageTermine()) {
            $readOnly = true;
            $disabled = "disabled";
        }

        $date_depart = $voyage->getDepartureDate();
        $heure_depart = $voyage->getDepartureTime();
        $adresse_depart = $voyage->getDepartureAddress();
        $ville_depart = $voyage->getDeparturePlace();
        $date_arrivee = $voyage->getArrivalDate();
        $heure_arrivee = $voyage->getArrivalTime();
        $adresse_arrivee = $voyage->getArrivalAddress();
        $ville_arrivee = $voyage->getArrivalPlace();
        $nb_place = $voyage->getNbPlace();
        $prix_personne = $voyage->getPrice();
        $id_voiture = $voyage->getVoiture()->getId();
    }

    // Traitement du formulaire
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (isset($_POST["valider"])) {

            $date_depart = $_POST['date_depart'];
            $heure_depart = $_POST['heure_depart'];
            $adresse_depart = $_POST['adresse_depart'];
            $ville_depart = $_POST['ville_depart'];
            $date_arrivee = $_POST['date_arrivee'];
            $heure_arrivee = $_POST['heure_arrivee'];
            $adresse_arrivee = $_POST['adresse_arrivee'];
            $ville_arrivee = $_POST['ville_arrivee'];
            $nb_place = $_POST['nb_place'];
            $prix_personne = $_POST['prix_personne'];
            $id_voiture = $_POST['id_voiture'];
            $duree = calculerDureeEnMinutes($date_depart, $heure_depart, $date_arrivee, $heure_arrivee);

            $result = null;
            if ($editMode) {
                $result = Voyage::enregistrerVoyage($_GET["voyageId"], $date_depart, $heure_depart, $adresse_depart, $ville_depart, $date_arrivee, $heure_arrivee, $adresse_arrivee, $ville_arrivee, $nb_place, $prix_personne, $id_voiture, $duree, $pdo);
            } else {
                $result = Voyage::creerVoyage($date_depart, $heure_depart, $adresse_depart, $ville_depart, $date_arrivee, $heure_arrivee, $adresse_arrivee, $ville_arrivee, $nb_place, $prix_personne, $id_voiture, $duree, $pdo);
            }
            if ($result->getSucceeded()) {
                $successMsg = $result->getMessage();
                header("Refresh:2, url=accueil_utilisateur.php");
            } else {
                $errorMsg = $result->getMessage();
            }
        } else if (isset($_POST["annuler"])) {
            $result = $voyage->annulerChauffeur($pdo);
            if ($result->getSucceeded()) {
                $successMsg = $result->getMessage();
                header("Refresh:2, url=accueil_utilisateur.php");
            } else {
                $errorMsg = $result->getMessage();
            }
        } else if (isset($_POST["demarrer"])) {
            $result = $voyage->demarrer($pdo);
            if ($result->getSucceeded()) {
                $successMsg = $result->getMessage();
                header("Refresh:2, url=accueil_utilisateur.php");
            } else {
                $errorMsg = $result->getMessage();
            }
        } else if (isset($_POST["arreter"])) {
            $result = $voyage->arreter($pdo);
            if ($result->getSucceeded()) {
                $successMsg = $result->getMessage();
                header("Refresh:2, url=accueil_utilisateur.php");
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

            <?php if ($editMode && (!empty($voyage->isVoyageAnnule()))): ?>
                <div class="warning-msg">Ce voyage a été annulé.</div>
            <?php endif; ?>
            <?php if ($editMode && (!empty($voyage->isVoyageEnCours()))): ?>
                <div class="warning-msg">Ce voyage est en cours.</div>
            <?php endif; ?>
            <?php if ($editMode && (!empty($voyage->isVoyageTermine()))): ?>
                <div class="warning-msg">Ce voyage est terminé.</div>
            <?php endif; ?>

            <?php if ($editMode): ?>
                <h1>Détail du voyage n°<?php echo $voyageId; ?></h1>
            <?php else: ?>
                <h1>Créer un nouveau voyage</h1>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
                <h2>Informations de départ</h2>
                <div>
                    <label for="date_depart">Date de départ :</label>
                    <input type="date" id="date_depart" name="date_depart" value="<?php echo $date_depart ?>" <?php echo $disabled ?> required>
                </div>
                <div>
                    <label for="heure_depart">Heure de départ :</label>
                    <input type="time" id="heure_depart" name="heure_depart" value="<?php echo $heure_depart ?>"
                         <?php echo $disabled ?>  required>
                </div>
                <div>
                    <label for="adresse_depart">Adresse de départ :</label>
                    <input type="text" id="adresse_depart" name="adresse_depart" value="<?php echo $adresse_depart ?>"
                         <?php echo $disabled ?>  required>
                </div>
                <div>
                    <label for="ville_depart">Ville de départ :</label>
                    <input type="text" id="ville_depart" name="ville_depart" value="<?php echo $ville_depart ?>"
                         <?php echo $disabled ?>  required>
                </div>


                <h2>Informations d'arrivée</h2>
                <div>
                    <label for="date_arrivee">Date d'arrivée :</label>
                    <input type="date" id="date_arrivee" name="date_arrivee" value="<?php echo $date_arrivee ?>"
                         <?php echo $disabled ?>  required>
                </div>
                <div>
                    <label for="heure_arrivee">Heure d'arrivée :</label>
                    <input type="time" id="heure_arrivee" name="heure_arrivee" value="<?php echo $heure_arrivee ?>"
                         <?php echo $disabled ?>  required>
                </div>
                <div>
                    <label for="adresse_arrivee">Adresse d'arrivée :</label>
                    <input type="text" id="adresse_arrivee" name="adresse_arrivee"
                        value="<?php echo $adresse_arrivee ?>"  <?php echo $disabled ?>  required>
                </div>
                <div>
                    <label for="ville_arrivee">Ville d'arrivée :</label>
                    <input type="text" id="ville_arrivee" name="ville_arrivee" value="<?php echo $ville_arrivee ?>"
                         <?php echo $disabled ?>  required>
                </div>


                <h2>Détails du voyage</h2>
                <div>
                    <label for="nb_place">Nombre de places :</label>
                    <input type="number" id="nb_place" name="nb_place" min="1" value="<?php echo $nb_place ?>"  <?php echo $disabled ?>  required>
                </div>
                <div>
                    <label for="prix_personne">Prix par personne :</label>
                    <input type="number" id="prix_personne" name="prix_personne" step="0.01" min="0"
                        value="<?php echo $prix_personne ?>"  <?php echo $disabled ?>  required>
                </div>
                <div>
                    <label for="id_voiture">Voiture :</label>
                    <select id="id_voiture" name="id_voiture" value="<?php echo $id_voiture ?>"  <?php echo $disabled ?>  required>
                        <?php foreach ($voitures as $voiture): ?>
                            <option value="<?php echo $voiture['Voiture_Id']; ?>">
                                <?php echo htmlspecialchars(string: $voiture['Immatriculation'] . ' - ' . $voiture['Marque'] . ' ' . $voiture['Modele']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ((!$editMode) || ($editMode && ($voyage->getStatus() == Voyage::VOYAGE_STATUT_OUVERT))): ?>
                        <a class="action-btn" href="edition_voiture.php">Nouvelle voiture</a>
                    <?php endif; ?>
                </div>

                <?php if ((!$editMode) || ($editMode && ($voyage->getStatus() == Voyage::VOYAGE_STATUT_OUVERT))): ?>
                    <button class="action-btn" type="submit" name="valider">Sauvegarder</button>
                <?php endif; ?>

                <?php if ($editMode && ($voyage->getStatus() == Voyage::VOYAGE_STATUT_OUVERT)): ?>
                    <button class="action-btn" type="submit" name="annuler">Annuler</button>
                <?php endif; ?>

                <?php if ($editMode && ($voyage->getStatus() == Voyage::VOYAGE_STATUT_OUVERT)): ?>
                    <button class="action-btn" type="submit" name="demarrer">Démarrer</button>
                <?php endif; ?>

                <?php if ($editMode && ($voyage->getStatus() == Voyage::VOYAGE_STATUT_EN_COURS)): ?>
                    <button class="action-btn" type="submit" name="arreter">Arrêter</button>
                <?php endif; ?>
            </form>
        </div>

    </main>

    <?php include 'footer.php' ?>

</body>

</html>
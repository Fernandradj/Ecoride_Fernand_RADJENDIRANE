<?php include 'imports.php' ?>
<?php include 'session.php' ?>

<?php

function calculerDureeEnMinutes($date_depart, $heure_depart, $date_arrivee, $heure_arrivee)
{
    // Crée des objets DateTimeImmutable pour une meilleure gestion des dates et heures
    try {
        $datetime_depart = new DateTimeImmutable($date_depart . ' ' . $heure_depart);
        $datetime_arrivee = new DateTimeImmutable($date_arrivee . ' ' . $heure_arrivee);
    } catch (Exception $e) {
        // Retourne false si les formats de date ou d'heure sont invalides
        return false;
    }

    // Calcule la différence entre les deux dates
    $interval = $datetime_depart->diff($datetime_arrivee);

    // Retourne la durée totale en minutes
    // $interval->days est le nombre de jours entiers
    // $interval->h est le nombre d'heures restantes
    // $interval->i est le nombre de minutes restantes
    return ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
}

$userId = 1;
// Récupération des voitures pour la liste déroulante
$sql_voitures = "SELECT Voiture_Id, Marque, Modele, Immatriculation FROM Voiture WHERE Utilisateur_Id = " . $userId;
$stmt_voitures = $pdo->prepare($sql_voitures);
$stmt_voitures->execute();
$voitures = $stmt_voitures->fetchAll(PDO::FETCH_ASSOC);

$message = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Insertion des données dans la base de données
    $sql_insert = "INSERT INTO covoiturage (Date_depart, Heure_depart, Adresse_depart, Ville_depart, Date_arrivee, Heure_arrivee, Adresse_arrivee, Ville_arrivee, Nb_place, Prix_personne, Voiture_Id, Duree) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);

    try {
        $stmt_insert->execute([
            $date_depart,
            $heure_depart,
            $adresse_depart,
            $ville_depart,
            $date_arrivee,
            $heure_arrivee,
            $adresse_arrivee,
            $ville_arrivee,
            $nb_place,
            $prix_personne,
            $id_voiture,
            $duree
        ]);
        $message = "Voyage enregistré avec succès ! ✅";
    } catch (PDOException $e) {
        $message = "Erreur lors de l'enregistrement : " . $e->getMessage() . " ❌";
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
            <h1>🚗 Enregistrer un nouveau voyage</h1>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'succès') !== false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h2>Informations de départ</h2>
                <div>
                    <label for="date_depart">Date de départ :</label>
                    <input type="date" id="date_depart" name="date_depart" required>
                </div>
                <div>
                    <label for="heure_depart">Heure de départ :</label>
                    <input type="time" id="heure_depart" name="heure_depart" required>
                </div>
                <div>
                    <label for="adresse_depart">Adresse de départ :</label>
                    <input type="text" id="adresse_depart" name="adresse_depart" required>
                </div>
                <div>
                    <label for="ville_depart">Ville de départ :</label>
                    <input type="text" id="ville_depart" name="ville_depart" required>
                </div>

                
                <h2>Informations d'arrivée</h2>
                <div>
                    <label for="date_arrivee">Date d'arrivée :</label>
                    <input type="date" id="date_arrivee" name="date_arrivee" required>
                </div>
                <div>
                    <label for="heure_arrivee">Heure d'arrivée :</label>
                    <input type="time" id="heure_arrivee" name="heure_arrivee" required>
                </div>
                <div>
                    <label for="adresse_arrivee">Adresse d'arrivée :</label>
                    <input type="text" id="adresse_arrivee" name="adresse_arrivee" required>
                </div>
                <div>
                    <label for="ville_arrivee">Ville d'arrivée :</label>
                    <input type="text" id="ville_arrivee" name="ville_arrivee" required>
                </div>

                
                <h2>Détails du voyage</h2>
                <div>
                    <label for="nb_place">Nombre de places :</label>
                    <input type="number" id="nb_place" name="nb_place" min="1" required>
                </div>
                <div>
                    <label for="prix_personne">Prix par personne :</label>
                    <input type="number" id="prix_personne" name="prix_personne" step="0.01" min="0" required>
                </div>
                <div>
                    <label for="id_voiture">Voiture :</label>
                    <select id="id_voiture" name="id_voiture" required>
                        <?php foreach ($voitures as $voiture): ?>
                            <option value="<?php echo $voiture['Voiture_Id']; ?>">
                                <?php echo htmlspecialchars(string: $voiture['Immatriculation'] . ' - ' . $voiture['Marque'] . ' ' . $voiture['Modele']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">Valider</button>
            </form>
        </div>

    </main>

    <?php include 'footer.php' ?>

</body>

</html>
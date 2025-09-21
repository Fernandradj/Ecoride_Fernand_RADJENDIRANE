<?php

class Voyage
{

    public const RESULT_PARTICPATION_SAVED = "Votre participation a bien été enregistré.";
    public const RESULT_PARTICPATION_CANCELLED = "Votre participation a bien été annulé.";
    public const RESULT_CREDIT_INSUFFISANT = "Votre crédit n'est pas suffisant pour particper à ce voyage.";
    public const RESULT_FAIL = "Une erreur s'est produite lors de l'enregistrement. Veuilllez réessayer ultérieurement.";

    public const RESULT_VOYAGE_CANCELLED = "Votre voyage a bien été annulé.";

    public const RESULT_VOYAGE_DEMARRE = "Le statut de votre voyage a bien été mis à jour. Bon voyage !";
    
    public const RESULT_VOYAGE_ARRETE = "Le statut de votre voyage a bien été mis à jour.";

    public const VOYAGE_ANNULE = "Voyage Annulé";

    public const VOYAGE_TERMINE = "Voyage Terminé";

    public const VOYAGE_STATUT_OUVERT = 'Ouvert';
    public const VOYAGE_STATUT_TERMINE = 'Terminé';
    public const VOYAGE_STATUT_EN_COURS = 'En cours';
    public const VOYAGE_STATUT_ANNULE = 'Annulé';

    public const PARTICIPATION_STATUT_PAYE = 'Payé';
    public const PARTICIPATION_STATUT_ANNULE = 'Annulé';

    private $pdo;

    private int $id;
    private string $placeDescription;
    private int $nbPlace;
    private $departureDate;
    private $departureTime;
    private $arrivalDate;
    private $arrivalTime;
    private int $duree;
    private float $price;
    private string $status;

    private Utilisateur $driver;
    private Voiture $voiture;


    public function __construct(int $id, PDO $pdo)
    {
        $this->id = $id;
        $this->pdo = $pdo;

        // Get voyage
        $sql = 'SELECT Covoiturage_id, Date_depart, Heure_depart, Adresse_depart, Ville_depart, Date_arrivee, Heure_arrivee, Adresse_arrivee, Ville_arrivee, Statut, Nb_place, Prix_personne, Duree,Voiture_Id FROM covoiturage WHERE Covoiturage_id = ' . $id;
        // $sql = 'SELECT Covoiturage_id, Date_depart, Heure_depart, Adresse_depart, Ville_depart, Date_arrivee, Heure_arrivee, Adresse_arrivee, Ville_arrivee, covoiturage.Statut, covoiturage.Nb_place, Prix_personne, Duree, covoiturage.Voiture_id as Voiture_Id, voiture.Ecologique, voiture.Marque, voiture.Modele, voiture.Energie, voiture.Immatriculation, YEAR(voiture.Date_premiere_immatriculation) as anneeImmat, utilisateur.Utilisateur_id as driverId, utilisateur.Nom, utilisateur.Prenom, utilisateur.Note, utilisateur.Telephone, utilisateur.Email, utilisateur.Photo, utilisateur.Animal_accepte, utilisateur.Fumeur_accepte, utilisateur.Autre_preference FROM covoiturage JOIN voiture ON covoiturage.Voiture_id = voiture.Voiture_id JOIN utilisateur ON voiture.Utilisateur_id = utilisateur.Utilisateur_id WHERE Covoiturage_id = ' . $id;
        // echo $sql;

        foreach ($pdo->query($sql, PDO::FETCH_ASSOC) as $row) {
            $this->placeDescription = $row['Ville_depart'] . ' à ' . $row['Ville_arrivee'];
            $this->departureDate = $row['Date_depart'];
            $this->departureTime = $row['Heure_depart'];
            $this->arrivalDate = $row['Date_arrivee'];
            $this->arrivalTime = $row['Heure_arrivee'];
            $this->duree = $row['Duree'];
            $this->price = $row['Prix_personne'];
            $this->nbPlace = $row['Nb_place'];
            $this->status = $row["Statut"];

            $this->voiture = new Voiture($row['Voiture_Id'], $pdo);
            $this->driver = $this->voiture->getDriver();
        }

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlaceDescription(): string
    {
        return $this->placeDescription;
    }

    public function getDepartureDate(): string
    {
        return $this->departureDate;
    }

    public function getArrivalDate(): string
    {
        return $this->arrivalDate;
    }

    public function getDepartureTime(): string
    {
        return $this->departureTime;
    }

    public function getArrivalTime(): string
    {
        return $this->arrivalTime;
    }

    public function getDuree(): int
    {
        return $this->duree;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isVoyageOuvert(): bool {
        return $this->getStatus() === Voyage::VOYAGE_STATUT_OUVERT;
    }
    public function isVoyageAnnule(): bool {
        return $this->getStatus() === Voyage::VOYAGE_STATUT_ANNULE;
    }
    public function isVoyageEnCours(): bool {
        return $this->getStatus() === Voyage::VOYAGE_STATUT_EN_COURS;
    }
    public function isVoyageTermine(): bool {
        return $this->getStatus() === Voyage::VOYAGE_STATUT_TERMINE;
    }

    public function getNbPlace(): int
    {
        return $this->nbPlace;
    }

    public function getVoiture(): Voiture
    {
        return $this->voiture;
    }

    public function getDriver(): Utilisateur
    {
        return $this->driver;
    }

    function getParticipantsIds($pdo): array
    {
        $participants = $this->getParticipations();
        $participantsIds = [];
        if ($participants && (count($participants) > 0)) {
            foreach ($participants as $participant) {
                array_push($participantsIds, $participant['Utilisateur_Id']);
            }
        }
        return $participantsIds;
    }

    function getParticipations($pdo): array
    {
        $sql = "SELECT Utilisateur_Id, Covoiturage_Id, Credit FROM participation WHERE Covoiturage_Id = " . $this->getId() . " AND Statut = '" . Voyage::PARTICIPATION_STATUT_PAYE . "'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $participants = $stmt->fetchAll();
        return $participants;
    }

    public function checkCanParticipate(float $credit): bool
    {
        return ($credit >= $this->price);
    }

    public function participer(int $userId, PDO $pdo): Result
    {
        $passager = new Utilisateur($userId, $pdo);
        $result = "";

        if ($this->checkCanParticipate($passager->getCredit())) {
            try {
                $sql = "INSERT INTO participation (Utilisateur_Id, Covoiturage_Id, Statut, Credit) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$passager->getId(), $this->getId(), Voyage::PARTICIPATION_STATUT_PAYE, $this->price]);

                if ($stmt) {
                    if ($passager->payerVoyage($this->getPrice(), $pdo)) {
                        return new Result(true, Voyage::RESULT_PARTICPATION_SAVED);
                    }
                }

            } catch (\Throwable $th) {
                return new Result(false, Voyage::RESULT_FAIL);
            }
        } else {
            return new Result(false, Voyage::RESULT_CREDIT_INSUFFISANT);
        }
        return new Result(false, Voyage::RESULT_FAIL);
    }

    public function annulerPassager(int $userId, PDO $pdo): Result
    {
        $passager = new Utilisateur($userId, $pdo);

        try {
            $sql = "UPDATE participation SET Credit = ?, Statut = ? WHERE Utilisateur_Id = ? AND Covoiturage_Id = ? AND Statut = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$this->price, Voyage::PARTICIPATION_STATUT_ANNULE, $passager->getId(), $this->getId(), Voyage::PARTICIPATION_STATUT_PAYE]);

            if ($stmt) {
                if ($passager->rembourserVoyage($this->getPrice(), $pdo)) {
                    return new Result(true, Voyage::RESULT_PARTICPATION_CANCELLED);
                }
            }
        } catch (\Throwable $th) {
            return new Result(false, Voyage::RESULT_FAIL);
        }
        return new Result(false, Voyage::RESULT_FAIL);
    }

    public function annulerChauffeur(PDO $pdo): Result
    {

        try {
            $sql = "UPDATE covoiturage SET Statut = ? WHERE Covoiturage_Id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([Voyage::VOYAGE_STATUT_ANNULE, $this->getId()]);

            if ($stmt) {
                $participants = $this->getParticipations($pdo);
                if (!empty($participants)) {
                    foreach ($participants as $participant) {
                        $passager = new Utilisateur($participant['Utilisateur_Id'], $pdo);
                        $passager->rembourserVoyage($participant['Credit'], $pdo);
                        $passager->notifier(Voyage::VOYAGE_ANNULE, $this);
                    }
                }

                $sql = "UPDATE participation SET Statut = ? WHERE Covoiturage_Id = ? AND Statut = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([Voyage::PARTICIPATION_STATUT_ANNULE, $this->getId(), Voyage::PARTICIPATION_STATUT_PAYE]);
                if ($stmt) {
                    return new Result(true, Voyage::RESULT_VOYAGE_CANCELLED);
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return new Result(false, Voyage::RESULT_FAIL);
        }
        return new Result(false, Voyage::RESULT_FAIL);
    }

    public function demarrer(PDO $pdo) {
        try {
            $sql = "UPDATE covoiturage SET Statut = ? WHERE Covoiturage_Id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([Voyage::VOYAGE_STATUT_EN_COURS, $this->getId()]);
            return new Result(true, Voyage::RESULT_VOYAGE_DEMARRE);
        } catch (Exception $e) {
            echo $e->getMessage();
            return new Result(false, Voyage::RESULT_FAIL);
        }
    }
    
    public function arreter(PDO $pdo) {
        try {
            $sql = "UPDATE covoiturage SET Statut = ? WHERE Covoiturage_Id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([Voyage::VOYAGE_STATUT_TERMINE, $this->getId()]);

            if ($stmt) {
                $participants = $this->getParticipations($pdo);
                if (!empty($participants)) {
                    foreach ($participants as $participant) {
                        $passager = new Utilisateur($participant['Utilisateur_Id'], $pdo);
                        $passager->notifier(Voyage::VOYAGE_TERMINE, $this);
                    }
                }
            }

            return new Result(true, Voyage::RESULT_VOYAGE_ARRETE);
        } catch (Exception $e) {
            echo $e->getMessage();
            return new Result(false, Voyage::RESULT_FAIL);
        }
    }

}

?>
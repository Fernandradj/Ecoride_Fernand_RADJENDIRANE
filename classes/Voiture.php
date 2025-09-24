<?php

class Voiture
{
    public const ENERGIE_ELECTRIQUE = "Electrique";

    private int $id;
    private string $energy;

    private bool $ecologic;
    private int $nbPlace;
    private string $carMake;
    private string $carModel;
    private int $carYear;
    private string $carPlate;

    private Utilisateur $driver;

    function __construct(int $id, PDO $pdo)
    {
        $this->id = $id;
        $sql = 'SELECT Voiture_Id, Ecologique, Marque, Modele, Energie, Immatriculation, YEAR(Date_premiere_immatriculation) as anneeImmat, Utilisateur_Id FROM voiture WHERE Voiture_Id  = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $voiture = $stmt->fetch();
        if ($voiture) {
            $this->energy = $voiture['Energie'];
            $this->ecologic = $voiture['Ecologique'];
            $this->carMake = $voiture['Marque'];
            $this->carModel = $voiture['Modele'];
            $this->carYear = $voiture['anneeImmat'];
            $this->carPlate = $voiture['Immatriculation'];
            $this->driver = new Utilisateur($voiture['Utilisateur_Id'], $pdo);
        }
    }

    public static function enregistrerVoiture(string $immatriculation, string $dateImmmatriculation, string $marque, string $modele, string $couleur, int $nbPlace, string $energie, int $userId, PDO $pdo): Result
    {
        $ecologique = false;
        if ($energie == Voiture::ENERGIE_ELECTRIQUE) {
            $ecologique = true;
        }
        $sql = "INSERT INTO voiture (Immatriculation, Date_premiere_immatriculation, Marque, Modele, Couleur, Nb_place, Energie, Ecologique, Utilisateur_Id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                $immatriculation,
                $dateImmmatriculation,
                $marque,
                $modele,
                $couleur,
                $nbPlace,
                $energie,
                $ecologique,
                $userId
            ]);
            return new Result(true, "Votre voiture a été crée avec succès.");
        } catch (PDOException $e) {
            echo $e->getMessage();
            return new Result(false, Voyage::RESULT_FAIL);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEnergy(): string
    {
        return $this->energy;
    }

    public function getEcologic(): int
    {
        return $this->ecologic;
    }

    public function getCarMake(): string
    {
        return $this->carMake;
    }

    public function getCarModel(): string
    {
        return $this->carModel;
    }

    public function getCarYear(): int
    {
        return $this->carYear;
    }

    public function getCarPlate(): string
    {
        return $this->carPlate;
    }

    public function getDriver(): Utilisateur
    {
        return $this->driver;
    }

}

?>
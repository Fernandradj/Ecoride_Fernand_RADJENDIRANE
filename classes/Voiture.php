<?php

class Voiture
{

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
        $sql = 'SELECT Voiture_Id, Ecologique, Marque, Modele, Energie, Immatriculation, YEAR(Date_premiere_immatriculation) as anneeImmat, Utilisateur_Id FROM Voiture WHERE Voiture_Id  = ?';
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

    /* function __construct(int $id, int $nbPlace, string $carMake, string $carModel, int $carYear, string $carPlate, Utilisateur $driver)
    {
        $this->id = $id;
        $this->nbPlace = $nbPlace;
        $this->carMake = $carMake;
        $this->carModel = $carModel;
        $this->carYear = $carYear;
        $this->carPlate = $carPlate;
        $this->driver = $driver;
    } */

    function getId(): int {
        return $this->id;
    }

    function getEnergy(): int {
        return $this->energy;
    }

    function getEcologic(): int {
        return $this->ecologic;
    }

    function getCarMake(): string {
        return $this->carMake;
    }

    function getCarModel(): string {
        return $this->carModel;
    }

    function getCarYear(): int {
        return $this->carYear;
    }

    function getCarPlate(): string {
        return $this->carPlate;
    }

    function getDriver(): Utilisateur {
        return $this->driver;
    }

}

?>
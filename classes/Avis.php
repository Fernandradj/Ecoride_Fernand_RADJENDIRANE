<?php

class Avis
{

    public const PARTICIPATION_STATUT_PAYE = 'Payé';
    public const PARTICIPATION_STATUT_ANNULE = 'Annulé';
    public const PARTICIPATION_STATUT_EN_COURS = 'En attente de validation';
    public const PARTICIPATION_STATUT_VALIDE = 'Validé';
    public const PARTICIPATION_STATUT_REJETE = 'Rejeté';

    public const RESULT_AVIS_SAVED = "Votre avis a bien été enregistré.";

    public const RESULT_AVIS_VALIDATED = "Votre avis a bien été validé.";

    public const RESULT_AVIS_REJECTED = "Votre avis a bien été rejeté.";

    private int $id;

    private int $note;
    private string $commentaire;
    private string $statut;

    private float $credit;

    private Utilisateur $utilisateur;
    private Voyage $voyage;

    public function __construct(int $userId, int $voyageId, PDO $pdo)
    {
        $sql = "SELECT Avis_Id, Commentaire, Note, Statut, Credit, Covoiturage_Id, Utilisateur_Id FROM participation WHERE Utilisateur_Id = ? AND Covoiturage_Id = ? AND Statut != ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $voyageId, Avis::PARTICIPATION_STATUT_ANNULE]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultat) {
            if (count($resultat) > 0) {
                $this->id = $resultat["Avis_Id"];
                $this->note = $resultat["Note"];
                $this->commentaire = $resultat["Commentaire"];
                $this->credit = $resultat["Credit"];
                $this->statut = $resultat["Statut"];

                $this->utilisateur = new Utilisateur($userId, $pdo);
                $this->voyage = new Voyage($voyageId, $pdo);
            }
        }
    }

    public function soumettreAvis(int $note, string $commentaire, $pdo): Result
    {
        try {
            $sql = "UPDATE participation SET Commentaire = ?, Note = ?, Statut = ? WHERE Avis_Id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$commentaire, $note, Avis::PARTICIPATION_STATUT_EN_COURS, $this->getId()]);
            $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
            return new Result(true, Avis::RESULT_AVIS_SAVED);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return new Result(false, Voyage::RESULT_FAIL);
    }

    public function validerAvis(int $employeId, $pdo): Result
    {
        try {
            $sql = "UPDATE participation SET Statut = ?, Employe_Id = ? WHERE Avis_Id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([Avis::PARTICIPATION_STATUT_VALIDE, $employeId, $this->getId()]);
            $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($resultat) {
                $driver = new Utilisateur($this->getVoyage()->getDriver()->getId(), $pdo);
                $driver->updateNote($pdo);
                return new Result(true, Avis::RESULT_AVIS_VALIDATED);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return new Result(false, Voyage::RESULT_FAIL);
    }

    public function rejeterAvis(int $employeId, $pdo): Result
    {
        try {
            $sql = "UPDATE participation SET Statut = ?, Employe_Id = ? WHERE Avis_Id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([Avis::PARTICIPATION_STATUT_REJETE, $employeId, $this->getId()]);
            $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($resultat) {
                return new Result(true, Avis::RESULT_AVIS_REJECTED);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return new Result(false, Voyage::RESULT_FAIL);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNote(): int
    {
        return $this->note;
    }

    public function getCredit(): int
    {
        return $this->credit;
    }

    public function getComments(): string {
        return $this->commentaire;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function getVoyage(): Voyage
    {
        return $this->voyage;
    }

    public function isAvisPaye(): bool
    {
        return ($this->getStatut() == Avis::PARTICIPATION_STATUT_PAYE);
    }

    public function isAvisEnCours(): bool
    {
        return ($this->getStatut() == Avis::PARTICIPATION_STATUT_EN_COURS);
    }

    public function isAvisEnAnnule(): bool
    {
        return ($this->getStatut() == Avis::PARTICIPATION_STATUT_ANNULE);
    }

    public function isAvisValide(): bool
    {
        return ($this->getStatut() == Avis::PARTICIPATION_STATUT_VALIDE);
    }

    public function isAvisRejete(): bool
    {
        return ($this->getStatut() == Avis::PARTICIPATION_STATUT_REJETE);
    }

    public function isAvisNotOpen(): bool
    {
        return ($this->getVoyage()->isVoyageOuvert() || ($this->getVoyage()->isVoyageEnCours()));
    }

    public function isAvisOpen(): bool
    {
        return (($this->getVoyage()->isVoyageTermine()) && $this->isAvisPaye());
    }

    public function isAvisClosed(): bool
    {
        return (!($this->isAvisNotOpen() || $this->isAvisOpen()));
    }
}

?>
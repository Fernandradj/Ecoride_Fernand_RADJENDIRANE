<?php

class Utilisateur
{
    public const USER_STATUT_ACTIF = 'Actif';

    public const USER_ROLE_ADMIN = 'Administrateur';
    public const USER_ROLE_EMPLOYE = 'Employe';
    public const USER_ROLE_PASSAGER = 'Passager';
    public const USER_ROLE_CHAUFFEUR = 'Chauffeur';
    public const USER_ROLE_PASSAGER_ET_CHAUFFEUR = 'Passager et Chauffeur';

    public const RESULT_UPDATE_PROFIL_SUCCESS = "Le profil a été mis à jour avec succès.";
    public const RESULT_UPDATE_NOTE_SUCCESS = "La note a été mis à jour avec succès.";
    public const RESULT_FAIL = "Une erreur s'est produite lors de l'enregistrement. Veuilllez réessayer ultérieurement.";

    private $pdo;
    private bool $loggedIn;
    private int $id;
    private string $username;
    private string $firstName;
    private string $lastName;
    private string $fullName;

    private string $dateOfBirth;

    private string $role;

    private string $statut;

    private string $address;
    private string $phone;
    private string $email;
    private float $reviewScore;
    private float $reviewCount;
    private string $photoUrl;
    private bool $preferenceAnimal;
    private bool $preferenceFumeur;
    private string $preference;

    private float $credit;

    public function __construct(int $id, PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->id = $id;
        $sql = "SELECT Utilisateur_Id, Pseudo, Statut, Utilisateur_id, Nom, Prenom, Date_naissance, Note, Adresse, Telephone, Email, Photo, Animal_accepte, Fumeur_accepte, Autre_preference, Credit FROM utilisateur WHERE Utilisateur_Id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            $this->username = $user["Pseudo"];
            $this->firstName = $user["Prenom"];
            $this->lastName = $user["Nom"];
            $this->fullName = $user["Prenom"] . " " . $user["Nom"];
            $this->dateOfBirth = $user["Date_naissance"];
            $this->address = $user["Adresse"];
            $this->statut = $user["Statut"];
            $this->phone = $user["Telephone"];
            $this->email = $user["Email"];
            $this->reviewScore = $user["Note"];
            $this->reviewCount = 0;
            $this->photoUrl = $user["Photo"];
            $this->preferenceAnimal = $user['Animal_accepte'];
            $this->preferenceFumeur = $user['Fumeur_accepte'];
            $this->preference = $user["Autre_preference"];
            $this->credit = $user["Credit"];

            $roles = Utilisateur::loadRoles($id, $pdo);
            $this->role = Utilisateur::checkUserRole($roles);
        }
    }

    public static function loadUserFromUsername(string $username, PDO $pdo): array
    {
        $sql = "SELECT Utilisateur_Id, Pseudo, Password, Statut FROM utilisateur WHERE Pseudo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user) {
            // echo "user found : ".$user['Utilisateur_Id'];
            return $user;
        }
        return [];
    }

    public static function checkEmailAlreadyUsed(string $usermail, PDO $pdo): bool
    {
        $sql = "SELECT Utilisateur_Id FROM utilisateur WHERE Statut = ? AND Email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([Utilisateur::USER_STATUT_ACTIF, $usermail]);
        $user = $stmt->fetch();
        if ($user) {
            return true;
        }
        return false;
    }

    public static function loadUserImageFromId(int $userId, PDO $pdo): array
    {
        $sql = "SELECT Utilisateur_Id, Photo FROM utilisateur WHERE Utilisateur_Id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if ($user) {
            return $user;
        }
        return [];
    }

    public static function loadRoles(int $userId, PDO $pdo): array
    {
        $sql = "SELECT role.Role_Id, Libelle FROM role JOIN utilisateur_role ON role.Role_Id = utilisateur_role.Role_Id WHERE Utilisateur_Id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $roles = $stmt->fetchAll();
        if ($roles) {
            return $roles;
        }
        return [];
    }

    public static function checkUserRole(array $roles): string
    {
        $userRole = "";
        $userIsChauffeur = false;
        $userIsPassager = false;
        $userIsEmployee = false;
        $userIsAdmin = false;
        foreach ($roles as $role) {
            // echo "role : " . $role;
            if ($role['Libelle'] == Utilisateur::USER_ROLE_CHAUFFEUR) {
                $userIsChauffeur = true;
            }
            if ($role['Libelle'] == Utilisateur::USER_ROLE_PASSAGER) {
                $userIsPassager = true;
            }
            if ($role['Libelle'] == Utilisateur::USER_ROLE_ADMIN) {
                $userIsAdmin = true;
            }
            if ($role['Libelle'] == Utilisateur::USER_ROLE_EMPLOYE) {
                $userIsEmployee = true;
            }
        }
        if ($userIsAdmin) {
            $userRole = Utilisateur::USER_ROLE_ADMIN;
        } else if ($userIsEmployee) {
            $userRole = Utilisateur::USER_ROLE_EMPLOYE;
        } else if ($userIsChauffeur && $userIsPassager) {
            $userRole = Utilisateur::USER_ROLE_PASSAGER_ET_CHAUFFEUR;
        } else if ($userIsPassager) {
            $userRole = Utilisateur::USER_ROLE_PASSAGER;
        } else if ($userIsChauffeur) {
            $userRole = Utilisateur::USER_ROLE_CHAUFFEUR;
        }
        return $userRole;
    }

    public static function loadAllRoles(PDO $pdo): array
    {
        $sql = "SELECT Role_Id, Libelle FROM role";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([]);
        $roles = $stmt->fetchAll();
        return $roles;
    }

    public static function loadAllRolesMapbyId(PDO $pdo): array
    {
        $allRoles = Utilisateur::loadAllRoles($pdo);
        $roleMap = [];
        foreach ($allRoles as $role) {
            $roleMap[$role['Libelle']] = $role['Role_Id'];
        }
        return $roleMap;
    }

    public static function loadIdsFromRoles(string $userRole, PDO $pdo): array
    {
        if (($userRole == null) || ($userRole == '')) {
            return [];
        }
        $rolesMap = Utilisateur::loadAllRolesMapbyId($pdo);
        $roleIds = [];
        if (str_contains($userRole, Utilisateur::USER_ROLE_PASSAGER)) {
            array_push($roleIds, $rolesMap[Utilisateur::USER_ROLE_PASSAGER]);
        }
        if (str_contains($userRole, Utilisateur::USER_ROLE_CHAUFFEUR)) {
            array_push($roleIds, $rolesMap[Utilisateur::USER_ROLE_CHAUFFEUR]);
        }
        return $roleIds;
    }

    public function updateUserProfile(string $username, string $lastName, string $firstName, string $address, string $phone, string $email, string $dateOfBirth, int $animalAccepted, int $smokerAccepted, string $preference, float $credit, string $hashedPassword, $photo, PDO $pdo): Result
    {
        try {
            if (($hashedPassword != null) && ($hashedPassword != '')) {
                if (($photo != null) && ($photo != '')) {
                    $sql = "UPDATE utilisateur SET Pseudo = ?, Nom = ?, Prenom = ?, Adresse = ?, Telephone = ?, Email = ?, Date_naissance = ?, Animal_accepte = ?, Fumeur_accepte = ?, Autre_preference = ?, Credit = ?, Password = ?, Photo = ? WHERE Utilisateur_Id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$username, $lastName, $firstName, $address, $phone, $email, $dateOfBirth, $animalAccepted, $smokerAccepted, $preference, $credit, $hashedPassword, $photo, $this->getId()]);
                } else {
                    $sql = "UPDATE utilisateur SET Pseudo = ?, Nom = ?, Prenom = ?, Adresse = ?, Telephone = ?, Email = ?, Date_naissance = ?, Animal_accepte = ?, Fumeur_accepte = ?, Autre_preference = ?, Credit = ?, Password = ? WHERE Utilisateur_Id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$username, $lastName, $firstName, $address, $phone, $email, $dateOfBirth, $animalAccepted, $smokerAccepted, $preference, $credit, $hashedPassword, $this->getId()]);
                }
            } else {
                if (($photo != null) && ($photo != '')) {
                    $sql = "UPDATE utilisateur SET Pseudo = ?, Nom = ?, Prenom = ?, Adresse = ?, Telephone = ?, Email = ?, Date_naissance = ?, Animal_accepte = ?, Fumeur_accepte = ?, Autre_preference = ?, Credit = ?, Photo = ? WHERE Utilisateur_Id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$username, $lastName, $firstName, $address, $phone, $email, $dateOfBirth, $animalAccepted, $smokerAccepted, $preference, $credit, $photo, $this->getId()]);
                } else {
                    $sql = "UPDATE utilisateur SET Pseudo = ?, Nom = ?, Prenom = ?, Adresse = ?, Telephone = ?, Email = ?, Date_naissance = ?, Animal_accepte = ?, Fumeur_accepte = ?, Autre_preference = ?, Credit = ? WHERE Utilisateur_Id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$username, $lastName, $firstName, $address, $phone, $email, $dateOfBirth, $animalAccepted, $smokerAccepted, $preference, $credit, $this->getId()]);
                }
            }
            if ($stmt) {
                return new Result(true, Utilisateur::RESULT_UPDATE_PROFIL_SUCCESS);
            }
        } catch (Exception $e) {
            // echo $e;
            return new result(false, Utilisateur::RESULT_FAIL);
        }
        return new result(false, Utilisateur::RESULT_FAIL);
    }

    public function loadVoitures(PDO $pdo): array
    {
        $sql_voitures = "SELECT Voiture_Id, Marque, Modele, Immatriculation, Date_premiere_immatriculation, Couleur, Energie, Nb_place FROM voiture WHERE Utilisateur_Id = ?";
        $stmt_voitures = $pdo->prepare($sql_voitures);
        $stmt_voitures->execute([$this->getId()]);
        $voitures = $stmt_voitures->fetchAll(PDO::FETCH_ASSOC);
        if ($voitures) {
            if (count($voitures) > 0) {
                return $voitures;
            }
        }
        return [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserRole(): string
    {
        return $this->role;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getDateOfBirth(): string
    {
        return $this->dateOfBirth;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getTelephone(): string
    {
        return $this->phone;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getReviewScore(): float
    {
        return $this->reviewScore;
    }

    public function getReviewCount(): float
    {
        return $this->reviewCount;
    }

    public function getPhotoUrl(): string
    {
        return $this->photoUrl;
    }

    public function getPreferenceAnimal(): bool
    {
        return $this->preferenceAnimal;
    }

    public function getPreferenceAnimalString(): string
    {
        return ($this->preferenceAnimal == 0 ? 'Non' : 'Oui');
    }

    public function getPreferenceFumeur(): bool
    {
        return $this->preferenceFumeur;
    }

    public function getPreferenceFumeurString(): string
    {
        return ($this->preferenceFumeur == 0 ? 'Non' : 'Oui');
    }

    public function getPreference(): string
    {
        return $this->preference;
    }

    public function getCredit(): int
    {
        return $this->credit;
    }

    public function userIsAdmin(): bool
    {
        return ($this->role == Utilisateur::USER_ROLE_ADMIN);
    }

    public function userIsEmploye(): bool
    {
        return ($this->role == Utilisateur::USER_ROLE_EMPLOYE);
    }

    public function userIsPassager(): bool
    {
        return ($this->role == Utilisateur::USER_ROLE_PASSAGER);
    }

    public function userIsChauffeur(): bool
    {
        return ($this->role == Utilisateur::USER_ROLE_CHAUFFEUR);
    }

    public function userIsPassagerChauffeur(): bool
    {
        return ($this->role == Utilisateur::USER_ROLE_PASSAGER_ET_CHAUFFEUR);
    }

    public function updateCredit(float $newCredit, PDO $pdo): bool
    {
        $sql = "UPDATE utilisateur SET Credit = " . $newCredit . " WHERE Utilisateur_Id = " . $this->getId();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        if ($stmt) {
            return true;
        }
        return false;
    }

    public function payerVoyage(float $price, PDO $pdo): bool
    {
        $newCredit = $this->getCredit() - $price;
        if ($this->updateCredit($newCredit, $pdo)) {
            return true;
        }
        return false;
    }

    public function rembourserVoyage(float $price, PDO $pdo): bool
    {
        $newCredit = $this->getCredit() + $price;
        if ($this->updateCredit($newCredit, $pdo)) {
            return true;
        }
        return false;
    }

    public function notifier(string $actionType, Voyage $voyage): bool
    {
        return true;
    }

    public function updateNote($pdo): Result
    {

        try {
            $sql = "SELECT utilisateur.Utilisateur_id, AVG(participation.note) avgNote FROM `participation` JOIN covoiturage ON covoiturage.Covoiturage_id = participation.Covoiturage_id AND covoiturage.Statut = 'Terminé' JOIN voiture ON voiture.Voiture_id = covoiturage.Voiture_id JOIN utilisateur on voiture.Utilisateur_id = utilisateur.Utilisateur_id WHERE utilisateur.Utilisateur_id = ? AND participation.Statut = 'Validé' GROUP BY utilisateur.Utilisateur_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$this->getId()]);
            $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultat) {
                $avgNote = $resultat["avgNote"];
                $sql = "UPDATE utilisateur SET Note = ? WHERE Utilisateur_Id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$avgNote, $this->getId()]);
                $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($resultat) {
                    return new Result(true, Utilisateur::RESULT_UPDATE_NOTE_SUCCESS);
                }
            }
        } catch (Exception $e) {

        }
        return new Result(false, Utilisateur::RESULT_FAIL);
    }

}
?>
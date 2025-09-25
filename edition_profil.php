<?php include 'imports.php' ?>
<?php include 'session.php' ?>
<?php

$succesdMsg = "";
$errorMsg = "";
$userIsChauffeur = false;
$userIsPassager = false;

if (isset($_SESSION['id']) && isset($_SESSION['role'])) {
    $user = new Utilisateur($_SESSION['id'], $pdo);
    $userRole = $_SESSION['role'];
    if ($user->userIsPassager()) {
        $userIsPassager = true;
    }
    else if ($user->userIsChauffeur()) {
        $userIsChauffeur = true;
    }
}

if (isset($_POST['choixRole'])) {
    $userIsChauffeur = false;
    $userIsPassager = false;
    $userRole = $_POST['choixRole'];
    if ($userRole == Utilisateur::USER_ROLE_PASSAGER) {
        $userIsPassager = true;
    }
    else if ($userRole == Utilisateur::USER_ROLE_CHAUFFEUR) {
        $userIsChauffeur = true;
    }
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST["saveProfile"])) {

        if (isset($_POST["role"])) {
            $userIsChauffeur = false;
            $userIsPassager = false;
            $userRole = $_POST["role"];
            if ($userRole == Utilisateur::USER_ROLE_PASSAGER) {
                $userIsPassager = true;
            }
            else if ($userRole == Utilisateur::USER_ROLE_CHAUFFEUR) {
                $userIsChauffeur = true;
            }
        }

        // Process form submission and update user data
        $lastName = htmlspecialchars($_POST['last_name']);
        $firstName = htmlspecialchars($_POST['first_name']);
        $address = htmlspecialchars($_POST['address']);
        $phone = htmlspecialchars($_POST['phone']);
        $email = htmlspecialchars($_POST['email']);
        $username = htmlspecialchars($_POST['username']);
        $dateOfBirth = htmlspecialchars($_POST['dob']);
        $credit = floatval($_POST['credit']);
        
        // Check if a new password was provided and hash it
        $hashedPassword = "";
        if (!empty($_POST['password'])) {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $animalAccepted = 0;
        $smokerAccepted = 0;
        $preference = "";
        if ($userIsChauffeur) {
            $animalAccepted = isset($_POST['pets_accepted']);
            $smokerAccepted = isset($_POST['smoker_accepted']);
            $preference = htmlspecialchars($_POST['other_prefs']);
        }
        
        if (isset($_POST["role"])) {
            $userRole = htmlspecialchars($_POST['role']);
        }

        if (isset($_SESSION['id']) && isset($_SESSION['role'])) {
            $currentRole = $_SESSION['role'];
            if ($currentRole != $userRole) {
                
                // Delete current role(s)
                $sql = "DELETE FROM utilisateur_role WHERE Utilisateur_Id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION['id']]);

                // Add updated role(s)
                $newRoleIds = Utilisateur::loadIdsFromRoles($userRole, $pdo);
                foreach ($newRoleIds as $roleId) {
                    $sql = "INSERT INTO utilisateur_role (Utilisateur_Id, Role_Id) VALUES (?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_SESSION['id'], $roleId]);
                }
            }
            
            $imgContent = "";
            if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
                $image = $_FILES['photo']['tmp_name'];
                $imgContent = file_get_contents($image);
            }

            $result = $user->updateUserProfile($username, $lastName, $firstName, $address, $phone, $email, $dateOfBirth, $animalAccepted, $smokerAccepted, $preference, $credit, $hashedPassword, $imgContent, $pdo);

            if ($result->getSucceeded()) {
                $_SESSION['role'] = $userRole;
                $succesdMsg = $result->getMessage();
                header("Refresh:2");
            }
            else {
                $userRole = $_SESSION['role'];
                $errorMsg = $result->getMessage();
                header("Refresh:2");
            }
        }
    }
}
?>

<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>
    <title>Mon Profil</title>
</head>

<body id="body">

    <?php include 'header.php'?>

    <!-- main -->
    <main>

        <div class="profile-container">

            <?php if (!empty($succesdMsg)): ?>
            <div class="success-msg"><?php echo $succesdMsg; ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMsg)): ?>
            <div class="error-msg"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
            
            <h2>Mon profil</h2>

            <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post"
                enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group photo-upload full-width">
                        <div class="photo-preview">
                            <img src="<?php echo "image.php?userId=".$_SESSION['id']?>" alt="Image depuis la BDD">
                        </div>
                        <label for="photo">Photo de profil</label>
                        <input type="file" id="photo" name="photo">
                    </div>

                    <?php if ($userIsPassager || $userIsChauffeur): ?>
                    <div class="form-group full-width">
                        <label for="role">Rôle</label>
                        <select id="role" name="role" onchange="userRoleUpdated()">
                            <option value="<?php echo Utilisateur::USER_ROLE_PASSAGER ?>"
                                <?php echo ($userRole === Utilisateur::USER_ROLE_PASSAGER) ? 'selected' : ''; ?>><?php echo Utilisateur::USER_ROLE_PASSAGER ?>
                            </option>
                            <option value="<?php echo Utilisateur::USER_ROLE_CHAUFFEUR ?>"
                                <?php echo ($userRole === Utilisateur::USER_ROLE_CHAUFFEUR) ? 'selected' : ''; ?>><?php echo Utilisateur::USER_ROLE_CHAUFFEUR ?>
                            </option>
                            <option value="<?php echo Utilisateur::USER_ROLE_PASSAGER_ET_CHAUFFEUR ?>"
                                <?php echo ($userRole === Utilisateur::USER_ROLE_PASSAGER_ET_CHAUFFEUR) ? 'selected' : ''; ?>>
                                <?php echo Utilisateur::USER_ROLE_PASSAGER_ET_CHAUFFEUR ?></option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="last_name">Nom</label>
                        <input type="text" id="last_name" name="last_name"
                            value="<?php echo htmlspecialchars($user->getLastName()); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="first_name">Prénom</label>
                        <input type="text" id="first_name" name="first_name"
                            value="<?php echo htmlspecialchars($user->getFirstName()); ?>" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="address">Adresse</label>
                        <input type="text" id="address" name="address"
                            value="<?php echo htmlspecialchars($user->getAddress()); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="tel" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($user->getTelephone()); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($user->getEmail()); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username"
                            value="<?php echo htmlspecialchars($user->getUsername()); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dob">Date de naissance</label>
                        <input type="date" id="dob" name="dob"
                            value="<?php echo htmlspecialchars($user->getDateOfBirth()); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password"
                            placeholder="Laissez vide pour ne pas changer">
                    </div>

                     <div class="form-group">
                        <label for="credit">Crédit</label>
                        <input type="number" id="credit" name="credit"
                            value="<?php echo htmlspecialchars($user->getCredit()); ?>" required>
                    </div>

                    <?php if ($userIsChauffeur): ?>

                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="pets_accepted" name="pets_accepted"
                                <?php echo $user->getPreferenceAnimal() ? 'checked' : ''; ?>>
                            <label for="pets_accepted">Animal accepté</label>
                        </div>

                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="smoker_accepted" name="smoker_accepted"
                                <?php echo $user->getPreferenceFumeur() ? 'checked' : ''; ?>>
                            <label for="smoker_accepted">Fumeur accepté</label>
                        </div>

                        <div class="form-group full-width">
                            <label for="other_prefs">Autre préférence</label>
                            <textarea id="other_prefs"
                                name="other_prefs"><?php echo htmlspecialchars($user->getPreference()); ?></textarea>
                        </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" name="saveProfile">Enregistrer les
                            modifications</button>
                    </div>
                    
                    <?php if ($userIsChauffeur): ?>
                        <a class="action-btn btn btn-primary" href="edition_voiture.php">Mes voitures</a>
                    <?php endif; ?>

                </div>
            </form>
        </div>
    </main>


    <?php include 'footer.php'?>

    <script>
        function userRoleUpdated() {
            // Récupérer la valeur de la liste déroulante
            let valeurSelectionnee = document.getElementById('role').value;
            console.log(valeurSelectionnee);
            // Créer un objet FormData pour envoyer les données au serveur
            let formData = new FormData();
            formData.append('choixRole', valeurSelectionnee);
            console.log(formData);

            // Envoyer la requête au script PHP (edition_profil.php)
            fetch('edition_profil.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Afficher la réponse du serveur dans la div "resultat"
                document.getElementById('body').innerHTML = data;
            })
            .catch(error => console.error('Erreur:', error));
        }
    </script>

</body>

</html>
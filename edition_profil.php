<?php include 'imports.php' ?>
<?php include 'session.php' ?>
<?php

if (isset($_SESSION['id']) && isset($_SESSION['role'])) {
    $user = new Utilisateur($_SESSION['id'], $pdo);
    $userRole = $_SESSION['role'];
    $userIsPassager = false;
    $userIsChauffeur = false;
}

$succesMsg = "";

if (isset($_POST['choixRole'])) {
    $valeur = $_POST['choixRole'];

    if ($valeur == Utilisateur::USER_ROLE_PASSAGER) {
        $userIsChauffeur = false;
    }
    else {
        $userIsChauffeur = true;
    }
    print_r($userIsChauffeur);

}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["choixRole"])) {
        $userRole = $_POST["choixRole"];
    }

    if (isset($_POST["saveProfile"])) {

        // Process form submission and update user data
        /* $lastName = htmlspecialchars($_POST['last_name']);
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
            // In a real application, you would hash this password
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $animalAccepted = false;
        $smokerAccepted = false;
        $preference = "";
        if ($userIsChauffeur) {
            $animalAccepted = isset($_POST['pets_accepted']);
            $smokerAccepted = isset($_POST['smoker_accepted']);
            $preference = htmlspecialchars($_POST['other_prefs']);
        }
        $userRole = htmlspecialchars($_POST['role']);

        if (isset($_SESSION['id']) && ($_SESSION['role'])) {
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

            $result = $user->updateUserProfile($username, $lastName, $firstName, $address, $phone, $email, $dateOfBirth, $animalAccepted, $smokerAccepted, $preference, $credit, $hashedPassword, $pdo);
            if ($result->getSucceeded()) {
                $_SESSION['role'] = $userRole;
                $succesMsg = $result->getMessage();
            }
            else {
                $userRole = $_SESSION['role'];
                $errorMsg = $result->getMessage();
            }
        } */

        // Handle photo upload
        /* if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            // In a real app, move the uploaded file to a secure directory
            // $targetDir = "uploads/";
            // $targetFile = $targetDir . basename($_FILES["photo"]["name"]);
            // move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile);
            $userData['photo'] = htmlspecialchars($_FILES['photo']['name']);
        } */

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
            <p>Votre profil est : <?php echo $user->getStatut(); ?></p>
            <h2>Mon profil</h2>

            <?php if (!empty($succesMsg)): ?>
            <div class="succes-msg"><?php echo $succesMsg; ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMsg)): ?>
            <div class="error-msg"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group photo-upload">
                        <div class="photo-preview">
                            <img src="<?php echo "image.php?userId=".$_SESSION['id']?>" alt="Image depuis la BDD">
                        </div>
                        <!-- <label for="photo">Photo de profil</label> -->
                        <!-- <input type="file" id="photo" name="photo"> -->
                    </div>

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
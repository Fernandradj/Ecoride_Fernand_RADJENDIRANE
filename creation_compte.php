<?php include 'imports.php' ?>
<?php include 'session.php' ?>
<?php

// Define variables and set them to empty values
$username = $useremail = $password = $confirm_password = "";
$username_err = $useremail_err = $password_err = $confirm_password_err = "";
$success_msg = "";

$isAdmin = false;

// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Process the create account form submission
    if (isset($_POST["create_account"])) {

        // Validate username
        if (empty(trim($_POST["username"]))) {
            $username_err = "Veuillez entrer un nom d'utilisateur.";
        } else {
            // In a real application, you would check if the username already exists in the database
            $username = trim($_POST["username"]);
        }

        // Validate user email
        if (empty(trim($_POST["useremail"]))) {
            $useremail_err = "Veuillez entrer une adresse mail.";
        } else {
            // In a real application, you would check if the user email already exists in the database
            $useremail = trim($_POST["useremail"]);
        }
        $emailUsed = Utilisateur::checkEmailAlreadyUsed($useremail, $pdo);
        if ($emailUsed) {
            $useremail_err = "Cette adresse mail est utilisée. Veuillez utiliser une adresse différente.";
        }

        if (!$isAdmin) {
            // Validate password
            if (empty(trim($_POST["password"]))) {
                $password_err = "Veuillez entrer votre mot de passe.";
            } elseif (strlen(trim($_POST["password"])) < 8) {
                $password_err = "Le mot de passe doit avoir au moins 8 caractères.";
            } else {
                $password = trim($_POST["password"]);
            }

            // Validate confirm password
            if (empty(trim($_POST["confirm_password"]))) {
                $confirm_password_err = "Veuillez confirmez votre mot de passe.";
            } else {
                $confirm_password = trim($_POST["confirm_password"]);
                if (empty($password_err) && ($password != $confirm_password)) {
                    $confirm_password_err = "Le mot de passe ne correspond pas.";
                }
            }
        }

        $entryValid = false;
        if ($isAdmin && empty($username_err) && empty($useremail_err)) {
            $entryValid = true;
        }
        if ((!$isAdmin) && empty($username_err) && empty($useremail_err) && empty($password_err) && empty($confirm_password_err)) {
            $entryValid = true;
        }
        // If there are no errors, proceed with account creation
        if ($entryValid) {

            $stmt = false;
            if ($isAdmin) {
                $sql = "INSERT INTO utilisateur (Pseudo, Email) VALUES (:username, :useremail)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $useremail]);
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO utilisateur (Pseudo, Password, Email) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $hashed_password, $useremail]);
            }

            if ($stmt) {
                $sql = "SELECT Utilisateur_Id FROM utilisateur WHERE Pseudo = ? AND Email = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $useremail]);
                $user = $stmt->fetch();

                if ($user) {
                    $sql = "SELECT Role_Id FROM role WHERE Libelle = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([Utilisateur::USER_ROLE_PASSAGER]);
                    $defaultRole = $stmt->fetch();

                    if ($defaultRole) {
                        $sql = "INSERT INTO utilisateur_role (Utilisateur_Id, Role_Id) VALUES (?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$user['Utilisateur_Id'], $defaultRole['Role_Id']]);

                        // For this example, we'll just set a success message
                        $success_msg = "Compte crée avec succès! Vous pouvez vous connecter maintenant.";

                        header("refresh:2;url=connexion.php");
                    }
                }
            }
        }
    }
}
?>

<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>

    <?php
    if ($isAdmin) {
        echo "<title>Créer un employé </title>";
    } else {
        echo "<title>Créer un compte</title>";
    }

    ?>
</head>

<body>

    <?php include 'header.php' ?>

    <!-- main -->
    <main>
        <div class="create_acct_container">

            <?php
            if ($isAdmin) {
                echo "<h2>Créer un employé </h2>";
            } else {
                echo "<h2>Créer un compte</h2>";
            }

            ?>

            <?php if (!empty($success_msg)): ?>
                <div class="success-msg"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">

                    <input type="text" id="username" name="username" placeholder="Nom d'utilisateur"
                        value="<?php echo htmlspecialchars($username); ?>" required>
                    <?php if (!empty($username_err)): ?>
                        <span class="error-msg"><?php echo $username_err; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">

                    <input type="text" id="useremail" name="useremail" placeholder="Votre email"
                        value="<?php echo htmlspecialchars($useremail); ?>" required>
                    <?php if (!empty($useremail_err)): ?>
                        <span class="error-msg"><?php echo $useremail_err; ?></span>
                    <?php endif; ?>
                </div>

                <?php if (!$isAdmin): ?>
                    <div class="form-group">

                        <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                        <?php if (!empty($password_err)): ?>
                            <span class="error-msg"><?php echo $password_err; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">

                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Confirmez votre mot de passe" required>
                        <?php if (!empty($confirm_password_err)): ?>
                            <span class="error-msg"><?php echo $confirm_password_err; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="button-group">
                    <button type="submit" class="btn btn-success" name="create_account">Créer un compte</button>
                </div>

                <?php if (!$isAdmin): ?>
                    <a href="connexion.php" class="text-link">Avez-vous déjà un compte? Se connecter ici.</a>
                <?php endif; ?>
            </form>
        </div>
    </main>

    <?php include 'footer.php' ?>

</body>

</html>
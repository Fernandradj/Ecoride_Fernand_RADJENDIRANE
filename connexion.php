<?php include 'imports.php' ?>
<?php include 'session.php' ?>

<?php

// Define variables and set them to empty values
$username = $password = "";
$username_err = $password_err = "";

// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Process the login form submission
    if (isset($_POST["login"])) {
        // Here you would add code to validate the user's input
        // For this example, we will just simulate a check.
        if (empty(trim($_POST["username"]))) {
            $username_err = "Veuillez entrer votre nom d'utilisateur.";
        } else {
            $username = trim($_POST["username"]);
        }

        if (empty(trim($_POST["password"]))) {
            $password_err = "Veuillez entrer votre mot de passe.";
        } else {
            $password = trim($_POST["password"]);
        }

        // If there are no errors, proceed with authentication
        if (empty($username_err) && empty($password_err)) {

            // $sql = "SELECT Utilisateur_Id, Pseudo, Password, Statut FROM utilisateur WHERE Pseudo = ?";
            // $stmt = $pdo->prepare($sql);
            // $stmt->execute([$username]);
            // $user = $stmt->fetch();
            $user = Utilisateur::loadUserFromUsername($username, $pdo);

            if (!empty($user)) {
                // echo "status : " . $user['Statut'];
                if ($user['Statut'] == Utilisateur::USER_STATUT_ACTIF) {

                    $hashed_password = $user['Password'];
                    // echo "pass check...";
                    if (password_verify($password, $hashed_password)) {
                        // echo "pass ok...";
                        $roles = Utilisateur::loadRoles($user['Utilisateur_Id'], $pdo);
                        // echo "roles : " . empty($roles);

                        if (!empty($roles)) {
                            $userRole = Utilisateur::checkUserRole($roles);
                            // echo "roles : " . ($userRole != "");
                            if ($userRole != "") {
                                // echo "role : " . $userRole;
                                // $_SESSION["user"] = new Utilisateur(true, $user['Utilisateur_Id'], $username, $userRole, $pdo);
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $user['Utilisateur_Id'];
                                $_SESSION["username"] = $username;
                                $_SESSION["role"] = $userRole;
                                header("location: index.php");
                            } else {
                                $username_err = "L'utilisateur est invalide. Veuillez contacter l'administrateur.";
                            }
                        }
                    } else {
                        // Password is not valid
                        echo "pass not ok...";
                        echo $password;
                        echo "--";
                        echo $hashed_password;
                        $password_err = "Le mot de passe est invalide.";
                    }
                } else {
                    // User is not active
                    $username_err = "Votre compte n'est pas actif. Veuillez contacter l'administrateur.";
                }
            } else {
                // Username doesn't exist
                $username_err = "Le nom d'utilisateur est invalide.";
            }
        }
    }
}
?>

<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>
    <title>Connexion</title>
</head>

<body>

    <?php include 'header.php' ?>

    <!-- main -->
    <main>

        <div class="login-container">
            <h2>Se Connecter</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" placeholder="Entrez votre nom d'utilisateur"
                        value="<?php echo htmlspecialchars($username); ?>" required>
                    <?php if (!empty($username_err)): ?>
                        <span class="error-msg"><?php echo $username_err; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe"
                        required>
                    <?php if (!empty($password_err)): ?>
                        <span class="error-msg"><?php echo $password_err; ?></span>
                    <?php endif; ?>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary" name="login">Se Connecter</button>
                    <a class="btn btn-secondary" href="creation_compte.php">Créer un compte</a>
                </div>
                <a href="mot_de_passe_oublie.php" class="text-link">Mot de passe oublié ?</a>
            </form>
        </div>
    </main>

    <?php include 'footer.php' ?>
</body>

</html>
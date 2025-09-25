<?php include 'imports.php' ?>
<?php include 'session.php' ?>
<?php

// Vérifier si le paramètre 'token' est présent dans l'URL
if (isset($_GET['token'])) {

    $success_msg = "";
    $errorMsg = "";

    $token = $_GET['token'];

    // Préparez la requête pour trouver l'utilisateur associé au token et qui n'a pas expiré
    $stmt = $pdo->prepare("SELECT Utilisateur_Id FROM nouveau_mot_de_passe WHERE token = ? AND Date_Expiration >= CURRENT_DATE");

    $stmt->execute([$token]);
    $user = $stmt->fetch();

    // Si aucun utilisateur n'est trouvé, le token est invalide ou a expiré
    if (!$user) {
        $errorMsg = "Le token est invalide ou a expiré.";
    } else {
        // Le token est valide, l'utilisateur peut changer son mot de passe
        // Vous pouvez stocker l'ID de l'utilisateur pour une utilisation ultérieure
        $userId = $user['Utilisateur_Id'];
    }
}
?>
<?php
// Vérifier si la requête est de type POST et si les champs sont définis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'], $_POST['confirm_password'], $_POST['token'])) {

    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $token = $_POST['token'];

    // 1. Re-valider le token pour des raisons de sécurité
    $stmt = $pdo->prepare("SELECT Utilisateur_Id FROM nouveau_mot_de_passe WHERE token = ? AND Date_Expiration >= CURRENT_DATE");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $errorMsg = "Le token est invalide ou a expiré. Veuillez refaire une demande de réinitialisation.";
    }

    // 2. Vérifier que les mots de passe correspondent
    if ($password !== $confirmPassword) {
        $errorMsg = "Les mots de passe ne correspondent pas.";
    }

    // 3. Valider la force du mot de passe (optionnel mais recommandé)
    if (strlen($password) < 8) {
        $errorMsg = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    if (empty($errorMsg)) {
        // 4. Hacher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 5. Mettre à jour le mot de passe 
        $stmt = $pdo->prepare("UPDATE utilisateur SET password = ? WHERE Utilisateur_Id = ?");
        $stmt->execute([$hashedPassword, $user['Utilisateur_Id']]);

        // 6. Supprimer le token
        $stmt = $pdo->prepare("DELETE FROM nouveau_mot_de_passe WHERE Utilisateur_Id = ?");
        $stmt->execute([$user['Utilisateur_Id']]);

        // 7. Rediriger l'utilisateur vers une page de succès
        if ($stmt) {
            $successMsg = "Le mot de passe a été réinitialisé avec succès.";
            header("refresh:2;url=connexion.php");
        }
    }

} else {
    // Si la requête n'est pas POST, cela signifie que le formulaire n'a pas été soumis
    // Affiche le formulaire (la logique est déjà en place au début du script)
}
?>

<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>
    <title>Créer votre mot de passe</title>
</head>

<body>

    <?php include 'header.php' ?>


    <!-- main -->
    <main>
        <h2>Définir un nouveau mot de passe</h2>

        <form action="creation_mot_de_passe.php" method="POST">
            <?php if (!empty($successMsg)): ?>
                <div class="success-msg"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMsg)): ?>
                <div class="error-msg"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <label for="password">Nouveau mot de passe :</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirmer le mot de passe :</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <button type="submit">Changer le mot de passe</button>
        </form>
    </main>

    <?php include 'footer.php' ?>

</body>

</html>
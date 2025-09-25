<?php include 'imports.php' ?>
<?php include 'session.php' ?>

<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>
    <title></title>
</head>

<body>

    <?php include 'header.php' ?>


    <!-- main -->
    <main>
        <?php
        // Afficher un message de confirmation ou d'erreur s'il est présent dans l'URL
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                echo "<p style='color: green;'>Votre message a été envoyé avec succès !</p>";
            } else {
                echo "<p style='color: red;'>Une erreur est survenue lors de l'envoi de votre message.</p>";
            }
        }
        ?>
        <form action="contact.php" method="POST">
            <div>
                <label for="name">Nom :</label><br>
                <input type="text" id="name" name="name" required>
            </div>
            <br>
            <div>
                <label for="email">Email :</label><br>
                <input type="email" id="email" name="email" required>
            </div>
            <br>
            <div>
                <label for="subject">Sujet :</label><br>
                <input type="text" id="subject" name="subject" required>
            </div>
            <br>
            <div>
                <label for="message">Message :</label><br>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>
            <br>
            <button type="submit">Envoyer le message</button>
        </form>
        <?php

        // Vérifier si la requête est de type POST et si les champs requis sont définis
        if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['subject']) && !empty($_POST['message'])) {

            // 1. Nettoyer et récupérer les données du formulaire
            $name = htmlspecialchars(trim($_POST['name']));
            $email = htmlspecialchars(trim($_POST['email']));
            $subject = htmlspecialchars(trim($_POST['subject']));
            $message = htmlspecialchars(trim($_POST['message']));

            // 2. Valider l'adresse email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header("Location: contact.php?status=error");
                exit();
            }

            // 3. Préparer les informations pour l'envoi de l'email
            $to = "delphinalexandra51015@gmail.com"; // Remplacez par l'adresse email où vous voulez recevoir les messages
            $headers = "From: " . $name . " <" . $email . ">\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            // 4. Envoyer l'email
            $success = mail($to, $subject, $message, $headers);

            // 5. Rediriger l'utilisateur vers la page de contact avec un message de statut
            if ($success) {
                header("Location: contact.php?status=success");
            } else {
                header("Location: contact.php?status=error");
            }
            exit();

        }
        // else {
        //     // Si la requête n'est pas POST ou si des champs sont manquants
        //     header("Location: contact.php?status=error");
        //     exit();
        // }
        ?>
    </main>

    <?php include 'footer.php' ?>

</body>

</html>
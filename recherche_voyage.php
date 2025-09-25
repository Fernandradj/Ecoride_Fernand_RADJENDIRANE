<?php include 'imports.php' ?>
<?php include 'session.php' ?>
<?php include 'html.php' ?>

<head>
    <?php include 'head.php' ?>
    <title>Rechercher un voyage</title>
</head>

<body>

    <?php include 'header.php' ?>

    <!-- main -->
    <main>
        <script src="https://code.jquery.com/jquery-2.2.1.min.js"></script>
        <form id="search_form_container" role="form" action="#" method="GET" onsubmit="processForm();">
            <div class="d-flex">
                <input class="form-control me-2" type="search" placeholder="Ville de départ" aria-label="Depart"
                    name="villeDepart" required />
                <input class="form-control me-2" type="search" placeholder="Ville d'arrivée" aria-label="Arrivee"
                    name="villeArrivee" required />
                <input class="form-control me-2" type="date" placeholder="Date" aria-label="Date" name="dateDepart"
                    required>
            </div>

            <div>
                <div>
                    <p>Voyage écologique seulement</p>
                    <label class="switch">
                        <input type="checkbox" name="voyageEco">
                        <span class="slider round"></span>
                    </label>
                </div>
                <div>
                    <p>Prix maximal</p>
                    <input type="number" min="0" name="prixMax">
                </div>
                <div>
                    <p>Durée maximale (en minutes)</p>
                    <input type="number" min="0" name="dureeMax">
                </div>
                <div>
                    <p>Note minimale (sur 5)</p>
                    <input type="number" min="0" max="5" step="1" name="noteMin">
                </div>
            </div>

            <!-- <button class="btn btn-outline-success" type="submit" id="submit">Rechercher</button> -->
            <input class="btn btn-outline-success" type="submit" value="Rechercher">
        </form>
        <!-- <b>Symbol: </b><span id="sym_disp"></span> -->

        <div class="result_section">

            <?php

            $filterQuery = ' WHERE ';
            $filersSet = 0;

            if (isset($_GET['villeDepart'])) {
                $filterQuery = $filterQuery . 'Ville_depart = \'' . $_GET['villeDepart'] . '\'';
                $filersSet += 1;
            }
            if (isset($_GET['villeArrivee'])) {
                $filterQuery = $filterQuery . ' AND Ville_arrivee = \'' . $_GET['villeArrivee'] . '\'';
                $filersSet += 1;
            }
            if (isset($_GET['dateDepart'])) {
                $filterQuery = $filterQuery . ' AND Date_depart = \'' . $_GET['dateDepart'] . '\'';
                $filersSet += 1;
            }
            if (isset($_GET['voyageEco'])) {
                $filterQuery = $filterQuery . ' AND Ecologique = TRUE';
            }
            if (isset($_GET['prixMax']) && ($_GET['prixMax'] != '')) {
                $filterQuery = $filterQuery . ' AND Prix_personne <= ' . $_GET['prixMax'];
            }
            if (isset($_GET['dureeMax']) && ($_GET['dureeMax'] != '')) {
                $filterQuery = $filterQuery . ' AND Duree <= ' . $_GET['dureeMax'];
            }
            if (isset($_GET['noteMin']) && ($_GET['noteMin'] != '')) {
                $filterQuery = $filterQuery . ' AND Note >= ' . $_GET['noteMin'];
            }
            $filterQuery = $filterQuery . ' AND covoiturage.Statut = \'' . Voyage::VOYAGE_STATUT_OUVERT.'\'';
            $filterQuery = $filterQuery . ' AND covoiturage.Nb_place >= 1';

            // Get voyages
            $queryGetVoyage = 'SELECT Covoiturage_id, Date_depart, Heure_depart, Adresse_depart, Ville_depart, Date_arrivee, Heure_arrivee, Adresse_arrivee, Ville_arrivee, covoiturage.Statut, covoiturage.Nb_place, Prix_personne, Duree, covoiturage.Voiture_id, voiture.Ecologique, utilisateur.Utilisateur_id, utilisateur.Nom, utilisateur.Prenom, utilisateur.Note FROM covoiturage JOIN voiture ON covoiturage.Voiture_id = voiture.Voiture_id JOIN utilisateur ON voiture.Utilisateur_id = utilisateur.Utilisateur_id' . $filterQuery;
            // echo $queryGetVoyage.'<br>';
            
            if ($filersSet == 3) {
                try {
                    foreach ($pdo->query($queryGetVoyage, PDO::FETCH_ASSOC) as $row) {
                        echo '<div class="card result_card" style="width: 18rem;">';
                        echo '<a class="result_card_link" href="detail_voyage.php?voyageId='.$row['Covoiturage_id'].'">';
                        echo '<div class="card-body">';
                        echo '<h6 class="card-title">' . $row['Ville_depart'] . ' à ' . $row['Ville_arrivee'] . '</h6>';
                        if ($row['Ecologique']) {
                            echo '<p class="card-subtitle mb-2 text-body-secondary">Ecologique</p>';
                        }
                        echo '<div class="result_card_detail">';
                        echo '<div class="result_card_time_left">';
                        echo '<h5>' . $row['Heure_depart'] . '</h5>';
                        echo '<h6>' . $row['Date_depart'] . '</h6>';
                        echo '</div>';
                        echo '<div class="result_card_time_middle">' . $row['Duree'] . ' min</div>';
                        echo '<div class="result_card_time_right">';
                        echo '<h5>' . $row['Heure_arrivee'] . '</h5>';
                        echo '<h6>' . $row['Date_arrivee'] . '</h6>';
                        echo '</div>';
                        echo '</div>';
                        echo '<p class="card-text result_card_place">' . $row['Nb_place'] . ' place(s) disponible(s)</p>';
                        echo '<p class="card-text result_card_price">' . $row['Prix_personne'] . ' €</p>';

                        echo '<p class="card-text result_card_note">Note du chauffeur : ' . $row['Note'] . '/5</p>';
                        echo '</div>';
                        echo '</a>';
                        echo '</div>';
                    }
                } catch (PDOException $e) {
                    echo 'PDO Exception : ' . $e->getMessage();
                }
            }

            ?>

        </div>

    </main>

    <?php include 'footer.php' ?>
    
    <script>
        /* $(function () {
            $('#submit').click(function () {
                $('#search_form_container').append('loading');
                var villeDepart = $('#villeDepart').val();
                $.ajax({
                    url: 'recherche_voyage.php',
                    type: 'GET',
                    data: 'villeDepart: ' + villeDepart,
                    success: function (result) {
                        $('#search_form_container').append('<p>' + result + '</p>')
                    }
                });
                return false;
            });
        }); */
        /* const my_form = document.getElementById("search_form_container");
        const sym_disp = document.getElementById("sym_disp");*
        function processForm() {
            sym_disp.innerHTML = my_form.symbol.value;
            return false;
        } */
       /* $("#submit").click(function () {

            $.post($("#search_form_container").attr("action"),
                $("#search_form_container :input").serializeArray(),
                function (info) {

                    $("#response").empty();
                    $("#response").html(info);

                });

            $("#search_form_container").submit(function () {
                return false;
            });
        }); */
    </script>
</body>

</html>
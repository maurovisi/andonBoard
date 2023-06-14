<?php
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    // Redirect to the login page if not logged in
    header('Location: login.php');
    exit;
}

// Connessione al database
require_once "db_config.php";

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
    exit;
}

$risorse = $pdo->query("SELECT risorsa FROM risorse")->fetchAll(PDO::FETCH_COLUMN);
$operatori = $pdo->query("SELECT sigla FROM operatori")->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <meta name="description" content="Andon board dashboard">
    <meta name="author" content="Mauro Visintin"/>
    <meta name="copyright" content="Mauro Visintin"/>

    <title>Andon Board | Dashboard</title>

    <link rel="apple-touch-icon" sizes="57x57" href="/icons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/icons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/icons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/icons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/icons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/icons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/icons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/icons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
    <link rel="manifest" href="/icons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/icons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <meta name="color-scheme" content="light">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">  
    <link rel="stylesheet" href="css/andon_style.css">
</head>
<body class="bk">
    <div class="container">
        <form id="dataForm">
            <div class="mb-3">
                <label for="efficiency" class="form-label">Efficienza</label>
                <select class="form-select" id="efficiency" name="efficiency">
                    <option value="settimanale">Settimanale</option>
                    <option value="mensile">Mensile</option>
                    <option value="annuale">Annuale</option>
                    <option value="tutto">Tutto</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="what" class="form-label">Di Cosa</label>
                <select class="form-select" id="what" name="what">
                    <option value="risorsa">Risorsa</option>
                    <option value="operatore">Operatore</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="resources" class="form-label">Risorse</label>
                <select class="form-select" id="resources" name="resources">
                    <!-- Popolated dynamically based on the choice in the "Di Cosa" select -->
                </select>
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn">Visualizza</button>
        </form>

        <h4 class="mt-5">Efficienza Totale: <span id="efficienza-totale"></span></h4>
        <h4>Qualità Totale: <span id="qualita-totale"></span></h4>

        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Orario</th>
                    <th>Efficienza</th>
                    <th>Qualità</th>
                </tr>
            </thead>
            <tbody id="table-body">
            </tbody>
        </table>

        <div id="results">
            <!-- The results will appear here -->
        </div>
    </div>

    <script>
        var risorse = <?php echo json_encode($risorse); ?>;
        var operatori = <?php echo json_encode($operatori); ?>;

        $(document).ready(function() {
            $('#dataForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    type: "POST",
                    url: "get_data.php",
                    data: $(this).serialize(),
                    dataType: 'json', // Expect a JSON response
                    success: function(data) {
                        $('#efficienza-totale').text(data.efficienza.toFixed(2) + '%');
                        $('#qualita-totale').text(data.qualita.toFixed(2) + '%');

                        // Clear out the existing rows
                        $('#table-body').empty();

                        // Add a new row for each record
                        $.each(data.records, function(i, record) {
                            var row = $('<tr>');
                            row.append('<td>' + record.data + '</td>');
                            row.append('<td>' + record.orario + '</td>');
                            row.append('<td>' + record.efficienza.toFixed(2) + '%</td>');
                            row.append('<td>' + record.qualita.toFixed(2) + '%</td>');
                            $('#table-body').append(row);
                        });
                    }
                });
            });

            $('#what').change(function() {
                var selection = $(this).val();
                var options = [];

                if (selection === 'risorsa') {
                    options = risorse;
                } else if (selection === 'operatore') {
                    options = operatori;
                }

                var select = $('#resources');
                select.empty();

                $.each(options, function(index, value) {
                    select.append('<option value="' + value + '">' + value + '</option>');
                });
            }).trigger('change'); // Trigger the change event to populate the select on page load
        });
    </script>
</body>
</html>
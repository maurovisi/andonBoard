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

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">

    <!-- JavaScript -->
    <script src="https://cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.11.5/sorting/date-eu.js"></script>

</head>
<body class="bk">
    <div class="container">
        <h1 class="mt-4 text-center">Analisi dati Andon Board</h1>
        <form id="dataForm">
            <div class="row gy-2 gx-3 my-4 align-items-center">
                <div class="col">
                    <label for="efficiency" class="form-label"><b>Efficienza</b></label>
                    <select class="form-select" id="efficiency" name="efficiency">
                        <option value="settimanale">Settimanale</option>
                        <option value="mensile">Mensile</option>
                        <option value="trimestrale">trimestrale</option>
                        <option value="semestrale">semestrale</option>
                        <option value="annuale">Annuale</option>
                        <option value="tutto">Tutto</option>
                    </select>
                </div>

                <div class="col">
                    <label for="what" class="form-label"><b>Di Cosa</b></label>
                    <select class="form-select" id="what" name="what">
                        <option value="risorsa">Risorsa</option>
                        <option value="operatore">Operatore</option>
                    </select>
                </div>

                <div class="col">
                    <label for="resources" class="form-label"><b>Risorse</b></label>
                    <select class="form-select" id="resources" name="resources">
                        <!-- Popolated dynamically based on the choice in the "Di Cosa" select -->
                    </select>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary" data-target="bootstrap-table">Visualizza tabella Bootstrap</button>
                <button type="button" class="btn btn-info" data-target="data-table">Visualizza tabella DataTables</button>
            </div>   
        </form>

        <h4 class="mt-5">Utilizzo Risorsa Totale: <span id="usoRisorsaTot"></span></h4>
        <h4>Efficienza Totale: <span id="efficienza-totale"></span></h4>
        <h4 class="mb-5">Qualità Totale: <span id="qualita-totale"></span></h4>

        <div class="table-container">
            <table class="table table-striped mt-4" id="bootstrap-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Ciclo</th>
                        <th>Operatore</th>
                        <th>TC</th>
                        <th>Obiettivo</th>
                        <th>Pz. Realizz.</th>
                        <th>Pz. scarti</th>
                        <th>Efficienza</th>
                        <th>Qualità</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                </tbody>
            </table>

            <div id="data-table-container" style="display: none;">
                <table class="table table-striped" id="data-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Ciclo</th>
                            <th>Operatore</th>
                            <th>TC</th>
                            <th>Obiettivo</th>
                            <th>Pz. Realizz.</th>
                            <th>Pz. scarti</th>
                            <th>Efficienza</th>
                            <th>Qualità</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let risorse = <?php echo json_encode($risorse); ?>;
        let operatori = <?php echo json_encode($operatori); ?>;

        function getShift(time) {
            let hour = parseInt(time.split(':')[0]);
            if (hour >= 6 && hour < 14) {
                return 'Turno 1';
            } else if (hour >= 14 && hour < 22) {
                return 'Turno 2';
            } else {
                return 'Turno 3';
            }
        }

        $(document).ready(function() {
            $('#dataForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    type: "POST",
                    url: "get_data.php",
                    data: $(this).serialize(),
                    dataType: 'json', // Expect a JSON response
                    success: function(data) {
                        $('#usoRisorsaTot').text(data.usoRisorsaTot.toFixed(2) + '%');
                        $('#efficienza-totale').text(data.efficienzaTot.toFixed(2) + '%');
                        $('#qualita-totale').text(data.qualitaTot.toFixed(2) + '%');

                        let lastDate = '';
                        let lastShift = '';

                        // Clear out the existing rows
                        $('#table-body').empty();

                        // Add a new row for each record
                        let efficienzaTurno = 0;
                        let qualitaTurno = 0;
                        let dataTurnoPrec = "";

                        // Populate Bootstrap table
                        $.each(data.records, function(i, record) {
                            let row = $('<tr>');
                            row.append('<td>' + record.data_turno + '</td>');
                            row.append('<td>' + record.codice_ciclo + '</td>');
                            row.append('<td>' + record.sigla + '</td>');
                            row.append('<td>' + record.tempo_ciclo + '</td>');
                            row.append('<td>' + record.pzDaRealizzare + '</td>');
                            row.append('<td>' + record.totPzRealizzati + '</td>');
                            row.append('<td>' + record.totPzScarti + '</td>');
                            row.append('<td>' + record.efficienza.toFixed(2) + '%</td>');
                            row.append('<td>' + record.qualita.toFixed(2) + '%</td>');

                            // sommo efficienza e qualità di ogni turno e la scrivo prima del nuovo turno
                            efficienzaTurno += record.efficienza.toFixed(2);
                            qualitaTurno += record.qualita.toFixed(2);

                            if (record.data_turno != dataTurnoPrec) {
                                let row1 = $('<tr>');
                                row1.append('<td colspan="4">Efficienza turno = ' + record.efficienza.toFixed(2) + '%</td>');
                                row1.append('<td colspan="4">Qualità turno = ' + record.qualita.toFixed(2) + '%</td>');
                                row1.append('<td></td>');
                                $('#table-body').append(row1);
                            }

                            $('#table-body').append(row);
                            dataTurnoPrec = record.data_turno;
                        });

                        // Destroy DataTables table (if it exists)
                        if ($.fn.DataTable.isDataTable('#data-table')) {
                            $('#data-table').DataTable().destroy();
                            $('#data-table tbody').empty();
                        }

                        // Populate DataTables table with data
                        $.each(data.records, function(i, record) {
                            let row = $('<tr>');
                            row.append('<td>' + record.data_turno + '</td>');
                            row.append('<td>' + record.codice_ciclo + '</td>');
                            row.append('<td>' + record.sigla + '</td>');
                            row.append('<td>' + record.tempo_ciclo + '</td>');
                            row.append('<td>' + record.pzDaRealizzare + '</td>');
                            row.append('<td>' + record.totPzRealizzati + '</td>');
                            row.append('<td>' + record.totPzScarti + '</td>');
                            row.append('<td>' + record.efficienza.toFixed(2) + '%</td>');
                            row.append('<td>' + record.qualita.toFixed(2) + '%</td>');

                            $('#data-table tbody').append(row);
                        });

                        // Initialize DataTables
                        $('#data-table').DataTable({
                            lengthMenu: [50, 100, 200, -1],
                            buttons: [
                                {
                                    extend: 'copy',
                                    text: 'Copy',
                                    exportOptions: {
                                        columns: ':visible'
                                    }
                                },
                                {
                                    extend: 'excel',
                                    text: 'Excel',
                                    exportOptions: {
                                        columns: ':visible'
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    text: 'PDF',
                                    exportOptions: {
                                        columns: ':visible'
                                    }
                                },
                                {
                                    extend: 'csv',
                                    text: 'CSV',
                                    exportOptions: {
                                        columns: ':visible'
                                    }
                                },
                                {
                                    extend: 'print',
                                    text: 'Print',
                                    exportOptions: {
                                        columns: ':visible'
                                    }
                                },
                                'colvis'
                            ]
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

            $('button[data-target]').click(function() {
                let target = $(this).data('target');

                if (target === 'bootstrap-table') {
                    $('#bootstrap-table').show();
                    $('#data-table-container').hide();
                } else if (target === 'data-table') {
                    $('#bootstrap-table').hide();
                    $('#data-table-container').show();
                }
            });
        });
    </script>

</body>
</html>

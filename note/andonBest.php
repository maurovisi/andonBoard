<?php
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    // Redirect to the login page if not logged in
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <meta name="description" content="Andon board best analisys">
    <meta name="author" content="Mauro Visintin"/>
    <meta name="copyright" content="Mauro Visintin"/>

    <title>Andon Board | Best</title>

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

    <!-- Additional Libraries for Exporting Data -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <!-- JavaScript -->
    <script src="https://cdn.datatables.net/1.11.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.11.5/sorting/date-eu.js"></script>

    <!-- CSS personalized -->
    <link rel="stylesheet" href="css/stickyFooter.css">

</head>
<body class="bk d-flex flex-column h-100">
    <header>
        <hgroup class="text-center">
            <h1 class="mt-4">Analisi dati Best</h1>
            <h5>La classifica dei migliori</h5>
        </hgroup>
    </header>

    <main class="flex-shrink-0">
        <div class="container mb-4">
            <form id="bestForm">
                <div class="row gy-2 gx-3 my-4 align-items-center">
                    <div class="col">
                        <label for="intervallo" class="form-label"><b>Intervallo</b></label>
                        <select class="form-select" id="intervallo" name="intervallo">
                            <option value="settimanale">Settimanale</option>
                            <option value="mensile">Mensile</option>
                            <option value="trimestrale">Trimestrale</option>
                            <option value="semestrale">Semestrale</option>
                            <option value="annuale">Annuale</option>
                            <option value="tutto">Tutto</option>
                        </select>
                    </div>

                    <div class="col">
                        <label for="about" class="form-label"><b>Riguardo a</b></label>
                        <select class="form-select" id="about" name="about">
                            <option value="risorsa">Risorse</option>
                            <option value="operatore">Operatori</option>
                        </select>
                    </div>

                    <div class="col">
                        <label for="groups" class="form-label"><b>Gruppi</b></label>
                        <select class="form-select" id="groups" name="groups">
                            <option value="brother">Brother</option>
                            <option value="tornitura">Tornitura</option>
                            <option value="fresatura">Fresatura</option>
                            <option value="medicale">Medicale</option>
                        </select>
                    </div>
                </div>

                <div class="row gy-2 gx-3 my-4 align-items-center">
                    <div class="col" id="startingPoint" style="display:none">
                        <label for="start" class="form-label"><b>Mese o Inizio da...</b></label>
                        <select class="form-select" id="start" name="start">
                            <option value="01">Gennaio</option>
                            <option value="02">Febbraio</option>
                            <option value="03">Marzo</option>
                            <option value="04">Aprile</option>
                            <option value="05">Maggio</option>
                            <option value="06">Giugno</option>
                            <option value="07">Luglio</option>
                            <option value="08">Agosto</option>
                            <option value="09">Settembre</option>
                            <option value="10">Ottobre</option>
                            <option value="11">Novembre</option>
                            <option value="12">Dicembre</option>
                        </select>
                    </div>

                    <div class="col" id="yearRowBest" style="display: none;">
                        <label for="yearBest" class="form-label"><b>Anno</b></label>
                        <select class="form-select" id="yearBest" name="yearBest">
                            <!-- Popolated dynamically with current year and previous 5 years -->
                        </select>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success" data-target="data-table-best">Visualizza tabella</button>
                </div> 
            </form>

            <div class="table-container">
                <div id="data-table-best-container" style="display: none;">
                    <table class="table table-striped" id="data-table-best">
                        <thead>
                            <tr>
                                <th>Posizione</th>
                                <th>Classifica</th>
                                <th>Efficienza</th>
                                <th>Qualità</th>
                                <th>Pz. buoni</th>
                                <th>Pz. scarti</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Posizione</th>
                                <th>Classifica</th>
                                <th>Efficienza</th>
                                <th>Qualità</th>
                                <th>Pz. buoni</th>
                                <th>Pz. scarti</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <?php
        include "footer.php";
    ?>


<script>
    // Ottieni il riferimento alla select dell'intervallo
    let intervalloSelect = document.getElementById("intervallo");

    // Ottieni il riferimento alla row contenente la select di start
    let startRow = document.querySelector("#startingPoint");

    // Aggiungi un event listener per l'evento di cambio valore della select dell'intervallo
    intervalloSelect.addEventListener("change", function() {
        // Ottieni il valore selezionato
        let selectedIntervallo = intervalloSelect.value;
    
        // Mostra o nascondi la row di start in base al valore selezionato
        if (selectedIntervallo === "mensile" || selectedIntervallo === "trimestrale" || selectedIntervallo === "semestrale" || selectedIntervallo === "annuale") {
            startRow.style.display = "block";
            yearRowBest.style.display = "block";
        } else {
            startRow.style.display = "none";
            yearRowBest.style.display = "none";
        }
    });

    // Aggiungi gli anni alla select degli anni
    let yearSelectBest = document.getElementById("yearBest");
    let currentYearBest = new Date().getFullYear();

    for (let i = currentYearBest; i >= currentYearBest - 5; i--) {
        let option = document.createElement("option");
        option.value = i;
        option.text = i;
        
        if (i === currentYearBest) {
            option.selected = true;
        }
        
        yearSelectBest.appendChild(option);
    }
</script>

<script>
$(document).ready(function() {
    $('#bestForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "getBest.php",
            data: $(this).serialize(),
            dataType: 'json', // Expect a JSON response
            success: function(data) {
                // Populate DataTables table with data
                $.each(data.records, function(i, record) {
                    let row = $('<tr>');
                    row.append('<td>' + record.posizione + '</td>');
                    row.append('<td>' + record.classifica + '</td>');
                    row.append('<td>' + record.efficienza.toFixed(2) + '%</td>');
                    row.append('<td>' + record.qualita.toFixed(2) + '%</td>');
                    row.append('<td>' + record.pz_buoni + '</td>');
                    row.append('<td>' + record.pz_scarti + '</td>');
                    row.append('</tr>');

                    $('#data-table-best tbody').append(row);
                });

                // Initialize DataTables
                $('#data-table-best').DataTable({
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
                }).buttons().container().appendTo('#data-table_wrapper .col-md-6:eq(0)');
            }
        });
    });
});
</script>


</body>
</html>
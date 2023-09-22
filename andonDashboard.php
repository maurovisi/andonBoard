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
$operatori = $pdo->query("SELECT DISTINCT sigla FROM operatori")->fetchAll(PDO::FETCH_COLUMN);

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
        <h1 class="mt-4 text-center">Analisi dati Andon Board</h1>
    </header>
    <main class="flex-shrink-0">
        <div class="container mb-4">            
            <form id="dataForm">
                <div class="row gy-2 gx-3 my-4 align-items-center">
                    <div class="col">
                        <label for="efficiency" class="form-label"><b>Efficienza</b></label>
                        <select class="form-select" id="efficiency" name="efficiency">
                            <option value="settimanale">Settimanale</option>
                            <option value="mensile">Mensile</option>
                            <option value="trimestrale">Trimestrale</option>
                            <option value="semestrale">Semestrale</option>
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
                    <div class="col" id="yearRow" style="display:none">
                        <label for="year" class="form-label"><b>Anno</b></label>
                        <select class="form-select" id="year" name="year">
                            <!-- Popolated dynamically with current year and previous 5 years -->
                        </select>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary" data-target="bootstrap-table">Visualizza tabella Con Parziali</button>
                    <button type="button" class="btn btn-info" data-target="data-table">Visualizza tabella per Export</button>
                </div>
            </form>
            <!--
            <h5 class="mt-5">Utilizzo Risorsa Totale: <span class="fw-bold" id="usoRisorsaTot"></span></h5>
            <p class="pb-2">Rapporto tra il tempo massimo disponibile per far lavorare la macchina e quello in cui effettivamente ha lavorato.</p> -->
            <h5>Efficienza Totale: <span class="fw-bold" id="efficienza-totale"></span></h5>
            <p class="pb-2">Efficienza Totale calcolata in base alla somma delle efficienze dei turni, potrebbe differire dall'efficienza calcolata in rapporto alle ore di lavoro effettivamente lavorate e quelle con profitto.</p>
            <h5>Tot pz. realizz. <span class="fw-bold" id="totalePezziRealizzati"></span> | Tot pz. buoni <span class="fw-bold" id="pzBuoniRealizzati"></span> | Tot pz. scarti <span class="fw-bold" id="pzScartiRealizzati"></span></h5>
            <h5 class="pb-4">Qualità Totale: <span class="fw-bold" id="qualita-totale"></span> | % di scarto <span class="fw-bold" id="percentualeScarto"></span></h5>            
            <h5>Ore di lavoro complessive <span class="fw-bold" id="oreMax"></span> | Ore lavorate con profitto: <span class="fw-bold" id="oreProfittevoli"></span> | % di scarto <span class="fw-bold" id="scartoOre"></span></h5>
            <p class="mb-5">Ore massime possibili e ore effettivamente lavorate con profitto</p>

            <table class="table table-striped table-hover">
                <thead>
                    <tr class="table-info">
                        <th>Pezzo</th>
                        <th>Obiettivo</th>
                        <th>T.pz. Buoni</th>
                        <th>Tpz. Scarti</th>
                        <th>Efficienza</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

            <h6>Legenda:</h6>
            <p>La lettera R dopo le voci nelle intestazioni es. "Efficienza R" indica l'efficienza relativa del turno di lavoro riferita a quella lavorazione. Se ad es. una persona ha lavorato 4 ore durante le quali ha ottenuto un'efficienza del 100% qui risulterà 50% perchè complessivamente nel turno quello è stato il suo rendimento. Se una persona durante il turno, ha effettuato lavorazioni diverse, l'efficienza complessiva sarà la somma delle parziali ognuna su una riga diversa della tabella e indicate nel riepilogo finale del turno con medesimo codice operatore.</p>
            <p>Cliccare sulla sigla operatore per visualizzare i dettagli dell'intervallo specifico della lavorazione.</p>

            <div class="table-container">

                <table class="table table-striped mt-4" id="bootstrap-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Lavorazione</th>
                            <th>Operatore</th>
                            <th>TC</th>
                            <th>pz.ora</th>
                            <th>Obiettivo</th>
                            <th>Pz. Realizz.</th>
                            <th>Pz. buoni</th>
                            <th>Pz. scarti</th>
                            <th>Efficienza R</th>
                            <th>Qualità R</th>
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
                                <th>Lavorazione</th>
                                <th>Operatore</th>
                                <th>TC</th>
                                <th>pz.ora</th>
                                <th>Obiettivo</th>
                                <th>Pz. Realizz.</th>
                                <th>Pz. buoni</th>
                                <th>Pz. scarti</th>
                                <th>Efficienza R</th>
                                <th>Qualità R</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Data</th>
                                <th>Lavorazione</th>
                                <th>Operatore</th>
                                <th>TC</th>
                                <th>pz.ora</th>
                                <th>Obiettivo</th>
                                <th>Pz. Realizz.</th>
                                <th>Pz. buoni</th>
                                <th>Pz. scarti</th>
                                <th>Efficienza R</th>
                                <th>Qualità R</th>
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
        // Ottieni il riferimento alla select dell'efficienza
        let efficiencySelect = document.getElementById("efficiency");

        // Ottieni il riferimento alla row contenente la select di start
        let startRow = document.querySelector("#startingPoint");

        // Ottieni il riferimento alla row dell'anno
        let yearRow = document.getElementById("yearRow");

        // Aggiungi un event listener per l'evento di cambio valore della select dell'efficienza
        efficiencySelect.addEventListener("change", function() {
            // Ottieni il valore selezionato
            let selectedEfficiency = efficiencySelect.value;
        
            // Mostra o nascondi la row di start in base al valore selezionato
            if (selectedEfficiency === "mensile" || selectedEfficiency === "trimestrale" || selectedEfficiency === "semestrale" || selectedEfficiency === "annuale") {
                startRow.style.display = "block";
                yearRow.style.display = "block";
            } else {
                startRow.style.display = "none";
                yearRow.style.display = "none";
            }
        });

        // Aggiungi gli anni alla select degli anni
        let yearSelect = document.getElementById("year");
        let currentYear = new Date().getFullYear();

        for (let i = currentYear; i >= currentYear - 5; i--) {
            let option = document.createElement("option");
            option.value = i;
            option.text = i;
            
            if (i === currentYear) {
                option.selected = true;
            }
            
            yearSelect.appendChild(option);
        }
    </script>

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
                        /*
                        if (data.usoRisorsaTot != 'errore') {
                            $('#usoRisorsaTot').text(data.usoRisorsaTot.toFixed(2) + '%');
                        } else {
                            $('#usoRisorsaTot').text('Calcolo non possibile per questa opzione.');
                        }
                        */
                        $('#efficienza-totale').text(data.efficienzaTot.toFixed(2) + '%');
                        //$('#efficienza-totale-R').text(data.efficienzaTotaleR.toFixed(2) + '%');
                        $('#qualita-totale').text(data.qualitaTot.toFixed(2) + '%');
                        $('#totalePezziRealizzati').text(data.totalePezziRealizzati);
                        $('#totPzPossibiliDaRealizzare').text(data.totPzPossibiliDaRealizzare);
                        $('#pzBuoniRealizzati').text(data.pzBuoniRealizzati);
                        $('#pzScartiRealizzati').text(data.pzScartiRealizzati);
                        $('#percentualeScarto').text((100 - data.qualitaTot).toFixed(2) + '%');
                        $('#oreMax').text(data.totOreLavoroIntervallo);
                        $('#oreProfittevoli').text(data.totOreProfittevoli);
                        $('#scartoOre').text(data.scartoOre + '%');

                        let lastDate = '';
                        let lastShift = '';

                        // Clear out the existing rows
                        $('#table-body').empty();

                        // Add a new row for each record
                        let efficienzaTurno = 0;
                        let qualitaTurno = 0;
                        let dataTurnoPrec = "";
                        let m = 0;
                        let nRows = 1;

                        let lunghezza = Object.keys(data.records).length;

                        // Populate Bootstrap table
                        $.each(data.records, function(i, record) {
                            // sommo efficienza e qualità di ogni turno e la scrivo prima del nuovo turno                            
                            let qualitaParsificata = parseFloat(record.qualita);
                            qualitaTurno += qualitaParsificata;
                            
                            /* evito di inserire il totale come prima riga mentre devo inserire quasti dati solo dopo i primi parziali */
                            if (record.data_turno != dataTurnoPrec && nRows > 1) {
                                // devo sottrarre il record appena sommato perchè appartiene al turno dopo, successivamente dovrò assegnare questo valore alle variabili efficienzaTurno e QualitaTurno per non perderne traccia
                                
                                qualitaTurno -= qualitaParsificata;
                                    
                                if (m > 0) {
                                    
                                    qualitaTurno = (qualitaTurno/m).toFixed(2);
                                } else {
                                    
                                    qualitaTurno = qualitaTurno.toFixed(2);
                                }
                                                                    
                                let row1 = $('<tr>');

                                if (efficienzaTurno <= 80) {
                                    row1.append('<td colspan="5"><b class="text-danger">Efficienza turno = ' + efficienzaTurno + '%</b></td>');
                                    row1.append('<td colspan="6"><b class="text-danger">Qualità turno = ' + qualitaTurno + '%</b></td>');
                                } else {
                                    row1.append('<td colspan="5"><b>Efficienza turno = ' + efficienzaTurno + '%</b></td>');
                                    row1.append('<td colspan="6"><b>Qualità turno = ' + qualitaTurno + '%</b></td>');
                                }
                                
                                row1.append('</tr>');
                                $('#table-body').append(row1);

                                qualitaTurno = qualitaParsificata;
                                m = 0;
                            }

                            dataTurnoPrec = record.data_turno;
                            efficienzaTurno = record.effTurno;
                            
                            let row = $('<tr>');
                            row.append('<td>' + record.data_turno + '</td>');
                            row.append('<td>' + record.codice_ciclo + '</td>');
                            row.append('<td><a href="dettagliOperatore.php?sigla=' + record.sigla + '&data=' + record.data_turno + '&codCiclo=' + record.codice_ciclo + '" target="_blank">' + record.sigla + '</a></td>');
                            row.append('<td>' + record.tempo_ciclo + '</td>');
                            row.append('<td>' + record.pzPossibiliDaRealizzareOra + '</td>');
                            row.append('<td>' + record.pzObiettivo + '</td>');
                            row.append('<td>' + record.sommaTotPzRealizzati + '</td>');
                            row.append('<td>' + record.totPzBuoni + '</td>');
                            row.append('<td>' + record.totPzScarti + '</td>');
                            row.append('<td>' + record.efficienza + '%</td>');
                            row.append('<td>' + record.qualita + '%</td>');
                            row.append('</tr>');

                            $('#table-body').append(row);

                            /* inserimento solo dopo ultimo record */
                            if (nRows == lunghezza) {
                                if (m > 0) {
                                    ++m;
                                    qualitaTurno = (qualitaTurno/m);
                                }
                                    
                                let row2 = $('<tr>');
                                if (efficienzaTurno <= 80) {
                                    row2.append('<td colspan="5"><b class="text-danger">Efficienza turno = ' + efficienzaTurno + '%</b></td>');
                                    row2.append('<td colspan="6"><b class="text-danger">Qualità turno = ' + qualitaTurno + '%</b></td>');
                                } else {
                                    row2.append('<td colspan="5"><b>Efficienza turno = ' + efficienzaTurno + '%</b></td>');
                                    row2.append('<td colspan="6"><b>Qualità turno = ' + qualitaTurno + '%</b></td>');
                                }
                                row2.append('</tr>');
                                $('#table-body').append(row2);
                            }

                            m++;
                            nRows++;
                            
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
                            row.append('<td><a href="dettagliOperatore.php?sigla=' + record.sigla + '&data=' + record.data_turno + '&codCiclo=' + record.codice_ciclo + '" target="_blank">' + record.sigla + '</a></td>');
                            row.append('<td>' + record.tempo_ciclo + '</td>');
                            row.append('<td>' + record.pzPossibiliDaRealizzareOra + '</td>');
                            row.append('<td>' + record.pzObiettivo + '</td>');
                            row.append('<td>' + record.sommaTotPzRealizzati + '</td>');
                            row.append('<td>' + record.totPzBuoni + '</td>');
                            row.append('<td>' + record.totPzScarti + '</td>');
                            row.append('<td>' + record.efficienza + '%</td>');
                            row.append('<td>' + record.qualita + '%</td>');
                            row.append('</tr>');

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
                        }).buttons().container().appendTo('#data-table_wrapper .col-md-6:eq(0)');
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

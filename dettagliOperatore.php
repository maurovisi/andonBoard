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

$siglaOperatore = isset($_GET['sigla']) ? $_GET['sigla'] : NULL;
$dataTurno      = isset($_GET['data']) ? $_GET['data'] : NULL;
$codiceCiclo    = isset($_GET['codCiclo']) ? $_GET['codCiclo'] : NULL;

/* parte relativa ai dettagli selezionati */
$query = "SELECT o.sigla, c.codice_ciclo, c.tempo_ciclo, c.pzDaRealizzare, a.id_risorsa, r.risorsa, a.id_operatore, a.id_ciclo, a.num_pz_ora, a.num_pz_realizzati, a.orario, a.num_pz_scarti, a.pranzo, a.note, a.data_turno
          FROM andon_board AS a
            INNER JOIN cicli AS c ON a.id_ciclo = c.id_ciclo
            INNER JOIN operatori AS o ON a.id_operatore = o.id
            INNER JOIN risorse AS r ON a.id_risorsa = r.id
          WHERE o.sigla = :siglaOperatore AND a.data_turno = :dataTurno AND c.codice_ciclo = :codiceCiclo
            ORDER BY a.orario";

// Preparazione della query
$stmt = $pdo->prepare($query);

// Binding dei parametri
$stmt->bindParam(':siglaOperatore', $siglaOperatore);
$stmt->bindParam(':dataTurno', $dataTurno);
$stmt->bindParam(':codiceCiclo', $codiceCiclo);

// Esecuzione della query
$stmt->execute();

// Processamento del risultato
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sumPzBuoni = 0;
$sumPzScarti = 0;
$efficienza = 0;
$qualita = 0;
$tempoLavorazioneUtile = 0;
$pzMax = 0;

$numRecord = count($result);

if ($numRecord > 0) {    
    foreach ($result as $record) {
        $sumPzBuoni += $record['num_pz_realizzati'];
        $sumPzScarti += $record['num_pz_scarti'];
        $tempoLavorazioneUtile = $record['pranzo'] === NULL ? 3600 : 1800;
        $pzMax += $tempoLavorazioneUtile / $record['tempo_ciclo'];
    }
        
    $efficienza = round(((100/$pzMax)*$sumPzBuoni),2,PHP_ROUND_HALF_UP);
    
    if ($sumPzBuoni != 0) {
        $qualita = round((100 - ($sumPzScarti / $sumPzBuoni) * 100),2,PHP_ROUND_HALF_UP);
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <meta name="description" content="Analisi dettagliata">
    <meta name="author" content="Mauro Visintin"/>
    <meta name="copyright" content="Mauro Visintin"/>

    <title>Andon Board | Data analysis</title>

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

</head>
<body class="bk">
    <div class="container">
        <h1 class="mt-4 text-center">Analisi dati specifici</h1>

        <h5>Efficienza Totale: <span class="fw-bold" id="efficienza-totale"><?= $efficienza ?>%</span></h5>
        <h5 class="mb-5">Qualità Totale: <span class="fw-bold" id="qualita-totale"><?= $qualita ?>%</span></h5>

        <!-- Tabella dei dati -->
        <div class="table-responsive mt-4">
            <table id="data-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Data Turno</th>
                        <th>Orario</th>
                        <th>Sigla Operatore</th>
                        <th>Codice Ciclo</th>
                        <th>Tempo Ciclo</th>
                        <th>Pezzi da Realizzare</th>
                        <th>Risorsa</th>
                        <th>Pezzi per Ora</th>
                        <th>Pezzi Realizzati</th>                        
                        <th>Pezzi Scarti</th>
                        <th>Pranzo</th>
                        <th>Note</th>                        
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $record) : ?>
                        <tr>
                            <td><?php echo $record['data_turno']; ?></td>
                            <td><?php echo $record['orario']; ?></td>
                            <td><?php echo $record['sigla']; ?></td>
                            <td><?php echo $record['codice_ciclo']; ?></td>
                            <td><?php echo $record['tempo_ciclo']; ?></td>
                            <td><?php echo $record['pzDaRealizzare']; ?></td>
                            <td><?php echo $record['risorsa']; ?></td>
                            <td><?php echo $record['num_pz_ora']; ?></td>
                            <td><?php echo $record['num_pz_realizzati']; ?></td>                            
                            <td><?php echo $record['num_pz_scarti']; ?></td>
                            <td><?php echo $record['pranzo'] !== NULL ? 'YES' : '-'; ?></td>
                            <td><?php echo $record['note'] == '' ? '-' : $record['note']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Script JS -->
        <script>
            $(document).ready(function() {
                $('#data-table').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print',
                        {
                            extend: 'colvis',
                            columns: ':not(:first-child)'
                        }
                    ],
                    order: [[9, 'asc']],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Italian.json'
                    },
                    pageLength: 50,
                    lengthMenu: [50, 100, 250, -1],
                });
            });
        </script>
    </div>
</body>
</html>

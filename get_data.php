<?php

// Recupero i parametri tramite POST
$efficiency = isset($_POST['efficiency']) ? $_POST['efficiency'] : NULL;
$what       = isset($_POST['what']) ? $_POST['what'] : NULL;
$resources  = isset($_POST['resources']) ? $_POST['resources'] : NULL;

// Includo il file per la configurazione del database
require_once "db_config.php";

// Creo un nuovo oggetto PDO
$pdo = new PDO($dsn, $username, $password, $options);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Sanitizza i dati di input
$efficiency = htmlspecialchars(trim($efficiency));
$what = htmlspecialchars(trim($what));
$resources = htmlspecialchars(trim($resources));

// Calcolo la data odierna e altre
$dataOdierna = date("Y-m-d");

$dataSetteGgFa = strtotime('-7 day', strtotime($dataOdierna));
$dataSetteGgFa = date('Y-m-d', $dataSetteGgFa);

$dataUnMeseFa  = strtotime('-1 month', strtotime($dataOdierna));
$dataUnMeseFa  = date('Y-m-d', $dataUnMeseFa);

$dataOdierna2 = new DateTime();
$dataOdierna2->sub(new DateInterval('P1Y'));
$dataUnAnnoPrima = $dataOdierna2->format('Y-m-d');

$efficienza = 0;
$qualita = 0;
$queryEfficiency = "";

if ($efficiency = "settimanale" && $what = "risorsa") {

    $queryEfficiency = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo, ab.orario, ab.data_turno
                        FROM
                            andon_board AS ab
                            INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                            INNER JOIN risorse ON ab.id_risorsa = risorse.id
                        WHERE
                            ab.data_turno BETWEEN '".$dataSetteGgFa."' AND '".$dataOdierna."' AND risorsa = '".$risorsa."' ";

} elseif ($efficiency = "mensile" && $what = "risorsa") {
    # code...
} elseif ($efficiency = "annuale" && $what = "risorsa") {
    # code...
} elseif ($efficiency = "tutto" && $what = "risorsa") {
    # code...
} elseif ($efficiency = "settimanale" && $what = "operatore") {
    # code...
} elseif ($efficiency = "mensile" && $what = "operatore") {
    # code...
} elseif ($efficiency = "annuale" && $what = "operatore") {
    # code...
} elseif ($efficiency = "tutto" && $what = "operatore") {
    # code...
} else {
    // errore

}

$stmt = $pdo->query($queryEfficiency);
$dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sumPzBuoni = 0;
$sumPzScarti = 0;
$efficienzaTot = 0;
$qualitaTot = 0;
$details = [];

foreach ($dati as $record) {
    $sumPzBuoni += $record['num_pz_realizzati'];
    $sumPzScarti += $record['num_pz_scarti'];
}

// Calcolo efficienza e qualità totali
if($sumPzBuoni + $sumPzScarti > 0){
    $efficienzaTot = ($sumPzBuoni / ($sumPzBuoni + $sumPzScarti)) * 100;
    $qualitaTot = ($sumPzBuoni / ($sumPzBuoni + $sumPzScarti)) * 100;
}

$numRecord = count($dati);

// calcolo efficienza e qualità per ogni gg, includo anche tutte le righe dei record suddivisi per turno e gg
   
if ($numRecord > 0) {    
    foreach ($dati as $record) {
        $tempoCiclo = $record['tempo_ciclo'];        

        if ($record['pranzo'] === NULL) {
            $efficienza_record = ($record['num_pz_realizzati'] * 100 / (3600 / $tempoCiclo));
        } else {
            $efficienza_record = ($record['num_pz_realizzati'] * 100 / (1800 / $tempoCiclo));
        }

        // Calcolo la qualità del record corrente
        $qualita_record = ($record['num_pz_realizzati'] / ($record['num_pz_realizzati'] + $record['num_pz_scarti'])) * 100;

        // Calcolo l'indice del turno (0 = 6-14, 1 = 14-22, 2 = 22-6)
        $indice_turno = 0;
        $orario = explode("-", $record['orario']);
        $ora_inizio = intval($orario[0]);
        $ora_fine = intval($orario[1]);

        if ($ora_inizio >= 14 && $ora_fine < 22) {
            $indice_turno = 1;
        } elseif ($ora_inizio >= 22 || $ora_fine < 6) {
            $indice_turno = 2;
        }

        // Creo un array con i dettagli per ogni giorno e turno
        $details[$record['data_turno']][$indice_turno] = [
            'orario' => $record['orario'],
            'efficienza' => $efficienza_record,
            'qualita' => $qualita_record,
            // Aggiungi qui altri campi se necessario
        ];
    }

    // Calcolo l'efficienza e la qualità totali per ogni giorno
    foreach ($details as $data => &$turni) {
        foreach ($turni as $indice => &$turno) {
            if (isset($turni[$indice - 1])) {
                $turno['efficienza_totale'] = $turno['efficienza'] + $turni[$indice - 1]['efficienza_totale'];
                $turno['qualita_totale'] = $turno['qualita'] + $turni[$indice - 1]['qualita_totale'];
            } else {
                $turno['efficienza_totale'] = $turno['efficienza'];
                $turno['qualita_totale'] = $turno['qualita'];
            }
        }
    }

    // Calcolo l'efficienza e la qualità totali
    $efficienzaTot = 0;
    $qualitaTot = 0;
    foreach ($details as $data => &$turni) {
        $ultimo_turno = end($turni);
        $efficienzaTot += $ultimo_turno['efficienza_totale'];
        $qualitaTot += $ultimo_turno['qualita_totale'];
    }

    // Calcolo la media delle efficienze e qualità totali
    $efficienzaTot /= count($details);
    $qualitaTot /= count($details);

    // Arrotondamento dei valori
    $efficienzaTot = round($efficienzaTot, 2);
    $qualitaTot = round($qualitaTot, 2);
}

echo json_encode([
    'data' => $result,
    'efficienzaTot' => $efficienzaTot,
    'qualitaTot' => $qualitaTot,
    'dettagli' => $details,
]);

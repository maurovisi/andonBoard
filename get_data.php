<?php

// Recupero i parametri tramite POST
$efficiency = isset($_POST['efficiency']) ? $_POST['efficiency'] : NULL;
$what       = isset($_POST['what']) ? $_POST['what'] : NULL;
$resources  = isset($_POST['resources']) ? $_POST['resources'] : NULL;

// Includo il file per la configurazione del database
require_once "db_config.php";

try {
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

    if ($efficiency == "settimanale" && $what == "risorsa") {

        $queryEfficiency = "SELECT SUM(ab.num_pz_realizzati) AS totPzRealizzati, SUM(ab.num_pz_scarti) AS totPzScarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, cicli.codice_ciclo, ab.data_turno, operatori.sigla, COUNT(*) AS oreLavoro
                            FROM
                                andon_board AS ab
                                INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                INNER JOIN operatori ON ab.id_operatore = operatori.id
                            WHERE
                                ab.data_turno BETWEEN :dataSetteGgFa AND :dataOdierna AND risorsa = :resources
                            GROUP BY ab.data_turno, cicli.codice_ciclo, operatori.sigla ";

        $stmt = $pdo->prepare($queryEfficiency);
        $stmt->bindParam(':dataSetteGgFa', $dataSetteGgFa);
        $stmt->bindParam(':dataOdierna', $dataOdierna);
        $stmt->bindParam(':resources', $resources);

    } elseif ($efficiency == "mensile" && $what == "risorsa") {
        // ...
    } elseif ($efficiency == "annuale" && $what == "risorsa") {
        // ...
    } elseif ($efficiency == "tutto" && $what == "risorsa") {
        // ...
    } elseif ($efficiency == "settimanale" && $what == "operatore") {
        // ...
    } elseif ($efficiency == "mensile" && $what == "operatore") {
        // ...
    } elseif ($efficiency == "annuale" && $what == "operatore") {
        // ...
    } elseif ($efficiency == "tutto" && $what == "operatore") {
        // ...
    } else {
        // errore
    }

    $stmt->execute();
    $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tempoMacchinaAccesaTot = 0;
    $tempoProduzioneTot = 0;
    $tempoProduzioneScartiTot = 0;
    $tempoUtileSett_01 = 122400;
    $sumPzBuoni = 0;
    $sumPzScarti = 0;
    $usoRisorsaTot = 0;
    $efficienzaTot = 0;
    $qualitaTot = 0;

    foreach ($dati as $record) {
        $tempoCiclo = intval($record['tempo_ciclo']);
        $tempoProduzioneTot += intval($record['totPzRealizzati']) * $tempoCiclo;
        $tempoProduzioneScartiTot += intval($record['totPzScarti']) * $tempoCiclo;
        $tempoMacchinaAccesaTot += intval($record['oreLavoro']) * 60;
    }
    
    // Calcolo efficienza e qualità totali
    if($tempoProduzioneTot + $tempoProduzioneScartiTot > 0){
        if ($efficiency == "settimanale") {
            $tempoMacchinaAccesaTot = $tempoMacchinaAccesaTot - (((30*60)*3*5)+(30*60)); // sottraggo i secondi di pausa pranzo
            $usoRisorsaTot = round(((100 / $tempoUtileSett_01) * $tempoProduzioneTot),2,PHP_ROUND_HALF_UP);
            $efficienzaTot = round(((100 / $tempoMacchinaAccesaTot) * $tempoProduzioneTot),2,PHP_ROUND_HALF_UP);
            $qualitaTot = round(($tempoProduzioneTot / ($tempoProduzioneTot + $tempoProduzioneScartiTot) * 100 ),2,PHP_ROUND_HALF_UP);
        }
    }

    $numRecord = count($dati);

    // calcolo efficienza e qualità per ogni gg, includo anche tutte le righe dei record suddivisi per turno e gg

    $n = 0;
    $details = [];

    if ($numRecord > 0) {
        foreach ($dati as $record) { 
            $tempoCiclo = intval($record['tempo_ciclo']);
            $totPzRealizzati = intval($record['totPzRealizzati']);
            $totPzScarti = intval($record['totPzScarti']);
            $efficienza_record = 0;
            $qualita_record = 0;

            if ($tempoCiclo != 0) {
                $efficienza_record = ($totPzRealizzati * 100 / (27000 / $tempoCiclo));
            } else {
                $tempoCiclo = "ERRORE";
            }
            
            if ($totPzRealizzati != 0) {
                $qualita_record = ($totPzRealizzati / ($totPzRealizzati + $totPzScarti)) * 100;
            }
            
            // Creo un array con i dettagli per ogni giorno e turno            
            $details[$n] = [
                'totPzRealizzati' => $record['totPzRealizzati'],
                'totPzScarti' => $record['totPzScarti'],
                'tempo_ciclo' => $tempoCiclo,
                'pzDaRealizzare' => $record['pzDaRealizzare'],
                'codice_ciclo' => $record['codice_ciclo'],
                'data_turno' => $record['data_turno'],
                'sigla' => $record['sigla'],
                'efficienza' => $efficienza_record,
                'qualita' => $qualita_record,
            ];
            
            $n++;
        }
    }

    echo json_encode([
        'usoRisorsaTot' => $usoRisorsaTot,
        'efficienzaTot' => $efficienzaTot,
        'qualitaTot' => $qualitaTot,
        'records' => $details,
    ]);

} catch (PDOException $e) {
    // Gestione dell'eccezione
    echo "Errore: " . $e->getMessage();
}
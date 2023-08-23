<?php

// Recupero i parametri tramite POST
$efficiency = isset($_POST['efficiency']) ? $_POST['efficiency'] : NULL;
$what       = isset($_POST['what']) ? $_POST['what'] : NULL;
$resources  = isset($_POST['resources']) ? $_POST['resources'] : NULL;
$start      = isset($_POST['start']) ? $_POST['start'] : NULL;
$year       = isset($_POST['year']) ? $_POST['year'] : NULL;

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

    $startDate = '';
    $endDate = '';

    // Calcola la data di inizio e la data di fine in base all'intervallo e all'anno selezionati
    if ($efficiency != "settimanale" || $efficiency != "tutto") {
        switch ($efficiency) {
            case 'mensile':
                $startDate = $year . '-' . $start . '-01';
                $endDate = date('Y-m-t', strtotime($startDate));
                break;
            case 'trimestrale':
                $startDate = $year . '-' . $start . '-01';
                $endMonth = date('m', strtotime($startDate)) + 2;
                $endDate = date('Y-m-t', strtotime($year . '-' . $endMonth . '-01'));
                break;
            case 'semestrale':
                $startDate = $year . '-' . $start . '-01';
                $endMonth = date('m', strtotime($startDate)) + 5;
                $endDate = date('Y-m-t', strtotime($year . '-' . $endMonth . '-01'));
                break;
            case 'annuale':
                $startDate = $year . '-' . $start . '-01';
                $endDate = date('Y-m-t', strtotime($year . '-12-01'));
                break;
            default:
                // Durata non gestita
                break;
        }
    }    

    $efficienza = 0;
    $qualita = 0;
    $queryEfficiency = "";

    if ($efficiency == "settimanale" && $what == "risorsa") {

        $queryEfficiency = "SELECT SUM(ab.num_pz_realizzati) AS totPzRealizzati, SUM(ab.num_pz_scarti) AS totPzScarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, cicli.codice_ciclo, ab.data_turno, operatori.sigla, SUM(ab.pranzo) AS pranzo, COUNT(*) AS oreLavoro
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

    } elseif (($efficiency == "mensile" || $efficiency == "trimestrale" || $efficiency == "semestrale" || $efficiency == "annuale") && $what == "risorsa") {

        $queryEfficiency = "SELECT SUM(ab.num_pz_realizzati) AS totPzRealizzati, SUM(ab.num_pz_scarti) AS totPzScarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, cicli.codice_ciclo, ab.data_turno, operatori.sigla, SUM(ab.pranzo) AS pranzo, COUNT(*) AS oreLavoro
                            FROM
                                andon_board AS ab
                                INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                INNER JOIN operatori ON ab.id_operatore = operatori.id
                            WHERE
                                ab.data_turno BETWEEN :dataStart AND :dataEnd AND risorsa = :resources
                                GROUP BY ab.data_turno, cicli.codice_ciclo, operatori.sigla ";

        $stmt = $pdo->prepare($queryEfficiency);
        $stmt->bindParam(':dataStart', $startDate);
        $stmt->bindParam(':dataEnd', $endDate);
        $stmt->bindParam(':resources', $resources);

    } elseif ($efficiency == "tutto" && $what == "risorsa") {
        
        $queryEfficiency = "SELECT SUM(ab.num_pz_realizzati) AS totPzRealizzati, SUM(ab.num_pz_scarti) AS totPzScarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, cicli.codice_ciclo, ab.data_turno, operatori.sigla, SUM(ab.pranzo) AS pranzo, COUNT(*) AS oreLavoro
                            FROM
                                andon_board AS ab
                                INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                INNER JOIN operatori ON ab.id_operatore = operatori.id
                            WHERE
                                risorsa = :resources
                                GROUP BY ab.data_turno, cicli.codice_ciclo, operatori.sigla ";

        $stmt = $pdo->prepare($queryEfficiency);
        $stmt->bindParam(':resources', $resources);

    } elseif ($efficiency == "settimanale" && $what == "operatore") {
        
        $queryEfficiency = "SELECT SUM(ab.num_pz_realizzati) AS totPzRealizzati, SUM(ab.num_pz_scarti) AS totPzScarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, cicli.codice_ciclo, ab.data_turno, operatori.sigla, SUM(ab.pranzo) AS pranzo, COUNT(*) AS oreLavoro
                            FROM
                                andon_board AS ab
                                INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                INNER JOIN operatori ON ab.id_operatore = operatori.id
                            WHERE
                                ab.data_turno BETWEEN :dataSetteGgFa AND :dataOdierna AND operatori.sigla = :operatore
                                GROUP BY ab.data_turno, cicli.codice_ciclo, risorsa ";

        $stmt = $pdo->prepare($queryEfficiency);
        $stmt->bindParam(':dataSetteGgFa', $dataSetteGgFa);
        $stmt->bindParam(':dataOdierna', $dataOdierna);
        $stmt->bindParam(':operatore', $resources);

    } elseif (($efficiency == "mensile" || $efficiency == "trimestrale" || $efficiency == "semestrale" || $efficiency == "annuale") && $what == "operatore") {
        
        $queryEfficiency = "SELECT SUM(ab.num_pz_realizzati) AS totPzRealizzati, SUM(ab.num_pz_scarti) AS totPzScarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, cicli.codice_ciclo, ab.data_turno, operatori.sigla, SUM(ab.pranzo) AS pranzo, COUNT(*) AS oreLavoro
                            FROM
                                andon_board AS ab
                                INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                INNER JOIN operatori ON ab.id_operatore = operatori.id
                            WHERE
                                ab.data_turno BETWEEN :dataStart AND :dataEnd AND operatori.sigla = :operatore
                                GROUP BY ab.data_turno, cicli.codice_ciclo, risorsa ";

        $stmt = $pdo->prepare($queryEfficiency);
        $stmt->bindParam(':dataStart', $startDate);
        $stmt->bindParam(':dataEnd', $endDate);
        $stmt->bindParam(':operatore', $resources);

    } elseif ($efficiency == "tutto" && $what == "operatore") {
        
        $queryEfficiency = "SELECT SUM(ab.num_pz_realizzati) AS totPzRealizzati, SUM(ab.num_pz_scarti) AS totPzScarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, cicli.codice_ciclo, ab.data_turno, operatori.sigla, SUM(ab.pranzo) AS pranzo, COUNT(*) AS oreLavoro
                            FROM
                                andon_board AS ab
                                INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                INNER JOIN operatori ON ab.id_operatore = operatori.id
                            WHERE
                                operatori.sigla = :operatore
                                GROUP BY ab.data_turno, cicli.codice_ciclo, risorsa ";

        $stmt = $pdo->prepare($queryEfficiency);
        $stmt->bindParam(':operatore', $resources);


    } else {
        // errore
        echo json_encode([
            'usoRisorsaTot' => "ERRORE",
            'efficienzaTot' => "ERRORE",
            'qualitaTot' => "ERRORE",
            'efficienzaTotaleR' => "ERRORE",
            'totPzPossibiliDaRealizzare' => "ERRORE",
            'pzBuoniRealizzati' => "ERRORE",
            'totalePezziRealizzati' => "ERRORE",
            'pzScartiRealizzati' => "ERRORE",
            'records' => "ERRORE",
        ]);
        die;
    }

    $stmt->execute();
    $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tempoMacchinaAccesaTot = 0;
    $tempoProduzioneTot = 0;
    $tempoProduzioneScartiTot = 0;
    $tempoUtileSett_01 = ($resources=="012" || $resources=="015" || $resources=="023" || $resources=="999") ? 144000 : 446400; // se le macchine sono a Romans il tempo è diverso perchè lavorano max 8 ore x 5 gg a settimana
    $sumPzBuoni = 0;
    $sumPzScarti = 0;
    $usoRisorsaTot = 0;
    $efficienzaTot = 0;
    $efficienzaTotaleR = 0;
    $qualitaTot = 0;
    $totPzPossibiliDaRealizzare = 0;
    $AllTotPzPossibiliDaRealizzare = 0;
    $pzBuoniRealizzati = 0;
    $totalePezziRealizzati = 0;
    $pzScartiRealizzati = 0;
    $pzObiettivo = 0;
    $sommaTotPzRealizzati = 0;

    $tempoLavorazioneUtile = 0;
    $pzMax = 0;

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
                $orarioDiLavoro = ($resources=="012" || $resources=="015" || $resources=="023" || $resources=="999" || $resources=="GS" || $resources=="SM") ? 28800 : 27000;
                $pzMax = $orarioDiLavoro / $tempoCiclo;
                $efficienza_record = (100/$pzMax)*$totPzRealizzati;
                $efficienzaTot += $efficienza_record;
                $totPzPossibiliDaRealizzare = round(3600/$tempoCiclo, 1); 
                $AllTotPzPossibiliDaRealizzare += $totPzPossibiliDaRealizzare;
            } else {
                $tempoCiclo = "ERRORE";
                $totPzPossibiliDaRealizzare = "ERRORE";
            }
            
            if ($totPzRealizzati != 0 || $totPzScarti != 0) {
                //$qualita_record = ($totPzRealizzati / ($totPzRealizzati + $totPzScarti)) * 100;
                $qualita_record = 100 - ($totPzScarti / ($totPzRealizzati + $totPzScarti)) * 100;
                //$qualita_record = (100/($totPzRealizzati + $totPzScarti))*$totPzRealizzati;
                $qualitaTot += $qualita_record;
                $usoRisorsaTot += ($totPzRealizzati + $totPzScarti) * $tempoCiclo;
            }

            $pzBuoniRealizzati += $totPzRealizzati;
            $totalePezziRealizzati += ($totPzRealizzati + $totPzScarti);
            $pzScartiRealizzati += $totPzScarti;

            if ($resources=="012" || $resources=="015" || $resources=="023" || $resources=="999" || $resources=="GS" || $resources=="SM") {
                $pzObiettivo = 3600*$record['oreLavoro']/$tempoCiclo; //$pzObiettivo = $totPzPossibiliDaRealizzare * $record['oreLavoro'];
            } else {
                if ($record['pranzo'] === NULL) {
                    $pzObiettivo = 3600*$record['oreLavoro']/$tempoCiclo; //$pzObiettivo = $totPzPossibiliDaRealizzare * $record['oreLavoro'];
                } else {
                    $pzObiettivo = (3600*($record['oreLavoro']-1)+1800)/$tempoCiclo; //$pzObiettivo = ($totPzPossibiliDaRealizzare * ($record['oreLavoro'] - 1)) + ($totPzPossibiliDaRealizzare / 2);
                }
            }
            
            $sommaTotPzRealizzati = $totPzRealizzati + $totPzScarti;
            
            // Creo un array con i dettagli per ogni giorno e turno            
            $details[$n] = [
                'pzObiettivo' => $pzObiettivo,
                'sommaTotPzRealizzati' => $sommaTotPzRealizzati,
                'totPzRealizzati' => $record['totPzRealizzati'],
                'totPzBuoni' => $record['totPzRealizzati'],
                'totPzScarti' => $record['totPzScarti'],
                'tempo_ciclo' => $tempoCiclo,
                'pzDaRealizzare' => $totPzPossibiliDaRealizzare,
                'codice_ciclo' => $record['codice_ciclo'],
                'data_turno' => $record['data_turno'],
                'sigla' => $record['sigla'],
                'efficienza' => $efficienza_record,
                'qualita' => $qualita_record,
            ];
            
            $n++;
        }

        // calcolo tempo utile max in base all'intervallo di tempo scelto dal form
        switch ($efficiency) {
            case 'settimanale':
                $tempoUtileSett_01 = $tempoUtileSett_01;
                break;
            case 'mensile':
                $tempoUtileSett_01 *= 4;
                break;
            case 'trimestrale':
                $tempoUtileSett_01 *= 4*3;
                break;
            case 'semestrale':
                $tempoUtileSett_01 *= 4*6;
                break;
            case 'annuale':
                $tempoUtileSett_01 *= 4*12;
                break;
            default:
                // Durata non gestita
                $tempoUtileSett_01 = "errore";
                break;
        }

        $efficienzaTot = round(($efficienzaTot / $numRecord),2,PHP_ROUND_HALF_UP);
        //$efficienzaTotaleR = round(((100/$totalePezziRealizzati)*$pzBuoniRealizzati),2,PHP_ROUND_HALF_UP);
        //$efficienzaTotaleR = round((($totalePezziRealizzati/$AllTotPzPossibiliDaRealizzare)*100),2,PHP_ROUND_HALF_UP);
        //$qualitaTot = round(($qualitaTot / $numRecord),2,PHP_ROUND_HALF_UP);
        $qualitaTot  = round((($pzBuoniRealizzati/$totalePezziRealizzati)*100),2,PHP_ROUND_HALF_UP);
        
        if ($tempoUtileSett_01 != "errore") {
            $usoRisorsaTot = round(((100 / $tempoUtileSett_01) * $usoRisorsaTot),2,PHP_ROUND_HALF_UP);
        } else {
            $usoRisorsaTot = "errore";
        }
        
    }

    echo json_encode([
        'usoRisorsaTot' => $usoRisorsaTot,
        'efficienzaTot' => $efficienzaTot,
        'efficienzaTotaleR' => $efficienzaTotaleR,
        'qualitaTot' => $qualitaTot,
        'totPzPossibiliDaRealizzare' => $totPzPossibiliDaRealizzare,
        'pzBuoniRealizzati' => "$pzBuoniRealizzati",
        'totalePezziRealizzati' => "$totalePezziRealizzati",
        'pzScartiRealizzati' => "$pzScartiRealizzati",
        'records' => $details,
    ]);

} catch (PDOException $e) {
    // Gestione dell'eccezione
    echo "Errore: " . $e->getMessage();
}
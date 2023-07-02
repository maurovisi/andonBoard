<?php
$risorsa = $_GET['risorsa'];

// Connessione al database
require_once "db_config.php";
$dataOdierna = date("Y-m-d");
$dataPrec = strtotime ( '-1 day' , strtotime ( $dataOdierna ) ); 
$dataPrec = date ( 'Y-m-d' , $dataPrec );

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ottenere l'ora corrente come oggetto DateTime
    $oraCorrente = new DateTime();

    // Ottenere l'ora corrente come stringa nel formato 'H:i'
    $oraCorrenteStringa = $oraCorrente->format('H:i');

    // Estrarre solo l'ora dall'oggetto DateTime
    $oraCorrenteOre = $oraCorrente->format('H');


    // variabili globali
    $turnoMattino    = array();
    $turnoPomeriggio = array();
    $turnoNotte      = array();

    $efficienzaMattino = 0;
    $qualitaMattino = 0;
    $sumPzBuoniMattino = 0;
    $sumPzScartiMattino = 0;
    $sumPzObiettivoMattino = 0;

    $efficienzaPomeriggio = 0;
    $qualitaPomeriggio = 0;
    $sumPzBuoniPomeriggio = 0;
    $sumPzScartiPomeriggio = 0;
    $sumPzObiettivoPomeriggio = 0;

    $efficienzaNotte = 0;
    $qualitaNotte = 0;
    $sumPzBuoniNotte = 0;
    $sumPzScartiNotte = 0;
    $sumPzObiettivoNotte = 0;


    // Controllare se l'ora corrente rientra tra le 24:00 e le 6:00
    if ($oraCorrenteOre >= '00' && $oraCorrenteOre < '06') {
        // Se siamo tra le 24:00 e le 6:00, esegui queste azioni

        $queryTurnoMattino = "SELECT orario, num_pz_ora, num_pz_realizzati, num_pz_scarti, risorsa, sigla, tempo_ciclo, data_turno, andon_board.note, pranzo
                              FROM andon_board 
                              INNER JOIN cicli ON andon_board.id_ciclo = cicli.id_ciclo                          
                              INNER JOIN operatori ON andon_board.id_operatore = operatori.id
                              INNER JOIN risorse ON andon_board.id_risorsa = risorse.id
                              WHERE data_turno = '".$dataPrec."' AND risorsa = '".$risorsa."' AND (orario='6-7' OR orario='7-8' OR orario='8-9' OR orario='9-10' OR orario='10-11' OR orario='11-12' OR orario='12-13' OR orario='13-14') 
                              ORDER BY andon_board.created_at";

        $queryTurnoPome    = "SELECT orario, num_pz_ora, num_pz_realizzati, num_pz_scarti, risorsa, sigla, tempo_ciclo, data_turno, andon_board.note
                              FROM andon_board 
                              INNER JOIN cicli ON andon_board.id_ciclo = cicli.id_ciclo                          
                              INNER JOIN operatori ON andon_board.id_operatore = operatori.id
                              INNER JOIN risorse ON andon_board.id_risorsa = risorse.id
                              WHERE data_turno = '".$dataPrec."' AND risorsa = '".$risorsa."' AND (orario='14-15' OR orario='15-16' OR orario='16-17' OR orario='17-18' OR orario='18-19' OR orario='19-20' OR orario='20-21' OR orario='21-22') 
                              ORDER BY andon_board.created_at";

        $queryTurnoNotte   = "SELECT orario, num_pz_ora, num_pz_realizzati, num_pz_scarti, risorsa, sigla, tempo_ciclo, data_turno, andon_board.note
                              FROM andon_board 
                              INNER JOIN cicli ON andon_board.id_ciclo = cicli.id_ciclo                          
                              INNER JOIN operatori ON andon_board.id_operatore = operatori.id
                              INNER JOIN risorse ON andon_board.id_risorsa = risorse.id
                              WHERE risorsa = '".$risorsa."' AND ((data_turno = '".$dataPrec."' AND (orario='22-23' OR orario='23-24')) OR (data_turno = '".$dataOdierna."' AND (orario='24-1' OR orario='1-2' OR orario='2-3' OR orario='3-4' OR orario='4-5' OR orario='5-6'))) 
                              ORDER BY andon_board.created_at";

        
        foreach ($pdo->query($queryTurnoMattino) as $row) {
            array_push($turnoMattino, array("orario" => $row['orario'], "num_pz_ora" => $row['num_pz_ora'], "num_pz_realizzati" => $row['num_pz_realizzati'], "num_pz_scarti" => $row['num_pz_scarti'], "risorsa" => $row['risorsa'], "sigla" => $row['sigla'], "tempo_ciclo" => $row['tempo_ciclo'], "data_turno" => $row['data_turno'], "note" => $row['note'], "pranzo" => $row['pranzo']));
        }

        foreach ($pdo->query($queryTurnoPome) as $row) {
            array_push($turnoPomeriggio, array("orario" => $row['orario'], "num_pz_ora" => $row['num_pz_ora'], "num_pz_realizzati" => $row['num_pz_realizzati'], "num_pz_scarti" => $row['num_pz_scarti'], "risorsa" => $row['risorsa'], "sigla" => $row['sigla'], "tempo_ciclo" => $row['tempo_ciclo'], "data_turno" => $row['data_turno'], "note" => $row['note']));
        }

        foreach ($pdo->query($queryTurnoNotte) as $row) {
            array_push($turnoNotte, array("orario" => $row['orario'], "num_pz_ora" => $row['num_pz_ora'], "num_pz_realizzati" => $row['num_pz_realizzati'], "num_pz_scarti" => $row['num_pz_scarti'], "risorsa" => $row['risorsa'], "sigla" => $row['sigla'], "tempo_ciclo" => $row['tempo_ciclo'], "data_turno" => $row['data_turno'], "note" => $row['note']));
        }

        /* calcolo efficienza e qualità turno del mattino */ 
        $queryDati   = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                        FROM
                            andon_board AS ab
                            INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                            INNER JOIN risorse ON ab.id_risorsa = risorse.id
                        WHERE
                            ab.data_turno = '".$dataPrec."' AND
                            risorsa = '".$risorsa."' AND
                            (ab.orario='6-7' OR ab.orario='7-8' OR ab.orario='8-9' OR ab.orario='9-10' OR ab.orario='10-11' OR ab.orario='11-12' OR ab.orario='12-13' OR ab.orario='13-14')";
        
        $stmt = $pdo->query($queryDati);
        $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sumPzBuoniMattino = 0;
        $sumPzScartiMattino = 0;
        $efficienzaMattino = 0;
        $qualitaMattino = 0;
        $tempoLavorazioneUtileMatt = 0;
        $pzMaxMatt = 0;

        $numRecord = count($dati);
        
        if ($numRecord > 0) {    
            foreach ($dati as $record) {
                $sumPzBuoniMattino += $record['num_pz_realizzati'];
                $sumPzScartiMattino += $record['num_pz_scarti'];
                $tempoLavorazioneUtileMatt = $record['pranzo'] === NULL ? 3600 : 1800;
                $pzMaxMatt += $tempoLavorazioneUtileMatt / $record['tempo_ciclo'];               
            }

            $efficienzaMattino = round(((100/$pzMaxMatt)*$sumPzBuoniMattino),2,PHP_ROUND_HALF_UP);
            
            if ($sumPzBuoniMattino != 0) {
                $qualitaMattino = round((100 - ($sumPzScartiMattino / $sumPzBuoniMattino) * 100),2,PHP_ROUND_HALF_UP);
            } else {
                $qualitaMattino = 0;
            }
        }


        /* calcolo efficienza e qualità turno del pomeriggio */
        $queryDati   = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                        FROM
                            andon_board AS ab
                            INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                            INNER JOIN risorse ON ab.id_risorsa = risorse.id
                        WHERE
                            ab.data_turno = '".$dataPrec."' AND
                            risorsa = '".$risorsa."' AND
                            (ab.orario='14-15' OR ab.orario='15-16' OR ab.orario='16-17' OR ab.orario='17-18' OR ab.orario='18-19' OR ab.orario='19-20' OR ab.orario='20-21' OR ab.orario='21-22')";

        $stmt = $pdo->query($queryDati);
        $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sumPzBuoniPomeriggio = 0;
        $sumPzScartiPomeriggio = 0;
        $efficienzaPomeriggio = 0;
        $qualitaPomeriggio = 0;
        $tempoLavorazioneUtilePome = 0;
        $pzMaxPome = 0;

        $numRecord = count($dati);
        
        if ($numRecord > 0) {    
            foreach ($dati as $record) {
                $sumPzBuoniPomeriggio += $record['num_pz_realizzati'];
                $sumPzScartiPomeriggio += $record['num_pz_scarti'];
                $tempoLavorazioneUtilePome = $record['pranzo'] === NULL ? 3600 : 1800;
                $pzMaxPome += $tempoLavorazioneUtilePome / $record['tempo_ciclo'];               
            }

            $efficienzaPomeriggio = round(((100/$pzMaxPome)*$sumPzBuoniPomeriggio),2,PHP_ROUND_HALF_UP);
            
            if ($sumPzBuoniPomeriggio != 0) {
                $qualitaPomeriggio = round((100 - ($sumPzScartiPomeriggio / $sumPzBuoniPomeriggio) * 100),2,PHP_ROUND_HALF_UP);
            } else {
                $qualitaPomeriggio = 0;
            }
        }


        /* calcolo efficienza e qualità turno della notte */
        $queryDati   = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                        FROM
                            andon_board AS ab
                            INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                            INNER JOIN risorse ON ab.id_risorsa = risorse.id
                        WHERE
                            risorsa = '".$risorsa."' AND
                            ( (ab.data_turno = '".$dataPrec."' AND ab.orario = '22-23' ) OR (ab.data_turno = '".$dataPrec."' AND ab.orario = '23-24' ) OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '24-1') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '1-2') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '2-3') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '3-4') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '4-5') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '5-6') )";

        $stmt = $pdo->query($queryDati);
        $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sumPzBuoniNotte = 0;
        $sumPzScartiNotte = 0;
        $efficienzaNotte = 0;
        $qualitaNotte = 0;
        $tempoLavorazioneUtileNotte = 0;
        $pzMaxNotte = 0;

        $numRecord = count($dati);
        
        if ($numRecord > 0) {    
            foreach ($dati as $record) {
                $sumPzBuoniNotte += $record['num_pz_realizzati'];
                $sumPzScartiNotte += $record['num_pz_scarti'];
                $tempoLavorazioneUtileNotte = $record['pranzo'] === NULL ? 3600 : 1800;
                $pzMaxNotte += $tempoLavorazioneUtileNotte / $record['tempo_ciclo'];               
            }

            $efficienzaNotte = round(((100/$pzMaxNotte)*$sumPzBuoniNotte),2,PHP_ROUND_HALF_UP);
            
            if ($sumPzBuoniNotte != 0) {
                $qualitaNotte = round((100 - ($sumPzScartiNotte / $sumPzBuoniNotte) * 100),2,PHP_ROUND_HALF_UP);
            } else {
                $qualitaNotte = 0;
            }
        }        
        
    } else {

        $queryTurnoMattino = "SELECT orario, num_pz_ora, num_pz_realizzati, num_pz_scarti, risorsa, sigla, tempo_ciclo, data_turno, andon_board.note, pranzo
                              FROM andon_board 
                              INNER JOIN cicli ON andon_board.id_ciclo = cicli.id_ciclo                          
                              INNER JOIN operatori ON andon_board.id_operatore = operatori.id
                              INNER JOIN risorse ON andon_board.id_risorsa = risorse.id
                              WHERE data_turno = '".$dataOdierna."' AND risorsa = '".$risorsa."' AND (orario='6-7' OR orario='7-8' OR orario='8-9' OR orario='9-10' OR orario='10-11' OR orario='11-12' OR orario='12-13' OR orario='13-14') 
                              ORDER BY andon_board.created_at";

        $queryTurnoPome    = "SELECT orario, num_pz_ora, num_pz_realizzati, num_pz_scarti, risorsa, sigla, tempo_ciclo, data_turno, andon_board.note
                              FROM andon_board 
                              INNER JOIN cicli ON andon_board.id_ciclo = cicli.id_ciclo                          
                              INNER JOIN operatori ON andon_board.id_operatore = operatori.id
                              INNER JOIN risorse ON andon_board.id_risorsa = risorse.id
                              WHERE data_turno = '".$dataOdierna."' AND risorsa = '".$risorsa."' AND (orario='14-15' OR orario='15-16' OR orario='16-17' OR orario='17-18' OR orario='18-19' OR orario='19-20' OR orario='20-21' OR orario='21-22') 
                              ORDER BY andon_board.created_at";

        $queryTurnoNotte   = "SELECT orario, num_pz_ora, num_pz_realizzati, num_pz_scarti, risorsa, sigla, tempo_ciclo, data_turno, andon_board.note
                              FROM andon_board 
                              INNER JOIN cicli ON andon_board.id_ciclo = cicli.id_ciclo                          
                              INNER JOIN operatori ON andon_board.id_operatore = operatori.id
                              INNER JOIN risorse ON andon_board.id_risorsa = risorse.id
                              WHERE risorsa = '".$risorsa."' AND data_turno = '".$dataOdierna."' AND (orario='22-23' OR orario='23-24') 
                              ORDER BY andon_board.created_at";

        
        foreach ($pdo->query($queryTurnoMattino) as $row) {
            array_push($turnoMattino, array("orario" => $row['orario'], "num_pz_ora" => $row['num_pz_ora'], "num_pz_realizzati" => $row['num_pz_realizzati'], "num_pz_scarti" => $row['num_pz_scarti'], "risorsa" => $row['risorsa'], "sigla" => $row['sigla'], "tempo_ciclo" => $row['tempo_ciclo'], "data_turno" => $row['data_turno'], "note" => $row['note'], "pranzo" => $row['pranzo']));
        }

        foreach ($pdo->query($queryTurnoPome) as $row) {
            array_push($turnoPomeriggio, array("orario" => $row['orario'], "num_pz_ora" => $row['num_pz_ora'], "num_pz_realizzati" => $row['num_pz_realizzati'], "num_pz_scarti" => $row['num_pz_scarti'], "risorsa" => $row['risorsa'], "sigla" => $row['sigla'], "tempo_ciclo" => $row['tempo_ciclo'], "data_turno" => $row['data_turno'], "note" => $row['note']));
        }

        foreach ($pdo->query($queryTurnoNotte) as $row) {
            array_push($turnoNotte, array("orario" => $row['orario'], "num_pz_ora" => $row['num_pz_ora'], "num_pz_realizzati" => $row['num_pz_realizzati'], "num_pz_scarti" => $row['num_pz_scarti'], "risorsa" => $row['risorsa'], "sigla" => $row['sigla'], "tempo_ciclo" => $row['tempo_ciclo'], "data_turno" => $row['data_turno'], "note" => $row['note']));
        }

        /* calcolo efficienza e qualità turno del mattino */  
        $queryDati   = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                        FROM
                            andon_board AS ab
                            INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                            INNER JOIN risorse ON ab.id_risorsa = risorse.id
                        WHERE
                            ab.data_turno = '".$dataOdierna."' AND
                            risorsa = '".$risorsa."' AND
                            (ab.orario='6-7' OR ab.orario='7-8' OR ab.orario='8-9' OR ab.orario='9-10' OR ab.orario='10-11' OR ab.orario='11-12' OR ab.orario='12-13' OR ab.orario='13-14')";

        $stmt = $pdo->query($queryDati);
        $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sumPzBuoniMattino = 0;
        $sumPzScartiMattino = 0;
        $efficienzaMattino = 0;
        $qualitaMattino = 0;
        $tempoLavorazioneUtileMatt = 0;
        $pzMaxMatt = 0;

        $numRecord = count($dati);
        
        if ($numRecord > 0) {    
            foreach ($dati as $record) {
                $sumPzBuoniMattino += $record['num_pz_realizzati'];
                $sumPzScartiMattino += $record['num_pz_scarti'];
                $tempoLavorazioneUtileMatt = $record['pranzo'] === NULL ? 3600 : 1800;
                $pzMaxMatt += $tempoLavorazioneUtileMatt / $record['tempo_ciclo'];               
            }

            $efficienzaMattino = round(((100/$pzMaxMatt)*$sumPzBuoniMattino),2,PHP_ROUND_HALF_UP);
            
            if ($sumPzBuoniMattino != 0) {
                $qualitaMattino = round((100 - ($sumPzScartiMattino / $sumPzBuoniMattino) * 100),2,PHP_ROUND_HALF_UP);
            } else {
                $qualitaMattino = 0;
            }
        }



        /* calcolo efficienza e qualità turno del pomeriggio */
        $queryDati   = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                        FROM
                            andon_board AS ab
                            INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                            INNER JOIN risorse ON ab.id_risorsa = risorse.id
                        WHERE
                            ab.data_turno = '".$dataOdierna."' AND
                            risorsa = '".$risorsa."' AND
                            (ab.orario='14-15' OR ab.orario='15-16' OR ab.orario='16-17' OR ab.orario='17-18' OR ab.orario='18-19' OR ab.orario='19-20' OR ab.orario='20-21' OR ab.orario='21-22')";

        $stmt = $pdo->query($queryDati);
        $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sumPzBuoniPomeriggio = 0;
        $sumPzScartiPomeriggio = 0;
        $efficienzaPomeriggio = 0;
        $qualitaPomeriggio = 0;
        $tempoLavorazioneUtilePome = 0;
        $pzMaxPome = 0;

        $numRecord = count($dati);
        
        if ($numRecord > 0) {    
            foreach ($dati as $record) {
                $sumPzBuoniPomeriggio += $record['num_pz_realizzati'];
                $sumPzScartiPomeriggio += $record['num_pz_scarti'];
                $tempoLavorazioneUtilePome = $record['pranzo'] === NULL ? 3600 : 1800;
                $pzMaxPome += $tempoLavorazioneUtilePome / $record['tempo_ciclo'];               
            }

            $efficienzaPomeriggio = round(((100/$pzMaxPome)*$sumPzBuoniPomeriggio),2,PHP_ROUND_HALF_UP);
            
            if ($sumPzBuoniPomeriggio != 0) {
                $qualitaPomeriggio = round((100 - ($sumPzScartiPomeriggio / $sumPzBuoniPomeriggio) * 100),2,PHP_ROUND_HALF_UP);
            } else {
                $qualitaPomeriggio = 0;
            }
        }


        /* calcolo efficienza e qualità turno della notte */
        $queryDati   = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                        FROM
                            andon_board AS ab
                            INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                            INNER JOIN risorse ON ab.id_risorsa = risorse.id
                        WHERE
                            ab.data_turno = '".$dataOdierna."' AND
                            risorsa = '".$risorsa."' AND
                            (ab.orario='22-23' OR ab.orario='23-24')";

        $stmt = $pdo->query($queryDati);
        $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sumPzBuoniNotte = 0;
        $sumPzScartiNotte = 0;
        $efficienzaNotte = 0;
        $qualitaNotte = 0;
        $tempoLavorazioneUtileNotte = 0;
        $pzMaxNotte = 0;

        $numRecord = count($dati);
        
        if ($numRecord > 0) {    
            foreach ($dati as $record) {
                $sumPzBuoniNotte += $record['num_pz_realizzati'];
                $sumPzScartiNotte += $record['num_pz_scarti'];
                $tempoLavorazioneUtileNotte = $record['pranzo'] === NULL ? 3600 : 1800;
                $pzMaxNotte += $tempoLavorazioneUtileNotte / $record['tempo_ciclo'];               
            }

            $efficienzaNotte = round(((100/$pzMaxNotte)*$sumPzBuoniNotte),2,PHP_ROUND_HALF_UP); 
            //$efficienzaNotte = round(($efficienzaNotte/$numRecord),2,PHP_ROUND_HALF_UP);
            
            if ($sumPzBuoniNotte != 0) {
                $qualitaNotte = round((100 - ($sumPzScartiNotte / $sumPzBuoniNotte) * 100),2,PHP_ROUND_HALF_UP);
            } else {
                $qualitaNotte = 0;
            }
        }
        
    }

} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <meta name="description" content="Andon board view">
    <meta name="author" content="Mauro Visintin"/>
    <meta name="copyright" content="Mauro Visintin"/>

    <title>Andon Board | Efficienza Turno</title>

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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">  
    <link rel="stylesheet" href="css/andon_style.css">
    <!-- CSS personalized -->
    <link rel="stylesheet" href="css/stickyFooter.css">
</head>
<body class="d-flex flex-column h-100">
    <header>
        <h1 class="mt-4 text-center">Analisi dati turno corrente</h1>
    </header>

    <main class="flex-shrink-0">
        <div class="container mb-4">

            <table class="table caption-top">
                <caption>Turno del Mattino</caption>
                <thead>
                    <tr>
                        <th scope="col">orario</th>
                        <th scope="col">num_pz_ora</th>
                        <th scope="col">num_pz_realizzati</th>
                        <th scope="col">num_pz_scarti</th>
                        <th scope="col">risorsa</th>
                        <th scope="col">sigla</th>
                        <th scope="col">tempo_ciclo</th>
                        <th scope="col">data_turno</th>
                        <th scope="col">note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($turnoMattino as $record): ?>
                        <tr>
                            <th scope="row"><?php echo $record['orario']; ?></th>
                            <?php foreach ($record as $key => $value): ?>
                            <?php if ($key !== 'orario' && $key !== 'pranzo'): ?>
                                <?php if ($key == 'num_pz_ora' && $record['pranzo'] !== NULL): ?>
                                    <?php $value = floor($value / 2); ?>
                                <?php endif; ?>
                                <td><?php echo $value; ?></td>
                            <?php endif; ?>
                            <?php 
                                $sumPzObiettivoMattino += ($key == 'num_pz_ora') ? $value : 0 ;
                                endforeach; 
                            ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row" colspan="3">pz./turno = <?= $sumPzObiettivoMattino; ?></th>
                        <th scope="row" colspan="3">pz. buoni = <?= $sumPzBuoniMattino; ?></th>
                        <th scope="row" colspan="3">pz. scarti = <?= $sumPzScartiMattino; ?></th>
                    </tr>
                    <tr>
                        <th scope="row" colspan="3">efficienza = <?= $efficienzaMattino; ?>%</th>
                        <th scope="row" colspan="3">qualità = <?= $qualitaMattino; ?>%</th>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>

            <hr>

            <table class="table caption-top">
                <caption>Turno del Pomeriggio</caption>
                <thead>
                    <tr>
                        <th scope="col">orario</th>
                        <th scope="col">num_pz_ora</th>
                        <th scope="col">num_pz_realizzati</th>
                        <th scope="col">num_pz_scarti</th>
                        <th scope="col">risorsa</th>
                        <th scope="col">sigla</th>
                        <th scope="col">tempo_ciclo</th>
                        <th scope="col">data_turno</th>
                        <th scope="col">note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($turnoPomeriggio as $record): ?>
                        <tr>
                            <th scope="row"><?php echo $record['orario']; ?></th>
                            <?php foreach ($record as $key => $value): ?>
                            <?php if ($key !== 'orario' && $key !== 'pranzo'): ?>
                                <?php if ($key == 'num_pz_ora' && $record['pranzo'] !== NULL): ?>
                                    <?php $value = floor($value / 2); ?>
                                <?php endif; ?>
                                <td><?php echo $value; ?></td>
                            <?php endif; ?>
                            <?php 
                                $sumPzObiettivoPomeriggio += ($key == 'num_pz_ora') ? $value : 0 ;
                                endforeach; 
                            ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row" colspan="3">pz./turno = <?= $sumPzObiettivoPomeriggio; ?></th>
                        <th scope="row" colspan="3">pz. buoni = <?= $sumPzBuoniPomeriggio; ?></th>
                        <th scope="row" colspan="3">pz. scarti = <?= $sumPzScartiPomeriggio; ?></th>
                    </tr>
                    <tr>
                        <th scope="row" colspan="3">efficienza = <?= $efficienzaPomeriggio; ?>%</th>
                        <th scope="row" colspan="3">qualità = <?= $qualitaPomeriggio; ?>%</th>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>

            <hr>

            <table class="table caption-top">
                <caption>Turno della Notte</caption>
                <thead>
                    <tr>
                        <th scope="col">orario</th>
                        <th scope="col">num_pz_ora</th>
                        <th scope="col">num_pz_realizzati</th>
                        <th scope="col">num_pz_scarti</th>
                        <th scope="col">risorsa</th>
                        <th scope="col">sigla</th>
                        <th scope="col">tempo_ciclo</th>
                        <th scope="col">data_turno</th>
                        <th scope="col">note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($turnoNotte as $record): ?>
                        <tr>
                            <th scope="row"><?php echo $record['orario']; ?></th>
                            <?php foreach ($record as $key => $value): ?>
                            <?php if ($key !== 'orario' && $key !== 'pranzo'): ?>
                                <?php if ($key == 'num_pz_ora' && $record['pranzo'] !== NULL): ?>
                                    <?php $value = floor($value / 2); ?>
                                <?php endif; ?>
                                <td><?php echo $value; ?></td>
                            <?php endif; ?>
                            <?php 
                                $sumPzObiettivoNotte += ($key == 'num_pz_ora') ? $value : 0 ;
                                endforeach; 
                            ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row" colspan="3">pz./turno = <?= $sumPzObiettivoNotte; ?></th>
                        <th scope="row" colspan="3">pz. buoni = <?= $sumPzBuoniNotte; ?></th>
                        <th scope="row" colspan="3">pz. scarti = <?= $sumPzScartiNotte; ?></th>
                    </tr>
                    <tr>
                        <th scope="row" colspan="3">efficienza = <?= $efficienzaNotte; ?>%</th>
                        <th scope="row" colspan="3">qualità = <?= $qualitaNotte; ?>%</th>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>

        </div>
    </main>

    <?php
        include "footer.php";
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
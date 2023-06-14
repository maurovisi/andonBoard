<?php
// Connessione al database
require_once "db_config.php";

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $dataOdierna = date("Y-m-d");
    $dataPrec = strtotime ( '-1 day' , strtotime ( $dataOdierna ) ) ; // facciamo l'operazione
    $dataPrec = date ( 'Y-m-d' , $dataPrec ); //trasformiamo la data nel formato accettato dal db YYYY-MM-DD

    // Ottenere l'ora corrente come oggetto DateTime
    $oraCorrente = new DateTime();

    // Ottenere l'ora corrente come stringa nel formato 'H:i'
    $oraCorrenteStringa = $oraCorrente->format('H:i');

    // Estrarre solo l'ora dall'oggetto DateTime
    $oraCorrenteOre = $oraCorrente->format('H');

    
    $query_004 = "SELECT o.sigla, c.codice_ciclo, c.tempo_ciclo, c.pzDaRealizzare, a.id_risorsa, a.id_operatore, a.id_ciclo, a.num_pz_ora, a.num_pz_realizzati, a.orario, a.num_pz_scarti, a.pranzo, a.note, a.data_turno
                  FROM andon_board AS a
                  INNER JOIN cicli AS c ON a.id_ciclo = c.id_ciclo
                  INNER JOIN operatori AS o ON a.id_operatore = o.id
                  INNER JOIN risorse AS r ON a.id_risorsa = r.id
                  WHERE r.risorsa = '004'
                  ORDER BY a.created_at DESC LIMIT 1";

    $result_004 = $pdo->query($query_004);

    $data_004 = array();
    $rowPz_004 = array();

    $efficienza_004 = 0;
    $bgPBarEfficienza_004;
    $qualita_004 = 0;
    $bgPBarQualita_004;
    $problemi_004 = 0;
    $bgPBarProblemi_004;
    
    if ($result_004 && $result_004->rowCount() > 0) {
        $row_004 = $result_004->fetch(PDO::FETCH_ASSOC);

        $data_004['sigla'] = $row_004['sigla'];
        $data_004['codice_ciclo'] = $row_004['codice_ciclo'];
        $data_004['tempo_ciclo'] = $row_004['tempo_ciclo'];
        $data_004['pzDaRealizzare'] = $row_004['pzDaRealizzare'];
        $data_004['id_risorsa'] = $row_004['id_risorsa'];
        $data_004['id_operatore'] = $row_004['id_operatore'];
        $data_004['id_ciclo'] = $row_004['id_ciclo'];
        $data_004['num_pz_ora'] = $row_004['num_pz_ora'];
        $data_004['num_pz_realizzati'] = $row_004['num_pz_realizzati'];
        $data_004['orario'] = $row_004['orario'];
        $data_004['num_pz_scarti'] = $row_004['num_pz_scarti'];
        $data_004['pranzo'] = $row_004['pranzo'];
        $data_004['note'] = $row_004['note'];
        $data_004['data_turno'] = $row_004['data_turno'];

        // calcolo pz tot realizzati
        $query_pz = "SELECT SUM(a.num_pz_realizzati) AS pzTotRealizzati 
                     FROM andon_board AS a
                     INNER JOIN cicli AS c ON a.id_ciclo = c.id_ciclo
                     WHERE c.codice_ciclo = '".$data_004['codice_ciclo']."'";
        $resQeryPz_004 = $pdo->query($query_pz);
        $row = $resQeryPz_004->fetch(PDO::FETCH_ASSOC);
        $rowPz_004["pzTotRealizzati"] = $row["pzTotRealizzati"];

        /* 
        calcolo efficienza x turno dell'operatore
        recupero tutti i record del turno dell'operatore che ha inserito l'ultimo record e calcolo i parametri in base alla durata del turno
        */
        if ($dataOdierna == $data_004['data_turno'] || $dataPrec == $data_004['data_turno']) {

            $queryTurni_004  = "";
            $risorsa = '004';

            if ($oraCorrenteOre >= '06' && $oraCorrenteOre < '14') {

                $queryTurni_004  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='6-7' OR ab.orario='7-8' OR ab.orario='8-9' OR ab.orario='9-10' OR ab.orario='10-11' OR ab.orario='11-12' OR ab.orario='12-13' OR ab.orario='13-14')";

            } elseif ($oraCorrenteOre >= '14' && $oraCorrenteOre < '22') {

                $queryTurni_004  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='14-15' OR ab.orario='15-16' OR ab.orario='16-17' OR ab.orario='17-18' OR ab.orario='18-19' OR ab.orario='19-20' OR ab.orario='20-21' OR ab.orario='21-22')";

            } elseif ($oraCorrenteOre >= '22' && $oraCorrenteOre < '24') {
                
                $queryTurni_004  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='22-23' OR ab.orario='23-24')";

            } else {

                $queryTurni_004  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        risorsa = '".$risorsa."' AND
                                        ( (ab.data_turno = '".$dataPrec."' AND ab.orario = '22-23' ) OR (ab.data_turno = '".$dataPrec."' AND ab.orario = '23-24' ) OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '24-1') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '1-2') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '2-3') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '3-4') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '4-5') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '5-6') )";

            }


            $stmt = $pdo->query($queryTurni_004);
            $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sumPzBuoni = 0;
            $sumPzScarti = 0;

            foreach ($dati as $record) {
                $sumPzBuoni += $record['num_pz_realizzati'];
                $sumPzScarti += $record['num_pz_scarti'];
            }

            $numRecord = count($dati);

            if ($numRecord > 0) {    
                foreach ($dati as $record) {
                    $tempoCiclo = $record['tempo_ciclo'];        

                    if ($record['pranzo'] === NULL) {
                        $efficienza_004 += ($record['num_pz_realizzati'] * 100 / (3600 / $tempoCiclo));
                    } else {
                        $efficienza_004 += ($record['num_pz_realizzati'] * 100 / (1800 / $tempoCiclo));
                    }
                }

                $efficienza_004 = round(($efficienza_004/$numRecord),2,PHP_ROUND_HALF_UP);

                if ($sumPzBuoni != 0) {
                    $qualita_004 = round((100 - ($sumPzScarti / $sumPzBuoni) * 100),2,PHP_ROUND_HALF_UP);
                } else {
                    $qualita_004 = 0;
                }
                
            }


            // efficienza x turno
            if ($efficienza_004 >= 90) {
                $bgPBarEfficienza_004 = "bg-success";
            } elseif($efficienza_004 >= 60){
                $bgPBarEfficienza_004 = "bg-warning";
            } else {
                $bgPBarEfficienza_004 = "bg-danger";
            }

            // qualità x turno
            if ($qualita_004 >= 90) {
                $bgPBarQualita_004 = "bg-success";
            } elseif($qualita_004 >= 60){
                $bgPBarQualita_004 = "bg-warning";
            } else {
                $bgPBarQualita_004 = "bg-danger";
            }

            // calcolo problemi x turno
            $problemi_004 = round(((($efficienza_004 + $qualita_004) / 200 * 100) - 100) * (-1));
            if ($problemi_004 <= 10) {
                $bgPBarProblemi_004 = "bg-success";
            } elseif($problemi_004 <= 40){
                $bgPBarProblemi_004 = "bg-warning";
            } else {
                $bgPBarProblemi_004 = "bg-danger";
            }

            // background box riepilogo rapido
            $efficienzaRiepilogo = round(($efficienza_004 + $qualita_004) / 2);
            if ($efficienzaRiepilogo >= 90 && $problemi_004 <= 10) {
                $boxFastBackground_004 = "card text-bg-success";
            } elseif($efficienzaRiepilogo >= 60 && $problemi_004 <= 40){
                $boxFastBackground_004 = "card text-bg-warning";
            } elseif ($efficienzaRiepilogo >= 0 || $problemi_004 <= 100) {
                $boxFastBackground_004 = "card text-bg-danger";
            } else {
                $boxFastBackground_004 = "card text-bg-light";
            }
        } else {
            $boxFastBackground_004 = "card text-bg-dark";
        }
    }


    $query_011 = "SELECT o.sigla, c.codice_ciclo, c.tempo_ciclo, c.pzDaRealizzare, a.id_risorsa, a.id_operatore, a.id_ciclo, a.num_pz_ora, a.num_pz_realizzati, a.orario, a.num_pz_scarti, a.pranzo, a.note, a.data_turno
                  FROM andon_board AS a
                  INNER JOIN cicli AS c ON a.id_ciclo = c.id_ciclo
                  INNER JOIN operatori AS o ON a.id_operatore = o.id
                  INNER JOIN risorse AS r ON a.id_risorsa = r.id
                  WHERE r.risorsa = '011'
                  ORDER BY a.created_at DESC LIMIT 1";

    $result_011 = $pdo->query($query_011);
    $data_011 = array();
    $rowPz_011 = array();

    $efficienza_011 = 0;
    $bgPBarEfficienza_011;
    $qualita_011 = 0;
    $bgPBarQualita_011;
    $problemi_011 = 0;
    $bgPBarProblemi_011;

    if ($result_011 && $result_011->rowCount() > 0) {
        $row_011 = $result_011->fetch(PDO::FETCH_ASSOC);

        $data_011['sigla'] = $row_011['sigla'];
        $data_011['codice_ciclo'] = $row_011['codice_ciclo'];
        $data_011['tempo_ciclo'] = $row_011['tempo_ciclo'];
        $data_011['pzDaRealizzare'] = $row_011['pzDaRealizzare'];
        $data_011['id_risorsa'] = $row_011['id_risorsa'];
        $data_011['id_operatore'] = $row_011['id_operatore'];
        $data_011['id_ciclo'] = $row_011['id_ciclo'];
        $data_011['num_pz_ora'] = $row_011['num_pz_ora'];
        $data_011['num_pz_realizzati'] = $row_011['num_pz_realizzati'];
        $data_011['orario'] = $row_011['orario'];
        $data_011['num_pz_scarti'] = $row_011['num_pz_scarti'];
        $data_011['pranzo'] = $row_011['pranzo'];
        $data_011['note'] = $row_011['note'];
        $data_011['data_turno'] = $row_011['data_turno'];

        // calcolo pz tot realizzati
        $query_pz = "SELECT SUM(a.num_pz_realizzati) AS pzTotRealizzati 
                     FROM andon_board AS a
                     INNER JOIN cicli AS c ON a.id_ciclo = c.id_ciclo
                     WHERE c.codice_ciclo = '".$data_011['codice_ciclo']."'";
        $resQeryPz_011 = $pdo->query($query_pz);
        $row = $resQeryPz_011->fetch(PDO::FETCH_ASSOC);
        $rowPz_011["pzTotRealizzati"] = $row["pzTotRealizzati"];

        /* 
        calcolo efficienza x turno dell'operatore
        recupero tutti i record del turno dell'operatore che ha inserito l'ultimo record e calcolo i parametri in base alla durata del turno
        */
        if ($dataOdierna == $data_011['data_turno'] || $dataPrec == $data_011['data_turno']) {

            $queryTurni_011 = "";
            $risorsa = '011';

            if ($oraCorrenteOre >= '06' && $oraCorrenteOre < '14') {

                $queryTurni_011  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='6-7' OR ab.orario='7-8' OR ab.orario='8-9' OR ab.orario='9-10' OR ab.orario='10-11' OR ab.orario='11-12' OR ab.orario='12-13' OR ab.orario='13-14')";

            } elseif ($oraCorrenteOre >= '14' && $oraCorrenteOre < '22') {

                $queryTurni_011  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='14-15' OR ab.orario='15-16' OR ab.orario='16-17' OR ab.orario='17-18' OR ab.orario='18-19' OR ab.orario='19-20' OR ab.orario='20-21' OR ab.orario='21-22')";

            } elseif ($oraCorrenteOre >= '22' && $oraCorrenteOre < '24') {
                
                $queryTurni_011  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='22-23' OR ab.orario='23-24')";

            } else {

                $queryTurni_011  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        risorsa = '".$risorsa."' AND
                                        ( (ab.data_turno = '".$dataPrec."' AND ab.orario = '22-23' ) OR (ab.data_turno = '".$dataPrec."' AND ab.orario = '23-24' ) OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '24-1') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '1-2') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '2-3') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '3-4') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '4-5') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '5-6') )";

            }


            $stmt = $pdo->query($queryTurni_011);
            $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sumPzBuoni = 0;
            $sumPzScarti = 0;

            foreach ($dati as $record) {
                $sumPzBuoni += $record['num_pz_realizzati'];
                $sumPzScarti += $record['num_pz_scarti'];
            }

            $numRecord = count($dati);

            if ($numRecord > 0) {    
                foreach ($dati as $record) {
                    $tempoCiclo = $record['tempo_ciclo'];        

                    if ($record['pranzo'] === NULL) {
                        $efficienza_011 += ($record['num_pz_realizzati'] * 100 / (3600 / $tempoCiclo));
                    } else {
                        $efficienza_011 += ($record['num_pz_realizzati'] * 100 / (1800 / $tempoCiclo));
                    }
                }

                $efficienza_011 = round(($efficienza_011/$numRecord),2,PHP_ROUND_HALF_UP);

                if ($sumPzBuoni != 0) {
                    $qualita_011 = round((100 - ($sumPzScarti / $sumPzBuoni) * 100),2,PHP_ROUND_HALF_UP);
                } else {
                    $qualita_011 = 0;
                }
            }

            // efficienza x turno
            if ($efficienza_011 >= 90) {
                $bgPBarEfficienza_011 = "bg-success";
            } elseif($efficienza_011 >= 60){
                $bgPBarEfficienza_011 = "bg-warning";
            } else {
                $bgPBarEfficienza_011 = "bg-danger";
            }

            // qualità x turno
            if ($qualita_011 >= 90) {
                $bgPBarQualita_011 = "bg-success";
            } elseif($qualita_011 >= 60){
                $bgPBarQualita_011 = "bg-warning";
            } else {
                $bgPBarQualita_011 = "bg-danger";
            }

            // calcolo problemi x turno
            $problemi_011 = round(((($efficienza_011 + $qualita_011) / 200 * 100) - 100) * (-1));
            if ($problemi_011 <= 10) {
                $bgPBarProblemi_011 = "bg-success";
            } elseif($problemi_011 <= 40){
                $bgPBarProblemi_011 = "bg-warning";
            } else {
                $bgPBarProblemi_011 = "bg-danger";
            }

            // background box riepilogo rapido
            $efficienzaRiepilogo = round(($efficienza_011 + $qualita_011) / 2);
            if ($efficienzaRiepilogo >= 90 && $problemi_011 <= 10) {
                $boxFastBackground_011 = "card text-bg-success";
            } elseif($efficienzaRiepilogo >= 60 && $problemi_011 <= 40){
                $boxFastBackground_011 = "card text-bg-warning";
            } elseif ($efficienzaRiepilogo >= 0 || $problemi_011 <= 100) {
                $boxFastBackground_011 = "card text-bg-danger";
            } else {
                $boxFastBackground_011 = "card text-bg-light";
            }
        } else {
            $boxFastBackground_011 = "card text-bg-dark";
        }
    }


    $query_020 = "SELECT o.sigla, c.codice_ciclo, c.tempo_ciclo, c.pzDaRealizzare, a.id_risorsa, a.id_operatore, a.id_ciclo, a.num_pz_ora, a.num_pz_realizzati, a.orario, a.num_pz_scarti, a.pranzo, a.note, a.data_turno
                  FROM andon_board AS a
                  INNER JOIN cicli AS c ON a.id_ciclo = c.id_ciclo
                  INNER JOIN operatori AS o ON a.id_operatore = o.id
                  INNER JOIN risorse AS r ON a.id_risorsa = r.id
                  WHERE r.risorsa = '020'
                  ORDER BY a.created_at DESC LIMIT 1";

    $result_020 = $pdo->query($query_020);
    $data_020 = array();
    $rowPz_020 = array();

    $efficienza_020 = 0;
    $bgPBarEfficienza_020;
    $qualita_020 = 0;
    $bgPBarQualita_020;
    $problemi_020 = 0;
    $bgPBarProblemi_020;

    if ($result_020 && $result_020->rowCount() > 0) {
        $row_020 = $result_020->fetch(PDO::FETCH_ASSOC);

        $data_020['sigla'] = $row_020['sigla'];
        $data_020['codice_ciclo'] = $row_020['codice_ciclo'];
        $data_020['tempo_ciclo'] = $row_020['tempo_ciclo'];
        $data_020['pzDaRealizzare'] = $row_020['pzDaRealizzare'];
        $data_020['id_risorsa'] = $row_020['id_risorsa'];
        $data_020['id_operatore'] = $row_020['id_operatore'];
        $data_020['id_ciclo'] = $row_020['id_ciclo'];
        $data_020['num_pz_ora'] = $row_020['num_pz_ora'];
        $data_020['num_pz_realizzati'] = $row_020['num_pz_realizzati'];
        $data_020['orario'] = $row_020['orario'];
        $data_020['num_pz_scarti'] = $row_020['num_pz_scarti'];
        $data_020['pranzo'] = $row_020['pranzo'];
        $data_020['note'] = $row_020['note'];
        $data_020['data_turno'] = $row_020['data_turno'];

        // calcolo pz tot realizzati
        $query_pz = "SELECT SUM(a.num_pz_realizzati) AS pzTotRealizzati 
                     FROM andon_board AS a
                     INNER JOIN cicli AS c ON a.id_ciclo = c.id_ciclo
                     WHERE c.codice_ciclo = '".$data_020['codice_ciclo']."'";
        $resQeryPz_020 = $pdo->query($query_pz);
        $row = $resQeryPz_020->fetch(PDO::FETCH_ASSOC);
        $rowPz_020["pzTotRealizzati"] = $row["pzTotRealizzati"];

        /* 
        calcolo efficienza x turno dell'operatore
        recupero tutti i record del turno dell'operatore che ha inserito l'ultimo record e calcolo i parametri in base alla durata del turno
        */ 
        if ($dataOdierna == $data_020['data_turno'] || $dataPrec == $data_020['data_turno']) {
            
            $queryTurni_020 = "";
            $risorsa = '020';

            if ($oraCorrenteOre >= '06' && $oraCorrenteOre < '14') {

                $queryTurni_020  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='6-7' OR ab.orario='7-8' OR ab.orario='8-9' OR ab.orario='9-10' OR ab.orario='10-11' OR ab.orario='11-12' OR ab.orario='12-13' OR ab.orario='13-14')";

            } elseif ($oraCorrenteOre >= '14' && $oraCorrenteOre < '22') {

                $queryTurni_020  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='14-15' OR ab.orario='15-16' OR ab.orario='16-17' OR ab.orario='17-18' OR ab.orario='18-19' OR ab.orario='19-20' OR ab.orario='20-21' OR ab.orario='21-22')";

            } elseif ($oraCorrenteOre >= '22' && $oraCorrenteOre < '24') {
                
                $queryTurni_020  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='22-23' OR ab.orario='23-24')";

            } else {

                $queryTurni_020  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        risorsa = '".$risorsa."' AND
                                        ( (ab.data_turno = '".$dataPrec."' AND ab.orario = '22-23' ) OR (ab.data_turno = '".$dataPrec."' AND ab.orario = '23-24' ) OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '24-1') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '1-2') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '2-3') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '3-4') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '4-5') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '5-6') )";

            }


            $stmt = $pdo->query($queryTurni_020);
            $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sumPzBuoni = 0;
            $sumPzScarti = 0;

            foreach ($dati as $record) {
                $sumPzBuoni += $record['num_pz_realizzati'];
                $sumPzScarti += $record['num_pz_scarti'];
            }

            $numRecord = count($dati);

            if ($numRecord > 0) {    
                foreach ($dati as $record) {
                    $tempoCiclo = $record['tempo_ciclo'];        

                    if ($record['pranzo'] === NULL) {
                        $efficienza_020 += ($record['num_pz_realizzati'] * 100 / (3600 / $tempoCiclo));
                    } else {
                        $efficienza_020 += ($record['num_pz_realizzati'] * 100 / (1800 / $tempoCiclo));
                    }
                }

                $efficienza_020 = round(($efficienza_020/$numRecord),2,PHP_ROUND_HALF_UP);
                
                if ($sumPzBuoni != 0) {
                    $qualita_020 = round((100 - ($sumPzScarti / $sumPzBuoni) * 100),2,PHP_ROUND_HALF_UP);
                } else {
                    $qualita_020 = 0;
                }
            }
            
            // efficienza x turno
            if ($efficienza_020 >= 90) {
                $bgPBarEfficienza_020 = "bg-success";
            } elseif($efficienza_020 >= 60){
                $bgPBarEfficienza_020 = "bg-warning";
            } else {
                $bgPBarEfficienza_020 = "bg-danger";
            }

            // qualità x turno
            if ($qualita_020 >= 90) {
                $bgPBarQualita_020 = "bg-success";
            } elseif($qualita_020 >= 60){
                $bgPBarQualita_020 = "bg-warning";
            } else {
                $bgPBarQualita_020 = "bg-danger";
            }

            // calcolo problemi x turno
            $problemi_020 = round(((($efficienza_020 + $qualita_020) / 200 * 100) - 100) * (-1));
            if ($problemi_020 <= 10) {
                $bgPBarProblemi_020 = "bg-success";
            } elseif($problemi_020 <= 40){
                $bgPBarProblemi_020 = "bg-warning";
            } else {
                $bgPBarProblemi_020 = "bg-danger";
            }

            // background box riepilogo rapido
            $efficienzaRiepilogo = round(($efficienza_020 + $qualita_020) / 2);
            if ($efficienzaRiepilogo >= 90 && $problemi_020 <= 10) {
                $boxFastBackground_020 = "card text-bg-success";
            } elseif($efficienzaRiepilogo >= 60 && $problemi_020 <= 40){
                $boxFastBackground_020 = "card text-bg-warning";
            } elseif ($efficienzaRiepilogo >= 0 || $problemi_020 <= 100) {
                $boxFastBackground_020 = "card text-bg-danger";
            } else {
                $boxFastBackground_020 = "card text-bg-light";
            }
        } else {
            $boxFastBackground_020 = "card text-bg-dark";
        }    
    }


    $query_021 = "SELECT o.sigla, c.codice_ciclo, c.tempo_ciclo, c.pzDaRealizzare, a.id_risorsa, a.id_operatore, a.id_ciclo, a.num_pz_ora, a.num_pz_realizzati, a.orario, a.num_pz_scarti, a.pranzo, a.note, a.data_turno
                  FROM andon_board AS a
                  INNER JOIN cicli AS c ON a.id_ciclo = c.id_ciclo
                  INNER JOIN operatori AS o ON a.id_operatore = o.id
                  INNER JOIN risorse AS r ON a.id_risorsa = r.id
                  WHERE r.risorsa = '021'
                  ORDER BY a.created_at DESC LIMIT 1";

    $result_021 = $pdo->query($query_021);
    $data_021 = array();
    $rowPz_021 = array();

    $efficienza_021 = 0;
    $bgPBarEfficienza_021;
    $qualita_021 = 0;
    $bgPBarQualita_021;
    $problemi_021 = 0;
    $bgPBarProblemi_021;

    if ($result_021 && $result_021->rowCount() > 0) {
        $row_021 = $result_021->fetch(PDO::FETCH_ASSOC);

        $data_021['sigla'] = $row_021['sigla'];
        $data_021['codice_ciclo'] = $row_021['codice_ciclo'];
        $data_021['tempo_ciclo'] = $row_021['tempo_ciclo'];
        $data_021['pzDaRealizzare'] = $row_021['pzDaRealizzare'];
        $data_021['id_risorsa'] = $row_021['id_risorsa'];
        $data_021['id_operatore'] = $row_021['id_operatore'];
        $data_021['id_ciclo'] = $row_021['id_ciclo'];
        $data_021['num_pz_ora'] = $row_021['num_pz_ora'];
        $data_021['num_pz_realizzati'] = $row_021['num_pz_realizzati'];
        $data_021['orario'] = $row_021['orario'];
        $data_021['num_pz_scarti'] = $row_021['num_pz_scarti'];
        $data_021['pranzo'] = $row_021['pranzo'];
        $data_021['note'] = $row_021['note'];
        $data_021['data_turno'] = $row_021['data_turno'];

        // calcolo pz tot realizzati
        $query_pz = "SELECT SUM(a.num_pz_realizzati) AS pzTotRealizzati 
                     FROM andon_board AS a
                     INNER JOIN cicli AS c ON a.id_ciclo = c.id_ciclo
                     WHERE c.codice_ciclo = '".$data_021['codice_ciclo']."'";
        $resQeryPz_021 = $pdo->query($query_pz);
        $row = $resQeryPz_021->fetch(PDO::FETCH_ASSOC);
        $rowPz_021["pzTotRealizzati"] = $row["pzTotRealizzati"];

        /* 
        calcolo efficienza x turno dell'operatore
        recupero tutti i record del turno dell'operatore che ha inserito l'ultimo record e calcolo i parametri in base alla durata del turno solo se il turno è del gg d'oggi o a cavallo della mezza notte
        */
        if ($dataOdierna == $data_021['data_turno'] || $dataPrec == $data_021['data_turno']) {
            
            $queryTurni_021 = "";
            $risorsa = '021';

            if ($oraCorrenteOre >= '06' && $oraCorrenteOre < '14') {

                $queryTurni_021  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='6-7' OR ab.orario='7-8' OR ab.orario='8-9' OR ab.orario='9-10' OR ab.orario='10-11' OR ab.orario='11-12' OR ab.orario='12-13' OR ab.orario='13-14')";

            } elseif ($oraCorrenteOre >= '14' && $oraCorrenteOre < '22') {

                $queryTurni_021  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='14-15' OR ab.orario='15-16' OR ab.orario='16-17' OR ab.orario='17-18' OR ab.orario='18-19' OR ab.orario='19-20' OR ab.orario='20-21' OR ab.orario='21-22')";

            } elseif ($oraCorrenteOre >= '22' && $oraCorrenteOre < '24') {
                
                $queryTurni_021  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        ab.data_turno = '".$dataOdierna."' AND
                                        risorsa = '".$risorsa."' AND
                                        (ab.orario='22-23' OR ab.orario='23-24')";

            } else {

                $queryTurni_021  = "SELECT ab.num_pz_realizzati, ab.num_pz_scarti, cicli.tempo_ciclo, cicli.pzDaRealizzare, ab.id_ciclo, ab.pranzo
                                    FROM
                                        andon_board AS ab
                                        INNER JOIN cicli ON ab.id_ciclo = cicli.id_ciclo
                                        INNER JOIN risorse ON ab.id_risorsa = risorse.id
                                    WHERE
                                        risorsa = '".$risorsa."' AND
                                        ( (ab.data_turno = '".$dataPrec."' AND ab.orario = '22-23' ) OR (ab.data_turno = '".$dataPrec."' AND ab.orario = '23-24' ) OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '24-1') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '1-2') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '2-3') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '3-4') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '4-5') OR (ab.data_turno = '".$dataOdierna."' AND ab.orario = '5-6') )";

            }


            $stmt = $pdo->query($queryTurni_021);
            $dati = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sumPzBuoni = 0;
            $sumPzScarti = 0;

            foreach ($dati as $record) {
                $sumPzBuoni += $record['num_pz_realizzati'];
                $sumPzScarti += $record['num_pz_scarti'];
            }

            $numRecord = count($dati);

            if ($numRecord > 0) {    
                foreach ($dati as $record) {
                    $tempoCiclo = $record['tempo_ciclo'];        

                    if ($record['pranzo'] === NULL) {
                        $efficienza_021 += ($record['num_pz_realizzati'] * 100 / (3600 / $tempoCiclo));
                    } else {
                        $efficienza_021 += ($record['num_pz_realizzati'] * 100 / (1800 / $tempoCiclo));
                    }
                }

                $efficienza_021 = round(($efficienza_021/$numRecord),2,PHP_ROUND_HALF_UP);
                
                if ($sumPzBuoni != 0) {
                    $qualita_021 = round((100 - ($sumPzScarti / $sumPzBuoni) * 100),2,PHP_ROUND_HALF_UP);
                } else {
                    $qualita_021 = 0;
                }
            }

            // efficienza x turno
            if ($efficienza_021 >= 90) {
                $bgPBarEfficienza_021 = "bg-success";
            } elseif($efficienza_021 >= 60){
                $bgPBarEfficienza_021 = "bg-warning";
            } else {
                $bgPBarEfficienza_021 = "bg-danger";
            }

            // qualità x turno
            if ($qualita_021 >= 90) {
                $bgPBarQualita_021 = "bg-success";
            } elseif($qualita_021 >= 60){
                $bgPBarQualita_021 = "bg-warning";
            } else {
                $bgPBarQualita_021 = "bg-danger";
            }

            // calcolo problemi x turno
            $problemi_021 = round(((($efficienza_021 + $qualita_021) / 200 * 100) - 100) * (-1));
            if ($problemi_021 <= 10) {
                $bgPBarProblemi_021 = "bg-success";
            } elseif($problemi_021 <= 40){
                $bgPBarProblemi_021 = "bg-warning";
            } else {
                $bgPBarProblemi_021 = "bg-danger";
            }

            // background box riepilogo rapido
            $efficienzaRiepilogo = round(($efficienza_021 + $qualita_021) / 2);
            if ($efficienzaRiepilogo >= 90 && $problemi_021 <= 10) {
                $boxFastBackground_021 = "card text-bg-success";
            } elseif($efficienzaRiepilogo >= 60 && $problemi_021 <= 40){
                $boxFastBackground_021 = "card text-bg-warning";
            } elseif ($efficienzaRiepilogo >= 0 || $problemi_021 <= 100) {
                $boxFastBackground_021 = "card text-bg-danger";
            } else {
                $boxFastBackground_021 = "card text-bg-light";
            }
        } else {
            $boxFastBackground_021 = "card text-bg-dark";
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

    <title>Andon Board | View</title>

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
</head>
<body>
    <div class="container-fluid h-100">
        <div class="row pt-2">
            <div class="col-lg-3 column-content px-3 py-2">
                <div class="card border-light">
                    <div class="card-body">
                        <h2 class="andon-card-title">004</h2>                
                        <p id="ciclo1">Ciclo: <span class="data_view fw-bold" id="ciclo_004"><?= $data_004['codice_ciclo']??'NULL' ?></span></p>
                        <p id="tc_004">TC: <span class="data_view fw-bold" id="tc_004"><?= $data_004['tempo_ciclo']??'NULL' ?></span></p>
                        <p id="sigla-operatore1">Operatore: <span class="data_view fw-bold" id="operatore_004"><?= $data_004['sigla']??'NULL' ?></span></p>
                        <hr>
                        <p id="num-pz-tot1">Num. Pz. Tot: <span class="data_view fw-bold" id="pzTotRealiz_004"><?= $rowPz_004["pzTotRealizzati"]??'NULL' ?></span> su <span class="data_view fw-bold" id="pzTot_004"><?= $data_004['pzDaRealizzare']??'NULL' ?></span></p>
                        <p id="num-pz-fatti1">Num. Pz. Fatti: <span class="data_view fw-bold" id="pzFatti_004"><?= $data_004['num_pz_realizzati']??'NULL' ?></span> su <span class="data_view fw-bold" id="pzOra_004"><?= $data_004['num_pz_ora']??'NULL' ?></span></p>
                        <p id="num-pz-scarti1">Num. Pz. Scarti: <span class="data_view fw-bold" id="pzScarti_004"><?= $data_004['num_pz_scarti']??'NULL' ?></span></p>
                        <hr>
                        <p>Efficienza:</p>
                        <div class="progress">
                            <div id="efficienza-bar004" class="progress-bar <?= $bgPBarEfficienza_004 ?>" role="progressbar" style="width: <?= $efficienza_004 ?>%" aria-valuenow="<?= $efficienza_004 ?>" aria-valuemin="0" aria-valuemax="100"><?= $efficienza_004 ?>%</div>
                        </div>
                        <p class="mt-2">Qualità:</p>
                        <div class="progress">
                            <div id="qualita-bar004" class="progress-bar <?= $bgPBarQualita_004 ?>" role="progressbar" style="width: <?= $qualita_004 ?>%" aria-valuenow="<?= $qualita_004 ?>" aria-valuemin="0" aria-valuemax="100"><?= $qualita_004 ?>%</div>
                        </div>
                        <p class="mt-2">Problemi:</p>
                        <div class="progress">
                            <div id="problemi-bar004" class="progress-bar" role="progressbar <?= $bgPBarProblemi_004 ?>" style="width: <?= $problemi_004 ?>%" aria-valuenow="<?= $problemi_004 ?>" aria-valuemin="0" aria-valuemax="100"><?= $problemi_004 ?>%</div>
                        </div> 
                    </div>
                </div>
            </div>
            <div class="col-lg-3 column-content px-3 py-2">
                <div class="card border-light">
                    <div class="card-body">
                        <h2 class="andon-card-title">011</h2>
                        <p id="ciclo2">Ciclo: <span class="data_view fw-bold" id="ciclo_011"><?= $data_011['codice_ciclo']??'NULL' ?></span></p>
                        <p id="tc_011">TC: <span class="data_view fw-bold" id="tc_011"><?= $data_011['tempo_ciclo']??'NULL' ?></span></p>
                        <p id="sigla-operatore2">Operatore: <span class="data_view fw-bold" id="operatore_011"><?= $data_011['sigla']??'NULL' ?></span></p>
                        <hr>
                        <p id="num-pz-tot2">Num. Pz. Tot: <span class="data_view fw-bold" id="pzTotRealiz_011"><?= $rowPz_011["pzTotRealizzati"]??'NULL' ?></span> su <span class="data_view fw-bold" id="pzTot_011"><?= $data_011['pzDaRealizzare']??'NULL' ?></span></p>
                        <p id="num-pz-fatti2">Num. Pz. Fatti: <span class="data_view fw-bold" id="pzFatti_011"><?= $data_011['num_pz_realizzati']??'NULL' ?></span> su <span class="data_view fw-bold" id="pzOra_011"><?= $data_011['num_pz_ora']??'NULL' ?></span></p>
                        <p id="num-pz-scarti2">Num. Pz. Scarti: <span class="data_view fw-bold" id="pzScarti_011"><?= $data_011['num_pz_scarti']??'NULL' ?></span></p>
                        <hr>
                        <p>Efficienza:</p>
                        <div class="progress">
                            <div id="efficienza-bar011" class="progress-bar <?= $bgPBarEfficienza_011 ?>" role="progressbar" style="width: <?= $efficienza_011 ?>%" aria-valuenow="<?= $efficienza_011 ?>" aria-valuemin="0" aria-valuemax="100"><?= $efficienza_011 ?>%</div>
                        </div>
                        <p class="mt-2">Qualità:</p>
                        <div class="progress">
                            <div id="qualita-bar011" class="progress-bar <?= $bgPBarQualita_011 ?>" role="progressbar" style="width: <?= $qualita_011 ?>%" aria-valuenow="<?= $qualita_011 ?>" aria-valuemin="0" aria-valuemax="100"><?= $qualita_011 ?>%</div>
                        </div>
                        <p class="mt-2">Problemi:</p>
                        <div class="progress">
                            <div id="problemi-bar011" class="progress-bar" role="progressbar <?= $bgPBarProblemi_011 ?>" style="width: <?= $problemi_011 ?>%" aria-valuenow="<?= $problemi_011 ?>" aria-valuemin="0" aria-valuemax="100"><?= $problemi_011 ?>%</div>
                        </div> 
                    </div>
                </div>
            </div>
            <div class="col-lg-3 column-content px-3 py-2">
                <div class="card border-light">
                    <div class="card-body">
                        <h2 class="andon-card-title">020</h2>
                        <p id="ciclo3">Ciclo: <span class="data_view fw-bold" id="ciclo_020"><?= $data_020['codice_ciclo']??'NULL' ?></span></p>
                        <p id="tc_020">TC: <span class="data_view fw-bold" id="tc_020"><?= $data_020['tempo_ciclo']??'NULL' ?></span></p>
                        <p id="sigla-operatore3">Operatore: <span class="data_view fw-bold" id="operatore_020"><?= $data_020['sigla']??'NULL' ?></span></p>
                        <hr>
                        <p id="num-pz-tot3">Num. Pz. Tot: <span class="data_view fw-bold" id="pzTotRealiz_020"><?= $rowPz_020["pzTotRealizzati"]??'NULL' ?></span> su <span class="data_view fw-bold" id="pzTot_020"><?= $data_020['pzDaRealizzare']??'NULL' ?></span></p>
                        <p id="num-pz-fatti3">Num. Pz. Fatti: <span class="data_view fw-bold" id="pzFatti_020"><?= $data_020['num_pz_realizzati']??'NULL' ?></span> su <span class="data_view fw-bold" id="pzOra_020"><?= $data_020['num_pz_ora']??'NULL' ?></span></p>
                        <p id="num-pz-scarti3">Num. Pz. Scarti: <span class="data_view fw-bold" id="pzScarti_020"><?= $data_020['num_pz_scarti']??'NULL' ?></span></p>
                        <hr>
                        <p>Efficienza:</p>
                        <div class="progress">
                            <div id="efficienza-bar020" class="progress-bar <?= $bgPBarEfficienza_020 ?>" role="progressbar" style="width: <?= $efficienza_020 ?>%" aria-valuenow="<?= $efficienza_020 ?>" aria-valuemin="0" aria-valuemax="100"><?= $efficienza_020 ?>%</div>
                        </div>
                        <p class="mt-2">Qualità:</p>
                        <div class="progress">
                            <div id="qualita-bar020" class="progress-bar <?= $bgPBarQualita_020 ?>" role="progressbar" style="width: <?= $qualita_020 ?>%" aria-valuenow="<?= $qualita_020 ?>" aria-valuemin="0" aria-valuemax="100"><?= $qualita_020 ?>%</div>
                        </div>
                        <p class="mt-2">Problemi:</p>
                        <div class="progress">
                            <div id="problemi-bar020" class="progress-bar" role="progressbar <?= $bgPBarProblemi_020 ?>" style="width: <?= $problemi_020 ?>%" aria-valuenow="<?= $problemi_020 ?>" aria-valuemin="0" aria-valuemax="100"><?= $problemi_020 ?>%</div>
                        </div> 
                    </div>
                </div>
            </div>
            <div class="col-lg-3 column-content px-3 py-2">
                <div class="card border-light">
                    <div class="card-body">
                        <h2 class="andon-card-title">021</h2>
                        <p id="ciclo4">Ciclo: <span class="data_view fw-bold" id="ciclo_021"><?= $data_021['codice_ciclo']??'NULL' ?></span></p>
                        <p id="tc_021">TC: <span class="data_view fw-bold" id="tc_021"><?= $data_021['tempo_ciclo']??'NULL' ?></span></p>
                        <p id="sigla-operatore4">Operatore: <span class="data_view fw-bold" id="operatore_021"><?= $data_021['sigla']??'NULL' ?></span></p>
                        <hr>
                        <p id="num-pz-tot4">Num. Pz. Tot: <span class="data_view fw-bold" id="pzTotRealiz_021"><?= $rowPz_021["pzTotRealizzati"]??'NULL' ?></span> su <span class="data_view fw-bold" id="pzTot_021"><?= $data_021['pzDaRealizzare']??'NULL' ?></span></p>
                        <p id="num-pz-fatti4">Num. Pz. Fatti: <span class="data_view fw-bold" id="pzFatti_021"><?= $data_021['num_pz_realizzati']??'NULL' ?></span> su <span class="data_view fw-bold" id="pzOra_021"><?= $data_021['num_pz_ora']??'NULL' ?></span></p>
                        <p id="num-pz-scarti4">Num. Pz. Scarti: <span class="data_view fw-bold" id="pzScarti_021"><?= $data_021['num_pz_scarti']??'NULL' ?></span></p>
                        <hr>
                        <p>Efficienza:</p>
                        <div class="progress">
                            <div id="efficienza-bar021" class="progress-bar <?= $bgPBarEfficienza_021 ?>" role="progressbar" style="width: <?= $efficienza_021 ?>%" aria-valuenow="<?= $efficienza_021 ?>" aria-valuemin="0" aria-valuemax="100"><?= $efficienza_021 ?>%</div>
                        </div>
                        <p class="mt-2">Qualità:</p>
                        <div class="progress">
                            <div id="qualita-bar021" class="progress-bar <?= $bgPBarQualita_021 ?>" role="progressbar" style="width: <?= $qualita_021 ?>%" aria-valuenow="<?= $qualita_021 ?>" aria-valuemin="0" aria-valuemax="100"><?= $qualita_021 ?>%</div>
                        </div>
                        <p class="mt-2">Problemi:</p>
                        <div class="progress">
                            <div id="problemi-bar021" class="progress-bar <?= $bgPBarProblemi_021 ?>" role="progressbar" style="width: <?= $problemi_021 ?>%" aria-valuenow="<?= $problemi_021 ?>" aria-valuemin="0" aria-valuemax="100"><?= $problemi_021 ?>%</div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row text-center pt-2 px-2">
            <div class="col-sm-3">
                <a href="efficienzaTurno.php?risorsa=004" target="_blank" title="Dettagli turni risorsa 004">
                    <div id="cardRiepilogo_004" class="<?= $boxFastBackground_004 ?>">
                        <div class="card-body">
                            <h5 class="card-title fs-2">004</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-3">
                <a href="efficienzaTurno.php?risorsa=011" target="_blank" title="Dettagli turni risorsa 011">
                    <div id="cardRiepilogo_011" class="<?= $boxFastBackground_011 ?>">
                        <div class="card-body">
                            <h5 class="card-title fs-2">011</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-3">
                <a href="efficienzaTurno.php?risorsa=020" target="_blank" title="Dettagli turni risorsa 020">
                    <div id="cardRiepilogo_020" class="<?= $boxFastBackground_020 ?>">
                        <div class="card-body">
                            <h5 class="card-title fs-2">020</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-3">
                <a href="efficienzaTurno.php?risorsa=021" target="_blank" title="Dettagli turni risorsa 021">
                    <div id="cardRiepilogo_021" class="<?= $boxFastBackground_021 ?>">
                        <div class="card-body">
                            <h5 class="card-title fs-2">021</h5>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function() {
            location.reload();
        }, 600000); // 600000 millisecondi corrispondono a 10 minuti
    </script>
</body>
</html>
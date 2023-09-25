<?php
// Connessione al database
require_once "db_config.php";

try {
    $conn = new PDO($dsn, $username, $password, $options);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dataOdierna = date("Y-m-d");

    $id_risorsa = $_POST['risorsa'] ?? NULL;
    $orario = $_POST['orario'] ?? NULL;

    if (!$id_risorsa || !$orario) {
        throw new Exception("Tutti i campi sono obbligatori. Assicurati di aver compilato tutti i campi.");
    }

    // Verifica se esiste già un record con gli stessi dati
    $stmt = $conn->prepare("SELECT * FROM andon_board WHERE id_risorsa = :id_risorsa AND orario = :orario AND data_turno = :data_turno");

    // Binding dei parametri
    $stmt->bindParam(':id_risorsa', $id_risorsa);
    $stmt->bindParam(':orario', $_POST['orario']);
    $stmt->bindParam(':data_turno', $dataOdierna);

    // Esecuzione della query
    $stmt->execute();

    // Se esiste già un record con gli stessi dati, termina lo script
    if ($stmt->rowCount() > 0) {
        throw new Exception("Errore: I dati inseriti sono già presenti nel database!");
    }

    // verifico il parametro che mi indica che tipologia di inserimento devo effettuare
    $parametroInsert = $_POST['checkValidation'] ?? 100;

    // preparo le variabili
    $id_operatore = $_POST['operatore'] ?? NULL;
    $id_risorsa = $_POST['risorsa'] ?? NULL;
    $id_ciclo = $_POST['ciclo'] ?? NULL;
    $codice_ciclo = $_POST['cicloInsert'] ?? NULL;
    $orario = $_POST['orario'] ?? NULL;
    $num_pz_ora = $_POST['num_pz'] ?? 0;
    $num_pz_realizzati = $_POST['pz_buoni'] ?? 0;
    $num_pz_scarti = $_POST['pz_sbagliati'] ?? 0;
    $pranzo = (isset($_POST['pranzo']) && $_POST['pranzo'] == 'on') ? 0 : NULL;
    $note = $_POST['note'] ?? NULL;
    $pezziMontati = $_POST['pezziMontati'] ?? 0;
    $num_pz_tagliati = $_POST['num_pz_tagliati'] ?? 0;

    if (!$id_operatore || !$id_risorsa || !$id_ciclo || !$orario) {
        throw new Exception("Errore bind param.");
    }
    
    // Preparazione della query di inserimento
    $stmt = $conn->prepare("INSERT INTO andon_board (id_operatore, id_risorsa, id_ciclo, codice_ciclo, orario, num_pz_ora, num_pz_realizzati, num_pz_scarti, pranzo, note) VALUES (:id_operatore, :id_risorsa, :id_ciclo, :codice_ciclo, :orario, :num_pz_ora, :num_pz_realizzati, :num_pz_scarti, :pranzo, :note)");

    $stmt->bindValue(':id_operatore', $id_operatore);
    $stmt->bindValue(':id_risorsa', $id_risorsa);
    $stmt->bindValue(':id_ciclo', $id_ciclo);
    $stmt->bindValue(':codice_ciclo', $codice_ciclo);
    $stmt->bindValue(':orario', $orario);
    $stmt->bindValue(':num_pz_ora', $num_pz_ora);
    $stmt->bindValue(':pranzo', $pranzo);
    $stmt->bindValue(':note', $note);
        
    // Binding dei parametri
    if ($parametroInsert == 1) {
        throw new Exception("Errore: Non riesco a determinare la tipologia di inserimento da effettuare - err 1!");
    } elseif ($parametroInsert == 2) {
        $stmt->bindValue(':num_pz_realizzati', $num_pz_realizzati);
        $stmt->bindValue(':num_pz_scarti', $num_pz_scarti);
    } elseif ($parametroInsert == 4) {
        $stmt->bindValue(':num_pz_realizzati', $num_pz_tagliati);
        $stmt->bindValue(':num_pz_scarti', 0);
    } elseif ($parametroInsert == 3) {
        $stmt->bindValue(':num_pz_realizzati', $pezziMontati);
        $stmt->bindValue(':num_pz_scarti', 0);
    } else {
        throw new Exception("Errore: Non riesco a determinare la tipologia di inserimento da effettuare - err 1500!");
    }
    
    // Esecuzione della query di inserimento
    $executionSuccess = $stmt->execute();

    if ($executionSuccess) {
        echo json_encode(["status" => "success", "message" => "Dati inseriti con successo!"]);
    } else {
        // Potresti anche recuperare informazioni sull'errore specifico con $stmt->errorInfo()
        throw new Exception("Errore durante l'esecuzione della query.");
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>
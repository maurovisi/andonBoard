<?php
require_once "db_config.php";

try {
    $conn = new PDO($dsn, $username, $password, $options);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method.");
    }

    $dataOdierna = date("Y-m-d");

    $id_risorsa = $_POST['risorsa'] ?? null;
    $orario = $_POST['orario'] ?? null;
    $id_operatore = $_POST['operatore'] ?? null;
    $id_ciclo = $_POST['ciclo'] ?? null;
    $num_pz_ora = $_POST['numPzOra'] ?? 0;
    $num_pz_realizzati = $_POST['pz_buoni'] ?? 0;
    $num_pz_scarti = $_POST['pz_sbagliati'] ?? 0;
    $note = $_POST['note'] ?? null;
    $pranzoValue = (isset($_POST['pranzo']) && $_POST['pranzo'] == 'on') ? 0 : NULL;

    // Verifica se tutti i campi obbligatori sono stati impostati
    
    if (!$id_risorsa || !$orario || !$id_operatore || !$id_ciclo || !$num_pz_ora || !$num_pz_realizzati ) {
        throw new Exception("Tutti i campi sono obbligatori. Assicurati di aver compilato tutti i campi.");
    }

    // Check for existing record
    $stmt = $conn->prepare("SELECT * FROM andon_board WHERE id_risorsa = :id_risorsa AND orario = :orario AND data_turno = :data_turno");
    $stmt->execute([':id_risorsa' => $id_risorsa, ':orario' => $orario, ':data_turno' => $dataOdierna]);

    if ($stmt->rowCount() > 0) {
        throw new Exception("I dati inseriti sono già presenti nel database!");
    }

    // Insert new record
    $stmt = $conn->prepare("INSERT INTO andon_board (id_operatore, id_risorsa, id_ciclo, orario, num_pz_ora, num_pz_realizzati, num_pz_scarti, pranzo, note) VALUES (:id_operatore, :id_risorsa, :id_ciclo, :orario, :num_pz_ora, :num_pz_realizzati, :num_pz_scarti, :pranzo, :note)");

    $executionSuccess = $stmt->execute([
        ':id_operatore' => $id_operatore,
        ':id_risorsa' => $id_risorsa,
        ':id_ciclo' => $id_ciclo,
        ':orario' => $orario,
        ':num_pz_ora' => $num_pz_ora,
        ':num_pz_realizzati' => $num_pz_realizzati,
        ':num_pz_scarti' => $num_pz_scarti,
        ':pranzo' => $pranzoValue,
        ':note' => $note
    ]);

    if ($executionSuccess) {
        echo json_encode(["status" => "success", "message" => "Dati inseriti con successo!"]);
    } else {
        // Potresti anche recuperare informazioni sull'errore specifico con $stmt->errorInfo()
        throw new Exception("Errore durante l'esecuzione della query.");
    }
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Errore durante l'inserimento nel database.", "detailed_message" => $e->getMessage()]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>

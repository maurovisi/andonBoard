<?php
// Connessione al database
require_once "db_config.php";

try {
    $conn = new PDO($dsn, $username, $password, $options);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dataOdierna = date("Y-m-d");

    // Verifica se esiste già un record con gli stessi dati
    $stmt = $conn->prepare("SELECT * FROM andon_board WHERE id_risorsa = :id_risorsa AND orario = :orario AND data_turno = '".$dataOdierna."'");

    // Binding dei parametri
    $stmt->bindParam(':id_risorsa', $_POST['risorsa']);
    $stmt->bindParam(':orario', $_POST['orario']);

    // Esecuzione della query
    $stmt->execute();

    // Se esiste già un record con gli stessi dati, termina lo script
    if ($stmt->rowCount() > 0) {
        die("Errore: I dati inseriti sono già presenti nel database!");
    }

    // Preparazione della query di inserimento
    $stmt = $conn->prepare("INSERT INTO andon_board (id_operatore, id_risorsa, id_ciclo, codice_ciclo, orario, num_pz_ora, num_pz_realizzati, num_pz_scarti, pranzo, note) VALUES (:id_operatore, :id_risorsa, :id_ciclo, :codice_ciclo, :orario, :num_pz_ora, :num_pz_realizzati, :num_pz_scarti, :pranzo, :note)");

    // Binding dei parametri
    $stmt->bindParam(':id_operatore', $_POST['operatore']);
    $stmt->bindParam(':id_risorsa', $_POST['risorsa']);
    $stmt->bindParam(':id_ciclo', $_POST['ciclo']);
    $stmt->bindParam(':codice_ciclo', NULL);
    $stmt->bindParam(':orario', $_POST['orario']);
    $stmt->bindParam(':num_pz_ora', $_POST['num_pz']);
    $stmt->bindParam(':num_pz_realizzati', $_POST['pz_buoni']);
    $stmt->bindParam(':num_pz_scarti', $_POST['pz_sbagliati']);
    $stmt->bindParam(':pranzo', $_POST['pranzo']);
    $stmt->bindParam(':note', $_POST['note']);

    // Esecuzione della query di inserimento
    $stmt->execute();

    echo "Dati inseriti con successo!";
} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
}

// Chiusura della connessione
$conn = null;
?>

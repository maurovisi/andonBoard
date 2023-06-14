<?php
require_once "db_config.php";

if (isset($_GET['id'])) {
    $risorsaId = $_GET['id'];
    try {
        $conn = new PDO($dsn, $username, $password, $options);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT id_ciclo, codice_ciclo, tempo_ciclo FROM cicli WHERE assegnato_a_risorsa = :risorsaId AND attivo = 1 ORDER BY codice_ciclo");
        $stmt->execute([':risorsaId' => $risorsaId]);
        $cicli = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($cicli);
    } catch (PDOException $e) {
        echo "Errore: " . $e->getMessage();
    }
}
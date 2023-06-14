<?php
header('Content-Type: application/json');

require_once "db_config.php";

try {
    $conn = new PDO($dsn, $username, $password, $options);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $risorse = array();
    $operatori = array();
    $cicli = array();

    $sql_risorse = "SELECT id, risorsa FROM risorse";
    $sql_operatori = "SELECT DISTINCT operatore FROM andon_board";
    $sql_cicli = "SELECT id_ciclo, codice_ciclo, tempo_ciclo FROM cicli";

    foreach ($conn->query($sql_risorse) as $row) {
        array_push($risorse, array("id" => $row['id'], "risorsa" => $row['risorsa']));
    }

    foreach ($conn->query($sql_operatori) as $row) {
        array_push($operatori, $row['operatore']);
    }

    foreach ($conn->query($sql_cicli) as $row) {
        array_push($cicli, array("id_ciclo" => $row['id_ciclo'], "codice_ciclo" => $row['codice_ciclo'], "tempo_ciclo" => $row['tempo_ciclo']));
    }

    $data = array("risorse" => $risorse, "operatori" => $operatori, "cicli" => $cicli);
    echo json_encode($data);

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null;
?>

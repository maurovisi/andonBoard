<?php
// Controlla se il riferimento della pagina è diverso da login.php
if ($_SERVER['HTTP_REFERER'] !== 'https://andon.nextlevelbranding.it/login.php') {
    // Reindirizza alla index.php
    header('Location: https://andon.nextlevelbranding.it/login.php');
    exit;
}

// Connessione al database
require_once "db_config.php";

try {
    $conn = new PDO($dsn, $username, $password, $options);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query per ottenere i dati per le select
    $risorse = $conn->query("SELECT id, risorsa FROM risorse");
    $operatori_query = $conn->query("SELECT id, sigla FROM operatori ORDER by sigla");
    $cicli = $conn->query("SELECT id_ciclo, codice_ciclo, tempo_ciclo FROM cicli")->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="/css/andon_style.css">
</head>
<body>
    
</body>
</html>
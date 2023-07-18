<?php
// Ricevi i dati inviati tramite POST
$intervallo = isset($_POST['intervallo']) ? $_POST['intervallo'] : '';
$about = isset($_POST['about']) ? $_POST['about'] : '';
$groups = isset($_POST['groups']) ? $_POST['groups'] : '';
$start = isset($_POST['start']) ? $_POST['start'] : '';
$yearBest = isset($_POST['yearBest']) ? $_POST['yearBest'] : '';

// Funzione per filtrare e sanificare i dati
function sanitizeInput($input)
{
    $sanitizedInput = trim($input); // Rimuovi spazi iniziali e finali
    $sanitizedInput = stripslashes($sanitizedInput); // Rimuovi eventuali backslash di escape
    $sanitizedInput = htmlspecialchars($sanitizedInput); // Converti caratteri speciali in entità HTML
    // Puoi aggiungere ulteriori filtri o regole di sanitizzazione qui, a seconda delle tue esigenze
    return $sanitizedInput;
}

// Filtra e sanifica i dati ricevuti
$intervallo = sanitizeInput($intervallo);
$about = sanitizeInput($about);
$groups = sanitizeInput($groups);
$start = sanitizeInput($start);
$yearBest = sanitizeInput($yearBest);

// Esempio di array di dati di prova
$datiProva = [
    [
        "posizione" => 1,
        "classifica" => "Migliore",
        "efficienza" => 90,
        "qualita" => 85,
        "pz_buoni" => 1000,
        "pz_scarti" => 50
    ],
    [
        "posizione" => 2,
        "classifica" => "Buono",
        "efficienza" => 80,
        "qualita" => 75,
        "pz_buoni" => 900,
        "pz_scarti" => 100
    ],
    // Aggiungi altri dati di prova qui...
];

// Prepara i dati di risposta nella struttura richiesta da DataTables
$response = [
    "data" => [],
];

// Popola i dati di risposta con i dati di prova
foreach ($datiProva as $dato) {
    $row = [
        $dato["posizione"],
        $dato["classifica"],
        $dato["efficienza"],
        $dato["qualita"],
        $dato["pz_buoni"],
        $dato["pz_scarti"]
    ];

    $response["data"][] = $row;
}

// Restituisci i dati di risposta come JSON
header("Content-Type: application/json");
echo json_encode($response);
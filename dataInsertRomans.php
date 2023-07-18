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
    //$cicli = $conn->query("SELECT id_ciclo, codice_ciclo, tempo_ciclo FROM cicli WHERE attivo = 1 ORDER BY codice_ciclo")->fetchAll(PDO::FETCH_ASSOC);

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

    <meta name="description" content="Andon board dashboard Romans">
    <meta name="author" content="Mauro Visintin"/>
    <meta name="copyright" content="Mauro Visintin"/>

    <title>Andon Board | Data Insert Romans</title>

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
    <link rel="stylesheet" href="/css/style.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">

</head>
<body class="bk">
    <div class="container">
        <div class="content-wrapper">
            <div class="row">
            <div class="col-12 text-center my-5 center-aligned">
                <img src="/img/logo02.png" alt="logo Bressan" width="50" height="50">
                <h1>&ensp;ANDON BOARD&ensp;</h1>
                <img src="/img/logo02.png" alt="logo Bressan" width="50" height="50">                   
            </div>
            </div>
            <form id="andon-form" class="andonForm" method="POST" target="insertdata.php">
                <div class="row mb-3">
                    <div class="col">
                        <label for="risorsa" class="form-label">Risorsa</label>
                        <select class="form-select" name="risorsa" id="risorsa">
                            <option value="">Seleziona</option>
                            <?php foreach ($risorse as $row): ?>
                                <option value="<?= $row['id'] ?>"><?= $row['risorsa'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Seleziona una risorsa.
                        </div>
                    </div>
                    <div class="col">
                        <label for="operatore" class="form-label">Operatore</label>
                        <select name="operatore" id="operatore" class="form-select" required>
                            <option value="">Seleziona</option>
                            <?php foreach ($operatori_query as $operatore): ?>
                                <option value="<?php echo $operatore["id"]; ?>"><?php echo $operatore["sigla"]; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Seleziona un operatore.
                        </div>
                    </div>
                    <div class="col">
                        <label for="ciclo" class="form-label">Ciclo</label>
                        <select class="form-select" name="ciclo" id="ciclo">
                            <option value="">Seleziona</option>
                            <!-- I cicli verranno popolati tramite AJAX -->
                        </select>
                        <div class="invalid-feedback">
                            Seleziona un ciclo.
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label for="orario" class="form-label">Orario</label>
                        <select class="form-select" id="orario" name="orario" required>
                            <option value="">Selezionare</option>
                            <option value="6-7">06:00 - 07:00</option>
                            <option value="7-8">07:00 - 08:00</option>
                            <option value="8-9">08:00 - 09:00</option>
                            <option value="9-10">09:00 - 10:00</option>
                            <option value="10-11">10:00 - 11:00</option>
                            <option value="11-12">11:00 - 12:00</option>
                            <option value="12-13">12:00 - 13:00</option>
                            <option value="13-14">13:00 - 14:00</option>
                            <option value="14-15">14:00 - 15:00</option>
                            <option value="15-16">15:00 - 16:00</option>
                            <option value="16-17">16:00 - 17:00</option>
                            <option value="17-18">17:00 - 18:00</option>
                            <option value="18-19">18:00 - 19:00</option>
                            <option value="19-20">19:00 - 20:00</option>
                            <option value="20-21">20:00 - 21:00</option>
                            <option value="21-22">21:00 - 22:00</option>
                            <option value="22-23">22:00 - 23:00</option>
                            <option value="23-24">23:00 - 24:00</option>
                            <option value="24-1">24:00 - 01:00</option>
                            <option value="1-2">01:00 - 02:00</option>
                            <option value="2-3">02:00 - 03:00</option>
                            <option value="3-4">03:00 - 04:00</option>
                            <option value="4-5">04:00 - 05:00</option>
                            <option value="5-6">05:00 - 06:00</option>
                        </select>
                        <div class="invalid-feedback">
                            Seleziona un orario.
                        </div>
                    </div>
                    <div class="col">
                        <label for="num_pz" class="form-label">Num pz. ora</label>
                        <input type="number" class="form-control" id="num_pz" name="num_pz" value="0" disabled>
                    </div>
                    <div class="col">
                        <label for="pranzo" class="form-label">Pranzo</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="pranzo" name="pranzo">
                            <label class="form-check-label" for="pranzo">Pranzo</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6 text-center">
                        <label for="pz_buoni" class="form-label">Pz. buoni</label>
                        <input type="number" class="form-control" id="pz_buoni" name="pz_buoni" value="0" min="0" required>
                        <div class="invalid-feedback">
                            Inserisci pz buoni.
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <label for="pz_sbagliati" class="form-label">Pz. sbagliati</label>
                        <input type="number" class="form-control" id="pz_sbagliati" name="pz_sbagliati" value="0" min="0" required>
                        <div class="invalid-feedback">
                            Inserisci pz sbagliati.
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label for="note" class="form-label">Note</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary btn-lg mx-2 px-4">Invio</button>
                        <button type="reset" class="btn btn-secondary btn-lg mx-2 px-4">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            // Calcola il valore del campo "num_pz" in base alla selezione del ciclo
            $('#ciclo').on('change', function () {
                var selectedOption = $('option:selected', this);
                var tempoCiclo = parseInt(selectedOption.attr('data-tempo-ciclo'));
                if (tempoCiclo) {
                    var numPz = Math.floor(3600 / tempoCiclo);
                    $('#num_pz').val(numPz);
                } else {
                    $('#num_pz').val('');
                }
            });

            // Controllo campi obbligatori e invio form
            $('#andon-form').on('submit', function (e) {
                e.preventDefault();

                let isValid = true;
                $('.error-message').remove();

                $('select').each(function () {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).after('<p class="error-message" style="color: darkred; font-size: smaller;">Selezionare un valore</p>');
                    }
                });

                if ($('#pz_buoni').val() === '' || $('#pz_scartati').val() === '') {
                    isValid = false;
                    $('#pz_buoni, #pz_scartati').after('<p class="error-message" style="color: darkred; font-size: smaller;">Campo obbligatorio</p>');
                }

                if (isValid) {
                    let pezzi = $('input[name=num_pz]').val();
                    $.ajax({
                        type: "POST",
                        url: "insertdata.php",
                        data: $(this).serialize()+'&num_pz=' + pezzi,
                        success: function (response) {
                            let color = "success"; // colore verde per successo
                            let buttonColor = '#3085d6'; // colore del pulsante per successo

                            // Se la risposta contiene la parola "Errore", cambia il colore in rosso
                            if (response.includes("Errore")) {
                                color = "error"; // colore rosso per errore
                                buttonColor = '#d33'; // colore del pulsante per errore
                            }

                            Swal.fire({
                                icon: color,
                                title: response,
                                confirmButtonColor: buttonColor, // colore del pulsante
                                allowOutsideClick: false // impedisce la chiusura dell'alert se si fa clic fuori
                            });

                            $('#formAndonBoard')[0].reset();
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            Swal.fire({
                                icon: 'error', // icona di errore
                                title: "Si è verificato un errore: " + textStatus + " " + errorThrown,
                                confirmButtonColor: '#d33', // colore rosso per il pulsante di conferma
                                allowOutsideClick: false // impedisce la chiusura dell'alert se si fa clic fuori
                            });
                        }
                    });

                }
            });
        });
    </script>

    <script>
        $('#risorsa').on('change', function () {
            let risorsaId = $("#risorsa option:selected").text();
            if (risorsaId) {
                $.get('get_cicli.php', {id: risorsaId}, function (data) {
                    let cicli = JSON.parse(data);
                    let cicloSelect = $('#ciclo');
                    cicloSelect.empty();
                    cicloSelect.append('<option value="">Seleziona</option>');
                    cicli.forEach(function (ciclo) {
                        cicloSelect.append('<option value="' + ciclo.id_ciclo + '" data-tempo-ciclo="' + ciclo.tempo_ciclo + '">' + ciclo.codice_ciclo + '</option>');
                    });
                });
            } else {
                $('#ciclo').empty();
                $('#ciclo').append('<option value="">Seleziona</option>');
            }
        });
    </script>
</body>
</html>
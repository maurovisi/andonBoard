$(document).ready(function () {
    $('#andon-form').on('submit', function (e) {
        e.preventDefault();

        if (this.checkValidity()) {
            const formData = $(this).serialize();

            $.ajax({
                url: 'insertdata.php',
                method: 'POST',
                data: formData,
                success: function (response) {
                    // Gestisci la risposta del server (es. mostrare un messaggio di successo)
                },
                error: function () {
                    alert('Errore nel contattare il server. Riprova più tardi.');
                }
            });
        } else {
            e.stopPropagation();
        }
    });

    $('#andon-form').on('reset', function () {
        $(this).find('.is-invalid').removeClass('is-invalid');
    });

    $('input, select').on('change', function () {
        if ($(this).is(':invalid')) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});






$(document).ready(function () {
    function validateForm() {
        let isValid = true;

        // Controlla i campi obbligatori
        $('.required').each(function () {
            if ($(this).val() === '' || $(this).val() === 'selezionare') {
                isValid = false;
                $(this).next('.error').text('Campo obbligatorio').css('color', 'darkred');
            } else {
                $(this).next('.error').text('');
            }
        });

        return isValid;
    }

    // Aggiorna il valore di "num_pz" in base al ciclo selezionato
    $('#ciclo').on('change', function () {
        const tempo_ciclo = $(this).find(':selected').data('tempo-ciclo');
        if (tempo_ciclo) {
            const num_pz = Math.floor(3600 / tempo_ciclo);
            $('#num_pz').val(num_pz);
        } else {
            $('#num_pz').val('');
        }
    });

    // Invia il form in modo asincrono
    $('#form').on('submit', function (event) {
        event.preventDefault();

        if (validateForm()) {
            const data = $(this).serialize();

            $.ajax({
                method: 'post',
                url: 'insertdata.php',
                data: data,
                success: function (response) {
                    console.log(response);
                    // Aggiungi qui le azioni da eseguire dopo l'invio del form
                },
                error: function () {
                    alert('Errore nel contattare il server');
                },
            });
        }
    });
});
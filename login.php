<?php
//$session_duration = 10 * 24 * 60 * 60; // 10 giorni in secondi
//$cookie_params = session_get_cookie_params();
//session_set_cookie_params($session_duration, $cookie_params['path'], '.andon.nextlevelbranding.it');
session_start();
//session_set_cookie_params($session_duration, $cookie_params['path'], '.andon.nextlevelbranding.it', $cookie_params['secure']);

function generateCsrfToken() {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

// Aggiorna il timestamp di scadenza della sessione ad ogni richiesta
//session_regenerate_id();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">

  <meta name="description" content="Login page.">
  <meta name="author" content="Mauro Visintin"/>
	<meta name="copyright" content="Mauro Visintin"/>

  <title>Andon Board | Login</title>

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
  <link rel="stylesheet" href="/css/style_login.css">
</head>
<body>

	<div id="loginFormDiv">

		<main class="form-signin w-100 m-auto text-center">
			<form action="authLogin.php" method="POST" id="loginform">
				<img class="mb-4" src="/img/logo02.png" alt="logo Bressan" width="72" height="72">
				<h1 class="h3 mb-3 fw-normal">Please login</h1>

				<?php
				if (!empty($_SESSION["message"])) { ?>
					<div class="alert alert-primary" id="message">
						<?= $_SESSION["message"]; ?>
					</div>
					<?php
					$_SESSION["message"] = "";
				} ?>

				<input type="hidden" name="_csrf" id="_csrf" value="<?= generateCsrfToken(); ?>">
				<div class="form-floating">
					<input type="text" class="form-control" id="username" name="username" placeholder="user" aria-describedby="insert username" value="" required>
					<label for="username" class="text-secondary">Username</label>
				</div>
				<div class="form-floating">
					<input type="password" class="form-control" id="password" name="password" placeholder="Password" aria-describedby="insert password" value="" required>
					<label for="password" class="text-secondary">Password</label>
				</div>

				<div class="checkbox mb-3">
					<label>
						<input type="checkbox" name="remember" id="remember" class="form-check-input" value="1" aria-describedby="check keep me logged in"> Remember me
					</label>
				</div>

				<button class="w-100 btn btn-lg btn-primary" type="submit">Login</button>
				<p class="mt-5 mb-3 text-white" style="text-shadow: 0px 0px 8px #000;">Bressan &copy; <?= date("Y"); ?></p>
			</form>
		</main>

	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		$(function() {
			$('#loginform').on('submit', function(evt) {
				evt.preventDefault();
				const data = $(this).serialize();
				$.ajax({
					method: 'post',
					data: data,
					url: $(this).attr('action'),
					success: function(response) {
						const data = JSON.parse(response);
						if (data) {
							alert(data.message);
							if (data.success) {
								let percorso = $('input[name=username]').val();
								switch (percorso) {
									case 'user':
										location.href = '/';
										break;
									case 'andon_brother':
										location.href = 'andon_brother.php';
										break;
									case 'romans':
										location.href = 'dataInsertRomans.php';
										break;									
									case 'admin':
										location.href = 'andonDashboard.php';
										break;
								
									default:
										location.href = 'login.php';
										break;
								}
								
							}
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.error("Error: " + textStatus, errorThrown);
						alert('Error: ' + textStatus + "\n" + errorThrown);
					}
				});
			});
		});
	</script>

</body>
</html>
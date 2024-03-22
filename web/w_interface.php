<?php

// Funcion para crear la interfaz global
function iface() {
	global $form, $check;

	if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
		if (!checkSession()) {
			header('Location: inicio.php');
			exit();
		}
	}

	// Si existe $check entonces realiza el checkeo de la página.
	if (isset($check)) {
		check();
	}
	
	// Si existe $form entonces procesa el formulario con la función de la página.
	if (isset($form)) {
		process_form();
	}


	// Se crea la interfaz:
	echo '
	<html>
	<head>
		<link rel="stylesheet" href="estilo.css">
		<title>RPG</title>
	</head>
	
	<body>
		<nav id="bs">
			<div id="bs-div">
				<a id="bs-item" href="inicio.php">Inicio</a>
				<a id="bs-item" href="perfil.php">Perfil</a>
				<a id="bs-item" href="">----</a>
				<a id="bs-item" href="logout.php">Logout</a>
			</div>
		</nav>
		<img id="logo" src="logo.png">

	';

	echo '<p id="session-msg" ';
	
	if (isset($_SESSION["error"]) && $_SESSION["error"] != "") {
		echo 'style="color: rgb(210, 40, 20);">';
		echo $_SESSION["error"];
	} 
	else if (isset($_SESSION["msg"]) && $_SESSION["msg"] != "") {
		echo 'style="color: green;">';
		echo $_SESSION["msg"];
	}

	echo '</p>';

	

	unset($_SESSION["error"]);
	unset($_SESSION["msg"]);

	// Acá irían comprobaciones de la página.

	// Llamar a la función de creación de la página:
	if (true) {
		web();
	}

	echo '
	</body>
	</html>
	';
	// Acá podría ir un footer
}

?>
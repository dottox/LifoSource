<?php

// Funcion para crear la interfaz global
function iface() {
	global $form, $check, $zonahhhh;

	if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
		if (!checkSession()) {
			header('Location: inicio.php');
			exit();
		}
	}

  $zonahhhh = 4;

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="estilo.css">
		<title>RPG</title>
	</head>
	
	<body>
    <nav id="bs">
      <div id="bs-div">
        
        <a class="bs-item" href="inicio.php"><i class="fa fa-home" id="bs-icon"></i> <span>Inicio</span></a>
        <a class="bs-item" href="perfil.php"><i class="fa fa-id-card" id="bs-icon"></i> <span>Perfil</span></a>
        <a class="bs-item" href="mensajeria.php"><i class="fa fa-envelope-open-o" id="bs-icon"></i> <span>Mensajeria</span></a>
        <a class="bs-item" href="clasificacion.php"><i class="fa fa-trophy" id="bs-icon"></i> <span>Clasificación</span></a>
        <a class="bs-item" href="trabajar.php"><i class="fa fa-briefcase" id="bs-icon"></i> <span>Trabajar</span></a>
        <a class="bs-item" href="combate.php"><i class="fa fa-bolt" id="bs-icon"></i> <span>Combate</span></a>
        <a class="bs-item" href="logout.php"><i class="fa fa-sign-out" id="bs-icon"></i> <span>Sign-out</span></a>
      
      </div>
    </nav>
		<img id="logo" src="logo.png">

	';

	echo '<p id="session-msg" ';
	
  if (!isset($_SESSION["error"]) && !isset($_SESSION["msg"])) {
    echo 'style="display: none";">';
  }
	else if (isset($_SESSION["error"]) && $_SESSION["error"] != "") {
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

  echo '<div id="page-variable-content">';

	// Llamar a la función de creación de la página:
	if (true) {
		web();
	}

  echo '</div>';

	echo '
	</body>
	</html>
	';
	// Acá podría ir un footer
}

?>
<?php

$form = 1;
include('w_core.php');

function web() {
	echo '<p id="page-desc">';
	
	if (isset($_SESSION["loggedin"])) {
		echo 'Bienvenido, ' . $_SESSION["username"] . '!';
	}
	
	echo '</p>';

	if (isset($_SESSION["loggedin"])) {
		echo '<pre>'; print_r($_SESSION); echo '</pre>';
	}
	else {
		echo '
		<form method="post" action="inicio.php" id="login-form">
			<p>Logeate o crea una cuenta <a href="signup.php">aquí</a>.</p>
				<input id="login-form-input" type="text" name="username" placeholder="Usuario" id="username" required>
				<input id="login-form-input" type="password" name="password" placeholder="Contraseña" id="password" required>
				<input id="login-form-button" type="submit" name="login" value="Iniciar sesión" id="login-submit">
		</form>
		';
	}
}

function process_form() {

	// Login
	if (!isset($_REQUEST["username"]) || !isset($_REQUEST["password"])) {
		return;
	}

	$username = $_REQUEST["username"];
	$password = $_REQUEST["password"];
	
	if (!validateUsername($username) || !validatePassword($password)) {
		$_SESSION["error"] = "Usuario o contraseña no validos";
		header('Location: inicio.php');
		exit();
	}


	if (!searchUserByUsername($username)) {
		$_SESSION["error"] = "Usuario no encontrado";
		header('Location: inicio.php');
		exit();
	
	}

	if (checkPassword($username, $password)) {
		$_SESSION["loggedin"] = true;
		$_SESSION["username"] = $username;
    $_SESSION["isadmin"] = isAdmin($username);
    db_connect();
    db_select_db();
    $time = time();
    $iplogin = $_SERVER['REMOTE_ADDR'];
    db_query("UPDATE jugadores SET login = $time, iplogin = '$iplogin' WHERE nombrejug = '$username'");
    db_close();
		$ip = $_SERVER['REMOTE_ADDR'];
		updateLogin($username, $ip);
		header('Location: perfil.php');
		exit();
	}
	else {
		$_SESSION["error"] = "Usuario o contraseña incorrectos";
		header('Location: inicio.php');
		exit();
	}
	
	return 1;
}

?>
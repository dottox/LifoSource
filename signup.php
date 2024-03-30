<?php

$form = 1;
$check = 1;
require "w_core.php";

function check() {
	if (isset($_SESSION["loggedin"])) {
		$_SESSION["msg"] = "Ya estás logeado";
		header('Location: inicio.php');
		exit();
		return;
	}
}

function web() {
	echo '<p id="page-desc"></p>';

	
	echo '
	<form method="post" action="signup.php" id="login-form">
		<p>Crea una cuenta o logeate <a href="inicio.php">aquí</a>.</p>
    <input id="login-form-button" type="email" name="email" placeholder="Email..." id="email">
    <input id="login-form-input" type="text" name="username" placeholder="Usuario..." id="username" required>
    <input id="login-form-input" type="password" name="password" placeholder="Contraseña..." id="password" required>
    <input id="login-form-button" type="submit" name="login" value="Iniciar sesión" id="login-submit">
	</form>
	';
}

function process_form() {
	if (!isset($_REQUEST["username"]) || !isset($_REQUEST["password"])) {
		return;
	}

	if (!validateUsername($_REQUEST["username"]) || !validatePassword($_REQUEST["password"])) {
		$_SESSION["error"] = "Usuario o contraseña no válidos. 3 a 15 caracteres alfanuméricos.";
		return;
	}

	$uname = $_REQUEST["username"];
	$upass = $_REQUEST["password"];
  $umail = $_REQUEST["email"];
	
	$line = searchUserByUsername($uname);

	if (!$line) {
    $hash = pwdHash($uname, $upass);
    $time = time();
    $ip = $_SERVER['REMOTE_ADDR'];
    db_connect();
    db_select_db();
    db_query("INSERT INTO jugadores (nombrejug, password, email, creado, ipcreado, iplogin) VALUES (
      '$uname',
      '$hash',
      '$umail',
      $time,
      '$ip',
      '$ip'
    )");
    db_close();
    $_SESSION["msg"] = "Usuario creado con éxito";
    header('Location: inicio.php');
		exit();
	} else {
		$_SESSION["error"] = "Ese usuario ya existe";
	}
	
}
?>
<?php

$check = 1;
include('w_core.php');

function check() {
	if (!isset($_SESSION["loggedin"])) {
		$_SESSION["error"] = "No estás logeado";
		header('Location: inicio.php');
		exit();
	}

}

function web() {
		
	$info = searchUserByUsername($_SESSION["username"]);
	foreach($info as $key => $value) {
		echo '<p>' . $key . ': ' . $value . '</p>';
	}
}

?>
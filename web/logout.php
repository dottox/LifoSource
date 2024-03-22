<?php

include('w_aux.php');

logout();
$_SESSION["msg"] = "Has cerrado sesión";
header('Location: inicio.php');
exit();

?>
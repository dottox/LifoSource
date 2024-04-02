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
  
  // Conexión con base de datos.
  db_connect();
  db_select_db();

  $retCant = db_query("SELECT COUNT(nombrejug) AS 'total' FROM jugadores");
  $rowCant = mysqli_fetch_assoc($retCant);
  $total = $rowCant['total'];

  if ($total == 0) {
    echo '<p id="page-desc">No hay jugadores en la base de datos.</p>';
    db_close();
    return;
  }
  else if ($total > 10) {
    $total = 10;
  }

  $ret = db_query("SELECT nombrejug, puntos, insignia FROM jugadores ORDER BY puntos DESC LIMIT $total");

  $numTop = 1;
  echo '<table id="page-table-clasificacion" style="width: 100%; ">';
  echo '<tr><th>Clasificación</th><th>Puntos</th><th>Nombre</th></tr>';
  while ($row = mysqli_fetch_assoc($ret)) {
    $insignia = $row['insignia'];
    echo '<tr>';
    echo '<td>' . $numTop . '</td>';
    echo '<td>' . $row['puntos'] . '</td>';
    echo '<td>' . "<img src='img/$insignia.gif' alt='Insignia' >" . $row['nombrejug'] . '</td>';
    echo '</tr>';
    $numTop++;
  }
  echo '</table>';


  db_close();
}



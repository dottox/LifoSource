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
  $username = $_SESSION['username'];
  
  // Mensajes totales por página.
  $msgPorPag = 10;
  
  // Verifica si page es correcto.
  $page;
  if (isset($_GET['page'])) {
    $page = $_GET['page'];
    try {
      $page = intval($page);
    } catch (Exception $e) {
      $page = 1;
    }
  } if (!isset($page) || $page < 1) {
    $page = 1;
  }

  // Crea el offset de mensajes para la base de datos.
  $offset = ($page - 1) * 10;

  // LLama a la base de datos para contar todos los mensajes.
  $rettodos = db_query("SELECT COUNT(idmensaje) AS 'total' FROM mensajes WHERE nombrejug = '$username'");
  $rowtodos = mysqli_fetch_assoc($rettodos);
  $total = $rowtodos['total'];

  // Llama a la base de datos para obtener los mensajes de la página.
  $ret = db_query("SELECT * FROM mensajes WHERE nombrejug = '$username' ORDER BY hora DESC LIMIT $offset, $msgPorPag");
  db_close();
  

  // Si no hay mensajes, muestra un mensaje.
  if (mysqli_num_rows($ret) == 0) {
    echo '<p id="page-desc">';
    if ($page > 1) {
      echo "No tienes mensajes en esta página.";
    } else {
      echo "No tienes mensajes en el muro.";
    }
    echo '</p>';
  } 
  
  // Si hay mensajes, los muestra.
  else {
    echo '<p id="page-desc">';
    echo "Este es el muro de mensajes, aquí tienes un historial de todo lo que te ha pasado. <br>";
    echo "Puedes ver mensajes de combates, trabajos, y otros eventos.";
    echo '</p>';
    
    // Iterar sobre la lista de mensajes.
    while ($row = mysqli_fetch_assoc($ret)) {
      $hora = ahora_hora($row['hora']);
      $dia = ahora_dia($row['hora']);
      $mensaje = $row['mensaje'];
      echo "<p class='message-format'>";
      echo "<span style='text-decoration: underline;'>Mensaje recibido a las $hora del día $dia</span> <br><br>";
      echo "$mensaje<br>";
      echo "</p>";
    }  

    // Paginación.
    $pags = ceil($total / $msgPorPag);

    // Creación de los links de paginación.
    if ($pags > 1) {
      echo '<p id="page-desc">';
      echo "Páginas: ";
      for ($i = 1; $i <= $pags; $i++) {
        echo "<a href='mensajeria.php?page=$i'>$i</a> ";
      }
      echo '</p>';
    } else {
      echo '<p id="page-desc">';
      echo "Solo tienes una página de mensajes.";
      echo '</p>';
    }
  }
}


?>
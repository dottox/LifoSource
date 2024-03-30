<?php

$check = 1;
$form = 1;
include('w_core.php');

function check() {
	if (!isset($_SESSION["loggedin"])) {
		$_SESSION["error"] = "No estás logeado";
		header('Location: inicio.php');
		exit();
	}

}

function web() {
  db_connect();
  db_select_db();
  $username = $_SESSION['username'];
  $retcheck = db_query("SELECT fintrabajo FROM jugadores WHERE nombrejug = '$username'");
  $ret = db_query("SELECT segundos, nombre, espremium FROM trabajos");

  $row = mysqli_fetch_assoc($retcheck);
  $fintrabajo = $row['fintrabajo'];
  db_close();
  
  if ($fintrabajo > time()) {
    $tiempo = $fintrabajo - time();
    
    echo '<p id="page-desc">';
    echo "Te encuentras trabajando. Puedes cancelar cuando quieras. <br>";
    echo "Recibirás una recompensa acorde al tiempo trabajado.";
    echo '</p>';
    echo '<form action="trabajar.php" method="post">';
    $tiempostring = ahora_tiempo($tiempo);
    echo "<p>Tiempo restante: $tiempostring</p>";
    echo '<input style="width: 80px;" type="submit" name="cancelar" value="Cancelar">';
    echo '</form>';
    return;
  }

  else if ($fintrabajo <= time() && $fintrabajo != 0) {
    echo '<p id="page-desc">';
    echo 'Terminaste de trabjar, reclama las recompensas en el botón de abajo. <br>';
    echo '<form action="trabajar.php" method="post">';
    echo "<p>Tiempo restante: 00:00:00</p>";
    echo '<input style="width: 80px;" type="submit" name="reclamar" value="Reclamar">';
    echo '</form>';
  }

  if ($fintrabajo == 0) {
    echo '<p id="page-desc">';
    echo 'Este es el muro de trabajos, delante de ti puedes ver muchos afiches presentando trabajos de todo tipo. <br>';
    echo 'Algunos requerirán más esfuerzo que otros, pero tu decides si la recompensa vale la pena. <br>';
    echo '¿Qué trabajo realizarás hoy?';
    echo '<form action="trabajar.php" method="post">';
    echo '<select style="width: 300px; display: block; margin-bottom: 3px;" name="trabajo">';
    while ($row = mysqli_fetch_assoc($ret)) {
      $nombre = $row['nombre'];
      $espremium = $row['espremium'];
      $segundos = $row['segundos'];
      $segundos = intval($segundos);
      if (!$espremium || ($espremium && $_SESSION['isadmin'])) {
        echo "<option value='$segundos'>$nombre</option>";
      }
    }
    echo '</select>';
    echo '<input style="width: 80px;" type="submit" name="trabajar" value="Trabajar">';
    echo '</form>';
  }
}

function process_form() {
  if (!isset($_POST['trabajo']) && !isset($_POST['cancelar']) && !isset($_POST['reclamar'])) {
    return;
  }
  
  db_connect();
  db_select_db();
  $username = $_SESSION['username'];
  $ret = db_query("SELECT trabajando, fintrabajo FROM jugadores WHERE nombrejug = '$username'");
  $row = mysqli_fetch_assoc($ret);
  $trabajando = $row['trabajando'];
  $fintrabajo = $row['fintrabajo'];
  
  $time = time();

  if (isset($_POST['reclamar'])) {
    if ($fintrabajo > time()) {
      $_SESSION['error'] = 'No has terminado de trabajar';
      header('Location: trabajar.php');
      db_close();
      exit();
    }

    $rettrabajo = db_query("SELECT oro, puntos FROM trabajos WHERE segundos = $fintrabajo - $trabajando");
    $rowtrabajo = mysqli_fetch_assoc($rettrabajo);
    $oro = $rowtrabajo['oro'];
    $puntos = $rowtrabajo['puntos'];

    db_query("UPDATE jugadores SET fintrabajo = 0, trabajando = 0, trabajado = trabajado + $fintrabajo - $trabajando, oro = oro + $oro, puntos = puntos + $puntos WHERE nombrejug = '$username'");
    db_query("INSERT INTO mensajes(nombrejug, remitente, hora, visto, reportado, mensaje) VALUES (
      nombrejug = '$username',
      remitente = 'Sistema',
      hora = $time,
      visto = 0,
      reportado = 0,
      mensaje = 'Has terminado tu trabajo. Has ganado $oro de oro y $puntos puntos.'
      )");
    db_close();
    $_SESSION['msg'] = "Has terminado tu trabajo. Has ganado $oro de oro y $puntos puntos.";
    header('Location: trabajar.php');
    exit();
  }
  else if (isset($_POST['trabajo'])) {
    $trabajo = $_POST['trabajo'];
    if ($time < $fintrabajo) {
      $_SESSION['error'] = 'Ya estás trabajando en otro trabajo';
      header('Location: trabajar.php');
      db_close();
      exit();
    }

    $flag = false;
    $ret = db_query("SELECT segundos, espremium FROM trabajos");
    while ($row = mysqli_fetch_assoc($ret)) {
      $segundos = $row['segundos'];
      $espremium = $row['espremium'];
      if ($segundos == $trabajo) {
        $flag = true;
        break;
      }
    }
  
    if (!$flag) {
      $_SESSION['error'] = "Trabajo no encontrado: $segundos, $trabajo";
      header('Location: trabajar.php');
      db_close();
      exit();
    }
  
    if ($espremium && !$_SESSION['isadmin']) {
      $_SESSION['error'] = 'No puedes realizar trabajos premium';
      header('Location: trabajar.php');
      db_close();
      exit();
    }
    
    $ret = db_query("UPDATE jugadores SET fintrabajo = $time + $segundos, trabajando = $time WHERE nombrejug = '$username'");
    db_close();
    $_SESSION['msg'] = "Has comenzado a trabajar.";
    header('Location: trabajar.php');
    exit(); 
  }
  else if (isset($_POST['cancelar'])) {
    if ($time > $fintrabajo) {
      $_SESSION['error'] = 'No estás trabajando';
      header('Location: trabajar.php');
      db_close();
      exit();
    }

    $rettrabajo = db_query("SELECT oro, puntos FROM trabajos WHERE segundos = $fintrabajo - $trabajando");
    $rowtrabajo = mysqli_fetch_assoc($rettrabajo);
    $oro = $rowtrabajo['oro'];
    $puntos = $rowtrabajo['puntos'];

    $tiempo_restante = $time - $trabajando;
    $div = $tiempo_restante / ($fintrabajo - $trabajando);
    if ($div > 1) {
      $div = 1;
    } else if ($div < 0.1) {
      $div = 0;
    }


    // TO-DO: Agregar sistema para dar items si el tiempo trabajo es mayor a 1 hora.
    //        (Aumentando probabilidades cuanto más tiempo sea.)

    $oro = round($oro * $div);
    $puntos = round($puntos * $div);
    $mensaje = "Has cancelado tu trabajo. Has ganado $oro de oro y $puntos puntos.";
    
    db_query("UPDATE jugadores SET fintrabajo = 0, trabajando = 0, trabajado = trabajado + $tiempo_restante, oro = oro + $oro, puntos = puntos + $puntos WHERE nombrejug = '$username'");
    db_query("INSERT INTO mensajes(nombrejug, remitente, hora, visto, reportado, mensaje) VALUES (
      '$username',
      'Sistema',
      $time,
      0,
      0,
      '$mensaje'
      )");
    db_close();
    $_SESSION['msg'] = "Has cancelado tu trabajo. Has ganado $oro de oro y $puntos puntos.";
    header('Location: trabajar.php');
    exit();
  }





  
}

?>
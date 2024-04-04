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
  global $confnivel, $conftiempominimoitems, $confprobabilidaditems;

  // Echar al usuario si no se encontró ningun POST.
  if (!isset($_POST['trabajo']) && !isset($_POST['cancelar']) && !isset($_POST['reclamar'])) {
    return;
  }
  
  db_connect();
  db_select_db();
  $username = $_SESSION['username'];

  $ret = db_query("SELECT trabajando, fintrabajo FROM jugadores WHERE nombrejug = '$username'");
  $row = mysqli_fetch_assoc($ret);
  $inicioTrabajo = $row['trabajando'];
  $finTrabajo = $row['fintrabajo']; 
  $time = time();




  /* --- SISTEMA DE RECLAMAR TRABAJO --- */
  if (isset($_POST['reclamar'])) {
    if ($finTrabajo > time()) {
      $_SESSION['error'] = 'No has terminado de trabajar';
      header('Location: trabajar.php');
      db_close();
      exit();
    }

    $tiempoTrabajado = $finTrabajo - $inicioTrabajo;

    // Obtener nivel del usuario
    $retjug = db_query("SELECT puntos, nivel FROM jugadores WHERE nombrejug = '$username'");
    $rowjug = mysqli_fetch_assoc($retjug);
    $nivelJug = $rowjug['nivel'];
    $puntosJug = $rowjug['puntos'];

    // Obtener oro y puntos del trabajo realizado
    $retTrabajo = db_query("SELECT oro, puntos FROM trabajos WHERE segundos = $tiempoTrabajado");
    $rowTrabajo = mysqli_fetch_assoc($retTrabajo);
    $oro = $rowTrabajo['oro'];
    $puntos = $rowTrabajo['puntos'];

    // Calcular items
    $items = calcularItems($tiempoTrabajado, $nivelJug, $puntos);

    $mensaje = "Has terminado tu trabajo. Has ganado $oro de oro y $puntos puntos.<br>";

    // Dar items
    $mensaje .= darItems($items, $username, $puntos);

    // Calcular nivel a subir si es que sube
    $nivelASubir = calcularNivel($puntos, $puntosJug, $nivelJug);

    // Actualizar la row del jugador.
    db_query("UPDATE jugadores SET nivel = $nivelASubir, fintrabajo = 0, trabajando = 0, trabajado = trabajado + $tiempoTrabajado, oro = oro + $oro, puntos = puntos + $puntos WHERE nombrejug = '$username'");
    
    // Mensaje de subir de nivel
    if ($nivelASubir > $nivelJug) {
      $nivelDiff = $nivelASubir - $nivelJug;
      $mensaje .= "<br><br>Has subido $nivelDiff nivel(es).";
    }

    // Insertar el mensaje
    db_query("INSERT INTO mensajes(nombrejug, remitente, hora, visto, reportado, mensaje) VALUES (
      '{$username}',
      'Sistema',
      $time,
      0,
      0,
      '{$mensaje}'
    )");

    // Cerrar todo y redirigir
    db_close();
    $_SESSION['msg'] = "Has terminado tu trabajo.";
    header('Location: mensajeria.php');
    exit();
  }

  /* --- SISTEMA DE COMENZAR A TRABAJAR --- */
  else if (isset($_POST['trabajo'])) {
    $trabajo = $_POST['trabajo'];

    // Si ya está trabajando: sacarlo
    if ($time < $finTrabajo) {
      $_SESSION['error'] = 'Ya estás trabajando en otro trabajo';
      header('Location: trabajar.php');
      db_close();
      exit();
    }

    // Verificar que el trabajo exista
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
  
    // Si no se encuentra el trabajo: sacarlo
    if (!$flag) {
      $_SESSION['error'] = "Trabajo no encontrado: $segundos, $trabajo";
      header('Location: trabajar.php');
      db_close();
      exit();
    }
  
    // Si el trabajo es premium y el usuario no es admin: sacarlo
    if ($espremium && !$_SESSION['isadmin']) {
      $_SESSION['error'] = 'No puedes realizar trabajos premium';
      header('Location: trabajar.php');
      db_close();
      exit();
    }
    
    // Comenzar el trabajo
    $ret = db_query("UPDATE jugadores SET fintrabajo = $time + $segundos, trabajando = $time WHERE nombrejug = '$username'");

    // Cerrar todo, sumar el mensaje y redirigir
    db_close();
    $_SESSION['msg'] = "Has comenzado a trabajar.";
    header('Location: trabajar.php');
    exit(); 
  }

  /* --- SISTEMA DE CANCELAR TRABAJO --- */
  else if (isset($_POST['cancelar'])) {

    // Si no está trabajando: sacarlo
    if ($finTrabajo == 0) {
      $_SESSION['error'] = 'No estás trabajando';
      header('Location: trabajar.php');
      db_close();
      exit();
    }

    //Obtener nivel y puntos del jugador
    $retJug = db_query("SELECT nivel, puntos FROM jugadores WHERE nombrejug = '$username'");
    $rowJug = mysqli_fetch_assoc($retJug);
    $nivelJug = $rowJug['nivel'];
    $puntosJug = $rowJug['puntos'];

    // Obtener oro y puntos del trabajo realizado
    $retTrabajo = db_query("SELECT oro, puntos FROM trabajos WHERE segundos = $finTrabajo - $inicioTrabajo");
    $rowTrabajo = mysqli_fetch_assoc($retTrabajo);
    $oro = $rowTrabajo['oro'];
    $puntos = $rowTrabajo['puntos'];

    // Calcular el tiempo restante:
    $tiempoTrabajado = $time - $inicioTrabajo;

    // Calcular el porcentaje de trabajo completado
    $div = $tiempoTrabajado / ($finTrabajo - $inicioTrabajo);
    if ($div > 1) {
      $div = 1;
    } else if ($div < 0.1) {
      $div = 0;
    }

    // Calcular la recompensa:
    $oro = round($oro * $div);
    $puntos = round($puntos * $div);
  
    // Calcular items
    $items = calcularItems($tiempoTrabajado, $nivelJug, $puntos);
    
    $mensaje = "Has cancelado tu trabajo. Has ganado $oro de oro y $puntos puntos.<br>";
    
    // Dar items
    $mensaje .= darItems($items, $username, $puntos);
    
    
    
    // Calcular nivel a subir si es que sube
    $nivelASubir = calcularNivel($puntos, $puntosJug, $nivelJug);
    
    // Mensaje de subir de nivel
    if ($nivelASubir > $nivelJug) {
      $nivelDiff = $nivelASubir - $nivelJug;
      $mensaje .= "<br><br>Has subido $nivelDiff nivel(es).";
    }
    
    
    // Actualizar la row del jugador.
    db_query("UPDATE jugadores SET nivel = $nivelASubir, fintrabajo = 0, trabajando = 0, trabajado = trabajado + $tiempoTrabajado, oro = oro + $oro, puntos = puntos + $puntos WHERE nombrejug = '$username'");
    
    
    // Insertar el mensaje
    db_query("INSERT INTO mensajes(nombrejug, remitente, hora, visto, reportado, mensaje) VALUES (
      '{$username}',
      'Sistema',
      $time,
      0,
      0,
      '{$mensaje}'
    )");

    // Cerrar todo y redirigir
    db_close();
    $_SESSION['msg'] = "Has cancelado tu trabajo. Has ganado $oro de oro y $puntos puntos.";
    header('Location: mensajeria.php');
    exit();
  }
  




  
}

?>
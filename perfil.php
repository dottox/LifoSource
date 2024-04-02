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

  // Conexión con base de datos.
  db_connect();
  db_select_db();
  $username = $_SESSION['username'];
  $ret = db_query("SELECT * FROM jugadores WHERE nombrejug = '$username'");
  $row = mysqli_fetch_assoc($ret);

  // Creación de la página
  
  echo '<p id="page-desc">';
  echo 'Este es tu perfil, aquí puedes ver toda la información sobre tu cuenta.';
  echo '</p>';
  
  // Adm panel: Objetos free
  if ($_SESSION['isadmin']) {
    $retAdm = db_query("SELECT * FROM objetos");
    echo '<p><strong>Admin giver:</strong></p>';
    echo '<form action="perfil.php" method="post">';
    echo '<select name="admin-giver">';
    while ($rowAdm = mysqli_fetch_assoc($retAdm)) {
      $nombreobj = $rowAdm['nombreobj'];
      echo "<option value='$nombreobj'>$nombreobj</option>";
    }
    echo '</select>';
    echo '<input type="submit" name="Dar" value="Dar">';
    echo '</form>';
  }  
  
  /* --- SELECTOR DE INSIGNIA --- */
  // Primera opción: insignia actual
  $img = $row['insignia'];
  
  echo "<p style='display:inline-block;'>Insignia:</p>";
  echo "<form action='perfil.php' method='post' style='vertical-align: middle; display:inline; margin-left: 0.5em;'>";
  echo "<select name='insignia'>";

  $retActualInsignia = db_query("SELECT nombreobj FROM objetos WHERE img = '$img'");
  $rowActualInsignia = mysqli_fetch_assoc($retActualInsignia);
  $nombreobj = $rowActualInsignia['nombreobj'];
  echo "<option value='$nombreobj'>$nombreobj</option>";

  // Resto de opciones: insignias que tiene el jugador
  $retInsignias = db_query("SELECT tiene.nombreobj FROM tiene INNER JOIN objetos WHERE tiene.nombrejug = '$username' AND tiene.nombreobj = objetos.nombreobj AND objetos.img != '$img'"); 
  while ($rowInsignias = mysqli_fetch_assoc($retInsignias)) {
    $nombreobj = $rowInsignias['nombreobj'];
    echo "<option value='$nombreobj'>$nombreobj</option>";
  }  
  echo "</select>";
  echo "<input type='submit' value='Cambiar'>";
  echo "</form> ";

  // Insignia y nombre del jugador
  echo "<p><br><strong>Jugador:</strong> <img src='img/$img.gif' alt='Insignia' style='vertical-align: middle;'> $username</p>";
  
  // Puntos y oro
  $nivel = $row['nivel'];
  $puntos = $row['puntos'];
  $oro = $row['oro'];
  echo "<p><strong>Nivel:</strong> $nivel</p>";
  echo "<p><strong>Experiencia:</strong> $puntos</p>";
  echo "<p><strong>Oro:</strong> $oro</p><br>";

  // Att y Prot
  $retAttProt = db_query("SELECT SUM(objetos.prot) AS 'proteccion', SUM(objetos.ataq) AS 'ataque' FROM tiene INNER JOIN objetos WHERE tiene.nombrejug = '$username' AND tiene.usado = '1' AND tiene.nombreobj = objetos.nombreobj");
  $rowAttProt = mysqli_fetch_assoc($retAttProt);
  $att = $rowAttProt['ataque'];
  $prot = $rowAttProt['proteccion'];
  echo "<p><br><strong>Equipado: Ataque $att - Protección $prot</strong></p>";

  // Equipo en uso
  $retEquipo = db_query("SELECT objetos.niveluso, objetos.img, objetos.nombreobj, objetos.prot, objetos.ataq FROM tiene INNER JOIN objetos WHERE tiene.nombrejug = '$username' AND tiene.usado = '1' AND tiene.nombreobj = objetos.nombreobj ORDER BY FIELD(objetos.tipo, 'Arma', 'Amuleto', 'Mapa', 'Anillo', 'Botas', 'Escudo', 'Coraza', 'Perneras', 'Yelmo', 'Mascota', 'Poción', 'Recurso', 'Hechizo')");
  while ($rowEquipo = mysqli_fetch_assoc($retEquipo)) {
    echo "<form action='perfil.php' method='post' style='margin: 0px;'>";
    $img = $rowEquipo['img'];
    $nombreobj = $rowEquipo['nombreobj'];
    $att = $rowEquipo['ataq'];
    $prot = $rowEquipo['prot'];
    $nivel = $rowEquipo['niveluso'];
    echo "<button name='Quitar' value='$nombreobj' style='display:inline;'>Quitar</button>";
    echo "<img src='img/$img.gif' alt='$nombreobj' title='$nombreobj' style='vertical-align: middle;'>";
    echo "<p style='display:inline;'> $nombreobj (ataque: $att, protección: $prot, nivel: $nivel)</p>";
    echo "</form>";
  }

  // Funcion para hacer echo de los objetos, independientemente de su tipo
  function echoRetEquipo($retEquipo) {
    while ($rowEquipo = mysqli_fetch_assoc($retEquipo)) {
      echo "<form action='perfil.php' method='post' style='margin: 0px;'>";
      $img = $rowEquipo['img'];
      $nombreobj = $rowEquipo['nombreobj'];
      $usado = $rowEquipo['usado'];
      $att = $rowEquipo['ataq'];
      $prot = $rowEquipo['prot'];
      $cant = $rowEquipo['cantidad'];
      $nivel = $rowEquipo['niveluso'];
      if ($usado == 0) {
        echo "<button name='Usar' value='$nombreobj' style='display:inline;'>Usar</button>";
        echo "<img src='img/$img.gif' alt='$nombreobj' title='$nombreobj' style='vertical-align: middle;'>";
        echo "<p style='display:inline;'> $cant x $nombreobj (ataque: $att, protección: $prot, nivel: $nivel)</p>";
        echo "</form>";
      } else {
        echo "<button disabled style='display:inline;'>En uso</button>";
        // echo "<p style='display:inline; font-size: 0.8em;'>En uso</p>";
        echo "<img src='img/$img.gif' alt='$nombreobj' title='$nombreobj' style='vertical-align: middle;'>";
        echo "<p style='display:inline;'> $cant x $nombreobj (ataque: $att, protección: $prot, nivel: $nivel)</p>";
        echo "</form>";
      }
  
    }
  }

  function auxEchoRetEquipo($tipo) {
    $username = $_SESSION['username'];
    $retTipo = db_query("SELECT objetos.niveluso, tiene.cantidad, objetos.img, objetos.nombreobj, objetos.ataq, objetos.prot, tiene.usado FROM tiene INNER JOIN objetos WHERE tiene.nombrejug = '$username' AND tiene.nombreobj = objetos.nombreobj AND objetos.tipo = '$tipo' ORDER BY objetos.niveluso DESC");
    if (mysqli_num_rows($retTipo) > 0) {
      echo "<p><br><strong>- $tipo</strong></p>";
      echoRetEquipo($retTipo);
    }
  }

  /* --- EQUIPO NO EN USO --- */
  // Alimento
  
  // Armas
  echo "<p><br><br><strong>Equipo en mochila:</strong></p>";
  $tipos = array("Arma", "Amuleto", "Mapa", "Anillo", "Botas", "Escudo", "Coraza", "Perneras", "Yelmo", "Mascota", "Poción", "Recurso", "Hechizo");
  
  foreach ($tipos as $tipo) {
    auxEchoRetEquipo($tipo);
  }
  

  echo "";
  db_close();
}

function process_form() {
  if (!isset($_POST['insignia']) && !isset($_POST['Quitar']) && !isset($_POST['Usar']) && !isset($_POST['Dar'])) {
    return;
  }

  /* --- ADMIN GIVER --- */
  if (isset($_POST['Dar'])) {
    $objeto = $_POST['admin-giver'];
    db_connect();
    db_select_db();
    $username = $_SESSION['username'];
    $ret = db_query("SELECT * FROM objetos WHERE nombreobj = '$objeto'");
    if (mysqli_num_rows($ret) == 0) {
      $_SESSION['error'] = "No existe ese objeto.";
      db_close();
      return;
    }

    $ret = db_query("SELECT * FROM tiene WHERE nombrejug = '$username' AND nombreobj = '$objeto'");
    if (mysqli_num_rows($ret) > 0) {
      db_query("UPDATE tiene SET cantidad = cantidad + 1 WHERE nombrejug = '$username' AND nombreobj = '$objeto'");
    } else {
      db_query("INSERT INTO tiene VALUES ('$username', '$objeto', 1, 0)");
    }
    db_close();
    $_SESSION['msg'] = "Objeto dado correctamente.";
    header('Location: perfil.php');
    exit();
  }

  /* --- CAMBIO DE INSIGNIA ---*/
  if (isset($_POST['insignia'])) {    
    $insignia = $_POST['insignia'];
    db_connect();
    db_select_db();
    $username = $_SESSION['username'];
    $ret = db_query("SELECT objetos.img FROM tiene INNER JOIN objetos WHERE tiene.nombrejug = '$username' AND tiene.nombreobj = '$insignia' AND tiene.nombreobj = objetos.nombreobj;");
    if (mysqli_num_rows($ret) == 0) {
      $_SESSION["error"] = "No tienes esa insignia.";
      db_close();
      return;
    } else {
      $row = mysqli_fetch_assoc($ret);
      $img = $row['img'];
      db_query("UPDATE jugadores SET insignia = '$img' WHERE nombrejug = '$username'");
      db_close();
      $_SESSION["success"] = "Insignia cambiada correctamente.";
      header('Location: perfil.php');
      exit();
    }
    /* --- FIN CAMBIO DE INSIGNIA ---*/
  } 
  
  /* --- QUITAR OBJETO --- */
  else if (isset($_POST['Quitar'])) {
    $objeto = $_POST['Quitar'];
    db_connect();
    db_select_db();
    $username = $_SESSION['username'];
    $ret = db_query("SELECT * FROM tiene WHERE nombrejug = '$username' AND nombreobj = '$objeto' AND usado = '1'");
    if (mysqli_num_rows($ret) == 0) {
      $_SESSION['error'] = "No tienes o no estás usando ese objeto.";
      db_close();
      return;
    }
    db_query("UPDATE tiene SET usado = '0' WHERE nombrejug = '$username' AND nombreobj = '$objeto'");
    db_close();
    $_SESSION['msg'] = "Objeto quitado correctamente.";
    header('Location: perfil.php');
    exit();
  }
  /* --- FIN QUITAR OBJETO --- */

  /* --- USAR OBJETO --- */
  else if (isset($_POST['Usar'])) {
    $objeto = $_POST['Usar'];
    db_connect();
    db_select_db();
    $username = $_SESSION['username'];

    // Sacar nivel del usuario
    $ret = db_query("SELECT nivel FROM jugadores WHERE nombrejug = '$username'");
    $row = mysqli_fetch_assoc($ret);
    $nivel = $row['nivel'];
    
    // Sacar tipo del objeto
    $ret = db_query("SELECT objetos.niveluso, objetos.tipo FROM objetos WHERE nombreobj = '$objeto'");
    $row = mysqli_fetch_assoc($ret);
    $tipo = $row['tipo'];
    $niveluso = $row['niveluso'];

    // Comprobar si el objeto es de un nivel inferior al del usuario
    if ($niveluso > $nivel) {
      $_SESSION['error'] = "No puedes equipar un objeto de nivel superior al tuyo.";
      db_close();
      return;
    }
    
    // Comprobar si el usuario tiene el objeto y no lo está usando
    $ret = db_query("SELECT * FROM tiene WHERE nombrejug = '$username' AND nombreobj = '$objeto' AND usado = '0'");
    if (mysqli_num_rows($ret) == 0) {
      $_SESSION['error'] = "No tienes ese objeto o lo tienes equipado actualmente.";
      db_close();
      return;
    }
    
    // Si ya tiene un objeto del mismo tipo, quitarselo y colocarle el nuevo
    $ret = db_query("SELECT tiene.nombreobj FROM tiene INNER JOIN objetos WHERE tiene.nombrejug = '$username' AND tiene.usado = '1' AND tiene.nombreobj = objetos.nombreobj AND objetos.tipo = '$tipo'");
    if (mysqli_num_rows($ret) > 0) {
      $objactual = mysqli_fetch_assoc($ret)['nombreobj'];
      db_query("UPDATE tiene SET usado = '0' WHERE nombrejug = '$username' AND nombreobj = '$objactual'");
    }
    
    // Equiparle el objeto
    db_query("UPDATE tiene SET usado = '1' WHERE nombrejug = '$username' AND nombreobj = '$objeto'");
    db_close();
    $_SESSION['msg'] = "Objeto equipado correctamente.";
    header('Location: perfil.php');
    exit();
  }
  /* --- FIN USAR OBJETO --- */
}

?>
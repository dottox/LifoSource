<?php


// Username existe y está dentro de la base de datos
function checkPassword($username, $pass) {
	db_connect();
	db_select_db();
	
	$retval = db_query("SELECT password FROM jugadores WHERE nombrejug='{$username}'");
	
	$row = mysqli_fetch_assoc($retval);

	if (implode($row) == pwdHash($username, $pass)) {
		db_close();
		return true;
	}
	
	db_close();
	return false;
	
}

function isAdmin($username) {
  db_connect();
  db_select_db();
  
  $retval = db_query("SELECT admin FROM jugadores WHERE nombrejug='{$username}'");
  
  $row = mysqli_fetch_assoc($retval);
  
  if (implode($row) == 1) {
    db_close();
    return true;
  }
  
  db_close();
  return false;
}

// Rompe la sesión actual salvando el mensaje de error.
function logout() {
	session_start();
  $aux = $_SESSION["error"] ? $_SESSION["error"] : ($_SESSION["msg"] ? $_SESSION["msg"] : "");
	session_unset();
	session_destroy();

	session_start();
  $_SESSION["error"] = $aux;
}


// Se fija si la sesión es válida, checkeando tiempo de inactividad e IP.
// - retorna true si la sesión es válida o si no hay sesión.
// - retorna false si la sesión no es válida.
function checkSession() {

  // Se fija si existe la sesión.
	if (!isset($_SESSION) || !isset($_SESSION["loggedin"])) {
		return true;
	} 

  // Si la sesión existe, se fija si es válida.
  else {
		db_connect();
		db_select_db();
		$retval = db_query("SELECT iplogin, login FROM jugadores WHERE nombrejug='{$_SESSION["username"]}'");
		$row = mysqli_fetch_assoc($retval);

    // Si no se encuentra ningún jugador con ese nombre, la sesión no es válida.
    if (!$row) {
      $_SESSION["error"] = "Sesión no válida";
      logout();
      db_close();
      return false;
    }


    $login = $row['login'];
    $iplogin = $row['iplogin'];

    // Se fija si el tiempo de inactividad es mayor a 5 minutos.
    $time = time();
    if (($time - $login) > 300) {
      $_SESSION["error"] = "Sesión expirada por inactividad";
      db_close();
      logout();
      return false;
    } 
    
    // Se fija si la IP es la misma con la que se logeó.
    else {
      db_query("UPDATE jugadores SET login = '$time' WHERE nombrejug='{$_SESSION["username"]}'");
      db_close();

      if ($iplogin != $_SERVER['REMOTE_ADDR']) {
        $_SESSION["error"] = "Una sesión con la misma cuenta está activa en otro lugar";
        logout();
        return false;
      } else {
        return true;
      }
    }
      
    }
}

// Username existe y está dentro de la base de datos
function updateLogin($username, $ip) {
	db_connect();
	db_select_db();
	db_query("UPDATE jugadores SET iplogin='{$ip}' WHERE nombrejug='{$username}'");
	db_close();
}

function searchUserByUsername($username) {
	db_connect();
	db_select_db();
	$retval = db_query("SELECT * FROM jugadores WHERE nombrejug='{$username}'");
	
	if ($retval) {
		$row = mysqli_fetch_assoc($retval);
		mysqli_free_result($retval);
		db_close();
		return $row;
	}
	
	db_close();
	return false;
	

}

function validateUsername($username) {
	if (strlen($username) < 3 || strlen($username) > 15) {
		return false;
	}
	
	$valid_chars = array("A","B","C","D","E","F","G","H","I","J",
		 "K","L","M","N","O","P","Q","R","S","T",
		 "U","V","W","X","Y","Z","a","b","c","d",
		 "e","f","g","h","i","j","k","l","m","n",
		 "o","p","q","r","s","t","u","v","w","x",
		 "y","z","1","2","3","4","5","6","7","8",
		 "9","0");

	for ($i = 0; $i < strlen($username); $i++) {
		if (!in_array($username[$i], $valid_chars)) {
			return false;
		}
	}

	return true;
}

function validatePassword($password) {
	if (strlen($password) < 3 || strlen($password) > 15) {
		return false;
	}

	$valid_chars = array("A","B","C","D","E","F","G","H","I","J",
		 "K","L","M","N","O","P","Q","R","S","T",
		 "U","V","W","X","Y","Z","a","b","c","d",
		 "e","f","g","h","i","j","k","l","m","n",
		 "o","p","q","r","s","t","u","v","w","x",
		 "y","z","1","2","3","4","5","6","7","8",
		 "9","0", ".", "!", "?", "_");

	for ($i = 0; $i < strlen($password); $i++) {
		if (!in_array($password[$i], $valid_chars)) {
			return false;
		}
	}

	return true;
}

/*pwdHash($username,$password) Herramienta de cifrado de contraseña.*/
function pwdHash($username,$password) {
	global $confpwdsalt;
	return sha1("{$username}{$password}{$confpwdsalt}");
}


/*pwdgen(); Herramienta que genera una contraseña. */
function pwdgen() {
	$out = '';
	$string = 'abcdefghijklmnopqrstuvwxyz';
	$len = strlen($string);
	for ($i = 0;$i < 7;$i++) {
	  $out .= $string[rand(0,10)];
	}
	return $out;
}


/*ahora_dia($time) Conversor a dia en formato texto. */
function ahora_dia($time) {
	return date('d-m-Y', l_getdate($time));
}
  
  /*ahora_hora($time) Conversor a hora en formato texto. */
function ahora_hora($time) {
	return date('H:i:s', l_getdate($time));
}
  
function l_setdate($zh) {
	global $zonahhhh,$zonaact;
	$zonahhhh = $zh;
	if ($zh == -15)
	  $zonahhhh = -2;
	$zonahhhh -= $zonaact;
}

function l_getdate($tiempo) {
	global $zonahhhh;
	return $tiempo-$zonahhhh*3600;
}
 

function expsignivel($nivactual,$ultimossubio) {
	global $confnivel;
	$incremento = 0;
	if ($nivactual >= 40)
		$incremento += ($nivactual-39)*$confnivel*2;
	if ($nivactual >= 55)
		$incremento += ($nivactual-54)*$confnivel*2;
	if ($nivactual >= 60)
		$incremento += ($nivactual-59)*$confnivel*2;
	if ($nivactual >= 65)
		$incremento += ($nivactual-64)*$confnivel*2;
	if ($nivactual >= 69)
		$incremento += ($nivactual-68)*$confnivel*2;
  
	return $ultimossubio+$incremento+$nivactual*$confnivel;
}


/* ahora_tiempo($cuanto) Devuelve el tiempo. */
function ahora_tiempo($cuanto) {
	$horas = floor($cuanto/3600);
	$cuanto = $cuanto%3600;
	$minutos = floor($cuanto/60);
	$cuanto = $cuanto%60;
	$segundos = $cuanto;
  
	if ($horas < 10)
		$horas = "0{$horas}";
	if ($minutos < 10)
		$minutos = "0{$minutos}";
	if ($segundos < 10)
		$segundos = "0{$segundos}";
  
	return "{$horas}:{$minutos}:{$segundos}";
}

function puedeatacar($jugador) {
	global $time,$conforonivelataca,$conftp;
	$retval = db_query("SELECT * FROM {$conftp}jugadores WHERE oro>={$conforonivelataca}*nivel AND noatacarhasta<{$time} AND nombrejug='{$jugador}' AND energia>=5");
	$a = mysql_num_rows($retval);
	mysql_free_result($retval);
	return $a;
}

function combateinfo($nombre) {
	global $conftp,$time,$confnoatacarhastapremium,$confnoatacarhasta;
	$retval = db_query("SELECT oro,puntos,nivel,combates,vencedor,vencido,energia FROM {$conftp}jugadores WHERE nombrejug='{$nombre}'");
	$ret = mysql_fetch_row($retval);
	$juga['nombre'] = $nombre;
	$juga['oro'] = $ret[0];
	$juga['puntos'] = $ret[1];
	$juga['nivel'] = $ret[2];
	$juga['combates'] = $ret[3]+1;
	$juga['vencedor'] = $ret[4];
	$juga['vencido'] = $ret[5];
	$juga['energia'] = $ret[6];

	$retval = db_query("SELECT SUM(ataq),SUM(prot) FROM {$conftp}tiene,{$conftp}objetos WHERE usado=1 AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND nombrejug='{$nombre}'");
	$ret = mysql_fetch_row($retval);
	$juga['ataq'] = $ret[0]+floor($juga['nivel']/3)+10;
	$juga['prot'] = floor(($ret[1]+floor(($juga['nivel'])/10)+10)*0.6);
	mysql_free_result($retval);
  
	$retvox = db_query("SELECT {$conftp}tienemascotas.nombremascota,img,nivel,experiencia,alimento,ataquebase,defensabase,ataquenivel,defensanivel,expbase,expmult,expgana,maxnivel,usado,expgana FROM {$conftp}tienemascotas,{$conftp}mascotas WHERE {$conftp}tienemascotas.nombremascota={$conftp}mascotas.nombremascota AND nombrejug='{$nombre}' AND usado=1");
	if (mysql_num_rows($retvox)) {
		$rrr = mysql_fetch_row($retvox);
		$juga['mascota'] = $rrr[1];
		$juga['expmascota'] = $rrr[14];
		$juga['nombremascota'] = $rrr[0];
		$juga['ataqmascota'] = $rrr[5]+($rrr[7]*($rrr[2]-1));
		$juga['protmascota'] = $rrr[6]+($rrr[8]*($rrr[2]-1));
	}
	else {
		$juga['mascota'] = '';
		$juga['expmascota'] = 0;
		$juga['nombremascota'] = '';
		$juga['ataqmascota'] = 0;
		$juga['protmascota'] = 0;
	}
	$juga['ataq'] = $juga['ataq']+$juga['ataqmascota'];
	$juga['prot'] = $juga['prot']+$juga['protmascota'];
	$juga['ataqprotmascota'] = $juga['ataqmascota']+$juga['protmascota'];
	$juga['ataqprot'] = $juga['ataq']+$juga['prot'];
	mysql_free_result($retvox);
  
	return $juga;
}
  
function gestionaataque(&$tacante,&$tacado,$motivo) {
	if ($motivo) {
		$motivo = ' '.$motivo;
	}
	$mensaje = "<b>{$tacante['nombre']}</b> se lanza hacia <b>{$tacado['nombre']}</b>{$motivo}.<br/>";
  
	$ataque = rand(1,$tacante['ataq']*3);
	$defensa = rand(1,$tacado['prot']*3);
	if ($ataque > $defensa) {
	  $ataque = floor($ataque/5);
	  $defensa = floor($defensa/5);
		if ($ataque == 0)
			$ataque = 1;
		if ($defensa == 0)
			$defensa = 1;
		if (rand(1,5) == 5) {
			$ataque = rand(2,5)*$ataque;
			$tacado['vida'] = $tacado['vida']-$ataque;
			$mensaje .= "¡<b>{$tacante['nombre']}</b> asesta un golpe crítico a <b>{$tacado['nombre']}</b> por <b>{$ataque}</b> puntos de resistencia!<br/>";
	  	}
		else {
			$tacado['vida'] = $tacado['vida']-$ataque;
			$mensaje .= "¡<b>{$tacante['nombre']}</b> asesta un golpe a <b>{$tacado['nombre']}</b> por <b>{$ataque}</b> puntos de resistencia!<br/>";
	  	}
	}
	else {
		$mensaje .= "¡<b>{$tacado['nombre']}</b> detiene el ataque de <b>{$tacante['nombre']}</b>!<br/>";
	}
	if ($tacante['vida'] < 0)
		$tacante['vida'] = 0;
	if ($tacado['vida'] < 0)
		$tacado['vida'] = 0;
	$mensaje .= '<br/>';
	return $mensaje;
  }

function infocombate($tacante,$tacado) {
	return "<b>{$tacante['nombre']}</b> (puntos de resistencia: <b>{$tacante['vida']}</b>).<br/><b>{$tacado['nombre']}</b> (puntos de resistencia: <b>{$tacado['vida']}</b>).<br/><br/>";
}

function combate($atacante,$atacado,$aleat) {
	global $confnoatacarhasta,$time,$conforonivelataca,$confganacombexpmax,$confganacombexpmin,$conftp,$imgroot;
	$tacante = combateinfo($atacante);
	$tacado = combateinfo($atacado);
	$tacante['noatacarhasta'] = $time+$confnoatacarhasta;
  

  
	if (!$aleat)
	  $mensa2 = '<font color="#DDDDDD">_</font>';
	else
	  $mensa2 = '';
  
	$mensaje = "<b>¡Combate!</b> <img style=\"vertical-align:middle\" src=\"{$imgroot}img/{$tacante['insignia']}.gif\" alt=\"insignia\"/> <b>{$atacante}</b> (nivel {$tacante['nivel']}) ha atacado a <img style=\"vertical-align:middle\" src=\"{$imgroot}img/{$tacado['insignia']}.gif\" alt=\"insignia\"/> <b>{$atacado}</b> (nivel {$tacado['nivel']})<br/><br/>";
  
	$retval = db_query("SELECT {$conftp}objetos.img,{$conftp}objetos.nombreobj FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$atacante}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND {$conftp}tiene.usado=1 ORDER BY tipo ASC");
	$mensaje .= "<b>{$atacante}</b> está usando: "; 
	if ($tacante['mascota'])
	  $mensaje .= "<img src=\"{$imgroot}img/{$tacante['mascota']}.gif\" alt=\"{$tacante['nombremascota']}\"/>";
	$numrows = mysql_num_rows($retval);
	for ($i = 0;$i < $numrows;$i++) {
	  $ret = mysql_fetch_row($retval);
	  $mensaje .= "<img src=\"{$imgroot}img/{$ret[0]}.gif\" alt=\"{$ret[1]}\"/>";
	}
	$mensaje .= '<br/>';
	mysql_free_result($retval);
  
	$retval = db_query("SELECT {$conftp}objetos.img,{$conftp}objetos.nombreobj FROM {$conftp}tiene,{$conftp}objetos WHERE nombrejug='{$atacado}' AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND {$conftp}tiene.usado=1 ORDER BY tipo ASC");
	$mensaje .= "<b>{$atacado}</b> está usando: ";
	if ($tacado['mascota'])
	  $mensaje .= "<img src=\"{$imgroot}img/{$tacado['mascota']}.gif\" alt=\"{$tacado['nombremascota']}\"/>";
	$numrows = mysql_num_rows($retval);
	for ($i = 0;$i < $numrows;$i++) {
	  $ret = mysql_fetch_row($retval);
	  $mensaje .= "<img src=\"{$imgroot}img/{$ret[0]}.gif\" alt=\"{$ret[1]}\"/>";
	}
	$mensaje .= '<br/><br/>';
	mysql_free_result($retval);
  
  
	$orooblig = $conforonivelataca*$tacado['nivel'];
	if ($conforonivelataca*$tacante['nivel'] < $orooblig)
	  $orooblig = $conforonivelataca*$tacante['nivel'];
	$oromax = $tacado['oro'];
	if ($tacante['oro'] < $oromax)
	  $oromax = $tacante['oro'];
	$oromax = floor($oromax / 6);
	$orooblig = floor($orooblig / 6);
	$oroapuesta = rand(1,$oromax);
	if ($orooblig > $oroapuesta) {
	  $aux = $orooblig;
	  $orooblig = $oroapuesta;
	  $oroapuesta = $aux;
	}
	$oro_ganador = rand($orooblig,$oroapuesta);
	$exptacante = floor(($tacante['puntos']+$tacado['puntos']*rand(10,20))/4000);
	$exptacado = floor(($tacado['puntos']+$tacante['puntos']*rand(10,20))/4000);
	if (!$exptacante)
	  $exptacante = 10;
	if (!$exptacado)
	  $exptacado = 10;
  
	$tacante['vida'] = 10000;
	$tacado['vida'] = 10000;
	$turno = 1;
  
	if ($tacante['vida']>0 && $tacado['vida']>0) {
	if ($tacante['ataqprotmascota']>$tacado['ataqprotmascota']) {
	  $mensaje .= infocombate($tacante,$tacado);
	  $mensaje .= gestionaataque($tacante,$tacado,'porque tiene una mascota más fuerte');
	  $turno++;
	}
	else if ($tacado['ataqprotmascota']>$tacante['ataqprotmascota']) {
	  $mensaje .= infocombate($tacante,$tacado);
	  $mensaje .= gestionaataque($tacado,$tacante,'porque tiene una mascota más fuerte');
	  $turno++;
	}
	}
  
	if ($tacante['vida']>0 && $tacado['vida']>0) {
	if ($tacante['nivel']>$tacado['nivel']) {
	  $mensaje .= infocombate($tacante,$tacado);
	  $mensaje .= gestionaataque($tacante,$tacado,'porque tiene más nivel');
	  $turno++;
	}
	else if ($tacado['nivel']>$tacante['nivel']) {
	  $mensaje .= infocombate($tacante,$tacado);
	  $mensaje .= gestionaataque($tacado,$tacante,'porque tiene más nivel');
	  $turno++;
	}
	}
  
	if ($tacante['vida']>0 && $tacado['vida']>0) {
	if ($tacante['energia']>$tacado['energia']) {
	  $mensaje .= infocombate($tacante,$tacado);
	  $mensaje .= gestionaataque($tacante,$tacado,'porque tiene más energía');
	  $turno++;
	}
	else if ($tacado['energia']>$tacante['energia']) {
	  $mensaje .= infocombate($tacante,$tacado);
	  $mensaje .= gestionaataque($tacado,$tacante,'porque tiene más energía');
	  $turno++;
	}
	}
  
	if ($tacante['vida']>0 && $tacado['vida']>0) {
	if ($tacante['ataqprot']>$tacado['ataqprot']) {
	  $mensaje .= infocombate($tacante,$tacado);
	  $mensaje .= gestionaataque($tacante,$tacado,'porque tiene mejor ataque y protección');
	  $turno++;
	}
	else if ($tacado['ataqprot']>$tacante['ataqprot']) {
	  $mensaje .= infocombate($tacante,$tacado);
	  $mensaje .= gestionaataque($tacado,$tacante,'porque tiene mejor ataque y protección');
	  $turno++;
	}
	}
  
	if ($turno%2==0)
	  $turno--;
	while ($turno <= 10) {
	  if ($tacante['vida']>0 && $tacado['vida']>0) {
		$mensaje .= infocombate($tacante,$tacado);
		if ($turno%2==0)
		  $mensaje .= gestionaataque($tacado,$tacante,'');
		else
		  $mensaje .= gestionaataque($tacante,$tacado,'');
	  }
	  $turno++;
	}
  
	$mensaje .= infocombate($tacante,$tacado);
  
	if ($tacante['vida'] > $tacado['vida']) {
	  $mensaje .= "¡<b>{$atacante} ha ganado el combate!</b><br/>";
	  $ganador = 'atacante';
	}
	else {
	  $mensaje .= "¡<b>{$atacado} ha ganado el combate!</b><br/>";
	  $ganador = 'atacado';
	}
  
	if ($ganador == 'atacante') {
	  $tacante['vencedor'] = $tacante['vencedor']+1;
	  $tacado['vencido'] = $tacado['vencido']+1;
	  $tacante['oro'] = $tacante['oro'] + $oro_ganador;
	  $tacado['oro'] = $tacado['oro'] - $oro_ganador;
	  $mensaje .= "<b>¡{$atacante} roba a {$atacado} {$oro_ganador} monedas de oro!</b><br/>";
	  if ($tacante['mascota'])
		db_query("UPDATE tienemascotas SET experiencia=experiencia+{$tacante['expmascota']} WHERE nombrejug='{$atacante}' AND nombremascota='{$tacante['nombremascota']}'");
	}
	else {
	  $tacado['vencedor'] = $tacado['vencedor']+1;
	  $tacante['vencido'] = $tacante['vencido']+1;
	  $tacante['oro'] = $tacante['oro'] - $oro_ganador;
	  $tacado['oro'] = $tacado['oro'] + $oro_ganador;
	  $mensaje .= "<b>¡{$atacado} roba a {$atacante} {$oro_ganador} monedas de oro!</b><br/>";
	  if ($tacado['mascota'])
		db_query("UPDATE tienemascotas SET experiencia=experiencia+{$tacado['expmascota']} WHERE nombrejug='{$atacado}' AND nombremascota='{$tacado['nombremascota']}'");
	}

	$tacante['puntos'] = $tacante['puntos'] + $exptacante;
	$tacado['puntos'] = $tacado['puntos'] + $exptacado;
  
  
	if ($ganador == 'atacante') {
		$mensaje .= "<b>¡{$atacante} gana {$exptacante} puntos de experiencia!";
		if ($tacante['mascota'])
			$mensaje .= ' Su mascota se siente fuerte.';
		$mensaje .= "</b><br/>";
		
		$mensaje .= "<b>¡{$atacado} ha aprendido de este combate y gana {$exptacado} puntos de experiencia!</b><br/>";
	}
	else {
		$mensaje .= "<b>¡{$atacado} gana {$exptacante} puntos de experiencia!";
		if ($tacado['mascota'])
			$mensaje .= ' Su mascota se siente fuerte.';
		$mensaje .= "</b><br/>";

		$mensaje .= "<b>¡{$atacante} ha aprendido de este combate y gana {$exptacante} puntos de experiencia!</b><br/>";
  
	}
	db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$atacante}','@',$time,'{$mensaje}')");
	db_query("INSERT INTO {$conftp}mensajes (nombrejug,remitente,hora,mensaje) VALUES ('{$atacado}','@',$time,'{$mensaje}')");
  
	db_query("UPDATE {$conftp}jugadores SET oro={$tacante['oro']},noatacarhasta={$tacante['noatacarhasta']},combates={$tacante['combates']},vencedor={$tacante['vencedor']},vencido={$tacante['vencido']} WHERE nombrejug='{$atacante}'");
	db_query("UPDATE {$conftp}jugadores SET oro={$tacado['oro']},combates={$tacado['combates']},vencedor={$tacado['vencedor']},vencido={$tacado['vencido']} WHERE nombrejug='{$atacado}'");
  
	$rxx = db_query("SELECT nombrejug,{$conftp}tiene.nombreobj,img FROM {$conftp}tiene,{$conftp}objetos WHERE (nombrejug='{$atacante}' OR nombrejug='{$atacado}') AND usado=1 AND {$conftp}tiene.nombreobj={$conftp}objetos.nombreobj AND usos=1");
	$nr = mysql_num_rows($rxx);
	for ($i = 0;$i < $nr;$i++) {
	  $rex = mysql_fetch_row($rxx);
	  quita_objeto($rex[0],$rex[1],$rex[2]);
	}
	mysql_free_result($rxx);
  }
?>
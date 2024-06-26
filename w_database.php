<?php
$dbc = '';

/* db_connect() Conecta a la base de datos. Devuelve la conexión si todo ha ido bien. */
function db_connect() {
  global $confdbhost,$confdbport,$confdbuser,$confdbpass,$dbc;
  $dbc = mysqli_connect("{$confdbhost}:{$confdbport}",$confdbuser);
  if (!$dbc) {
    echo 'Error conectando a la BD. Parece que el servidor está muy cargado. Espera unos segundos y presiona actualizar en tu navegador.';
    exit();
  }
  return $dbc;
}

/* db_select_db() Selecciona una base de datos. Devuelve la selección si todo ha ido bien. */
function db_select_db() {
  global $confdbname,$dbc;
  return mysqli_select_db($dbc, $confdbname);
}

/* db_lock($tables) Bloquea las tablas. */
function db_lock($tables) {
  global $dbc;
  return mysqli_query($dbc,"LOCK TABLES {$tables}");
}

/* db_unlock() Desbloquea las tablas. */
function db_unlock() {
  global $dbc;
  return mysqli_query($dbc,"UNLOCK TABLES");
}

/* db_query($query) Ejecuta una consulta en el servidor y devuelve el resource. Devuelve algo si todo ha ido bien. */
function db_query($query) {
    global $dbc,$time;

    db_select_db();
    $res = mysqli_query($dbc,$query);
    $err = mysqli_error($dbc);
    if ($err) {
    $errno = mysqli_errno($dbc);
    switch ($errno) {
        case 145:
        case 1194:
        case 1195:
        case 1459:
        case 1034:
        case 1035:
        case 1013:
        case 1014:
        case 1016:
        case 1017:
        case 1023:
        case 1024:
        case 1026:
        case 1028:
        case 1039:
        $res1 = mysqli_query($dbc,"REPAIR TABLE fix");
        db_lock("fix WRITE");
        $rq = mysqli_query($dbc,"SELECT contador FROM fix WHERE contador<{$time}");
        if (mysqli_num_rows($rq)) {
            $time2 = $time+30;
            mysqli_query($dbc,"UPDATE fix SET contador={$time2}");
            db_unlock();
            db_fix();
        }
        else
            db_unlock();
        break;
        }
    }
    return $res;
}   

function db_fix() {
  global $dbc,$time;
  $query = mysqli_query("SHOW TABLES",$dbc);
  $tables = '';
  for ($i = 0;$i < mysqli_num_rows($query);$i++) {
   $r = mysqli_fetch_row($query);
   $tables .= "{$r[0]},";  
  }
  $tables = substr($tables,0,strlen($tables)-1);
  $timedel = $time-86400*7;
  //mysql_query("DELETE FROM mensajes WHERE hora<{$timedel}",$dbc);
  //mysql_query("DELETE FROM loginlog WHERE hora<{$timedel}",$dbc);
  $q2 = mysqli_query($dbc,"REPAIR TABLE {$tables}");
  $q3 = mysqli_query($dbc,"OPTIMIZE TABLE {$tables}");
  mysqli_free_result($query);
  mysqli_free_result($q2);
  mysqli_free_result($q3);
}

/* db_affected() Devuelve el número de columnas afectadas. */
function db_affected() {
  global $dbc;
  return mysqli_affected_rows($dbc);
}

/* db_close() Cierra la conexión con la base de datos. */
function db_close() {
  global $dbc;
  return mysqli_close($dbc);
}

?>
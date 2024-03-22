#!/usr/bin/php
<?php

header('Location: ../inicio.php');
exit();

include('../w_database.php');
include('../w_aux.php');
include('../w_config.php');

echo "<html><body>";
echo "Estableciendo conexión con el servidor MySQL.<br/>";
db_connect();

$engine = 'MYISAM';

/* 
echo "Borrando base de datos antigua si existe.<br/>";
db_query("DELETE FROM {$conftp}trabajos");
db_query("DELETE FROM {$conftp}jugadores");
db_query("DELETE FROM {$conftp}objetos");
db_query("DELETE FROM {$conftp}tiene");
db_query("DELETE FROM {$conftp}mensajes");
db_query("DELETE FROM {$conftp}loginlog");
db_query("DELETE FROM {$conftp}exploracion");
db_query("DELETE FROM {$conftp}mascotas");
db_query("DELETE FROM {$conftp}tienemascotas");
*/
echo "Seleccionando base de datos recién creada.<br/>";
db_select_db();

db_query("DROP TABLE IF EXISTS {$conftp}trabajos");
db_query("DROP TABLE IF EXISTS {$conftp}jugadores");
db_query("DROP TABLE IF EXISTS {$conftp}objetos");
db_query("DROP TABLE IF EXISTS {$conftp}tiene");
db_query("DROP TABLE IF EXISTS {$conftp}mensajes");
db_query("DROP TABLE IF EXISTS {$conftp}loginlog");
db_query("DROP TABLE IF EXISTS {$conftp}exploracion");
db_query("DROP TABLE IF EXISTS {$conftp}mascotas");
db_query("DROP TABLE IF EXISTS {$conftp}tienemascotas");

//echo "Creando base de datos nueva.<br/>";
//db_query("CREATE DATABASE {$confdbname}");


db_query("CREATE TABLE {$conftp}mascotas (
  nombremascota VARCHAR(40) NOT NULL PRIMARY KEY,
  nombreobj VARCHAR(40) NOT NULL,
  img VARCHAR(30) NOT NULL DEFAULT 'none',
  alimento VARCHAR(40) NOT NULL,
  ataquebase INT(20) NOT NULL,
  defensabase INT(20) NOT NULL,
  ataquenivel INT(20) NOT NULL,
  defensanivel INT(20) NOT NULL,
  expbase INT(20) NOT NULL,
  expmult INT(20) NOT NULL,
  expgana INT(20) NOT NULL,
  maxnivel INT(20) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}tienemascotas (
  nombrejug VARCHAR(30) NOT NULL,
  nombremascota VARCHAR(40) NOT NULL,
  nivel INT(20) NOT NULL,
  experiencia INT(20) NOT NULL,
  usado INT(1) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}trabajos (
  segundos INT(14) PRIMARY KEY,
  nombre VARCHAR(70) NOT NULL UNIQUE,
  puntos INT(20) NOT NULL,
  oro BIGINT(30) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}jugadores (
  nombrejug VARCHAR(30) PRIMARY KEY,
  password VARCHAR(40) NOT NULL,
  email VARCHAR(80) NOT NULL,
  creado INT(14) NOT NULL,
  login INT(14) NOT NULL DEFAULT 0,

  ipcreado VARCHAR(15) NOT NULL,
  iplogin VARCHAR(15) NOT NULL,

  nivel INT(10) NOT NULL DEFAULT 1,

  insignia VARCHAR(30) NOT NULL DEFAULT 'none',

  puntos INT(20) NOT NULL DEFAULT 0,

  oro BIGINT(30) NOT NULL DEFAULT 0,
  energia INT(20) NOT NULL DEFAULT 100,

  trabajando INT(14) NOT NULL DEFAULT 0,
  fintrabajo INT(14) NOT NULL DEFAULT 0,
  trabajado INT(14) NOT NULL DEFAULT 0,

  noatacarhasta INT(14) NOT NULL DEFAULT 0,
  noexplorarhasta INT(14) NOT NULL DEFAULT 0,
  nocomerhasta INT(14) NOT NULL DEFAULT 0,

  combates INT(10) NOT NULL DEFAULT 0,
  vencedor INT(10) NOT NULL DEFAULT 0,
  vencido INT(10) NOT NULL DEFAULT 0,
  
  zonahoraria INT(3) NOT NULL DEFAULT -15,

  admin INT(1) NOT NULL DEFAULT 0
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}objetos (
  nombreobj VARCHAR(40) PRIMARY KEY,
  tipo VARCHAR(10) NOT NULL,
  img VARCHAR(30) NOT NULL UNIQUE,
  nivelcomprar INT(10) NOT NULL,
  nivelencontrar INT(10) NOT NULL,
  niveluso INT(10) NOT NULL,
  valor BIGINT(30) NOT NULL,
  prot INT(20) NOT NULL,
  ataq INT(20) NOT NULL,
  posibilidad INT(10) NOT NULL,
  puntosencontrar INT(20) NOT NULL,
  usos INT(1) NOT NULL DEFAULT 0
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}tiene (
  nombrejug VARCHAR(30) NOT NULL,
  nombreobj VARCHAR(40) NOT NULL,
  cantidad INT(10) NOT NULL,
  usado INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (nombrejug,nombreobj)
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}mensajes (
  idmensaje INT(20) PRIMARY KEY AUTO_INCREMENT,
  nombrejug VARCHAR(30),
  remitente VARCHAR(30) NOT NULL,
  hora INT(14) NOT NULL,
  visto INT(1) NOT NULL DEFAULT 0,
  reportado INT(1) NOT NULL DEFAULT 0,
  mensaje TEXT
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}exploracion (
  mapa VARCHAR(40) NOT NULL,
  nombreobj VARCHAR(40) NOT NULL,
  probabilidad INT(10) NOT NULL,
  exito INT(5) NOT NULL,
  vez INT(10) NOT NULL,
  exacto INT(1) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE TABLE {$conftp}loginlog (
  nombrejug VARCHAR(30) NOT NULL,
  hora INT(14) NOT NULL,
  ip VARCHAR(30) NOT NULL
) ENGINE = {$engine} CHARACTER SET latin1;");

db_query("CREATE INDEX {$conftp}objetostipo ON {$conftp}objetos (tipo);");
db_query("CREATE INDEX {$conftp}objetosimg ON {$conftp}objetos (img);");
db_query("CREATE INDEX {$conftp}objetosnivelcomprar ON {$conftp}objetos (nivelcomprar);");
db_query("CREATE INDEX {$conftp}objetosnivelencontrar ON {$conftp}objetos (nivelencontrar);");
db_query("CREATE INDEX {$conftp}objetosniveluso ON {$conftp}objetos (niveluso);");
db_query("CREATE INDEX {$conftp}objetosvalor ON {$conftp}objetos (valor);");
db_query("CREATE INDEX {$conftp}objetosprot ON {$conftp}objetos (prot);");
db_query("CREATE INDEX {$conftp}objetosataq ON {$conftp}objetos (ataq);");

echo "Inicializando datos.<br/>";
include('e_datos.php');

echo "Cerrando conexión con el servidor MySQL.<br/>";
db_close();

echo "Mundo creado corréctamente en la base de datos `{$confdbname}`.<br/>";
echo "<font color=\"red\"><b>IMPORTANTE: ELIMINE EL SUBDIRECTORIO EMERGE INMEDIATAMENTE</b></font>";
echo "</body></html>";


?>
<?php
  // dirección raiz de la partida, debe ser accesible desde fuera.
  $root = 'http://localhost/';

  // host del servidor MySQL
  $confdbhost = 'localhost';
  // usuario para el servidor MySQL
  $confdbuser = 'root';
  // puerto del servidor MySQL
  $confdbport = '3306';
  // contraseña para el usuario
  $confdbpass = '';
  // nombre de la base de datos
  $confdbname = 'lifosource';

  // cadena de texto con cualquier valor para mejorar el cifrado de las contraseñas
  // NO CAMBIAR despues de hacer el emerge
  $confpwdsalt = 'pepito';

  // contraseña por defecto para la cuenta Admin
  $confadminpass = 'admin';
  // dirección de correo del administrador
  $confmail = 'admin@admin.com';
  
  // nombre del juego
  $conftitle = 'Partida de LifoSource';
  
  // aviso legal
  $confavisolegal = 'none';




  //Zona horaria del servidor
  $zonaact = -3;

  // Cabeceras y pies de página
  $confcontentstart = '';
  $conftitlebar = '<img src="/img/logo.png" alt="Logo" class="logo"/>';
  $confcontentend = '';
  $confcontentbeffooter = '';


  // Está abierta la partida?
  $confabierto = 1;
  // Está abierto el registro de jugadores?
  $confregistro = 1;

  // Modo debug?
  $confdebug = 0;

  // Experiencia necesaria para encontrar un objeto
  $confobj = 120;
  // Experiencia necesaria para subir un nivel
  $confnivel = 120;
  
  // Intervalo en segundos entre ataques
  $confnoatacarhasta = 2;
  // Tiempo de protección en segundos tras recibir ataque
  $confprotegidohasta = 2;
  // Oro mínimo para atacar
  $conforonivelataca = 2;
  // Diferencia máxima de niveles para atacar
  $confdifnivel = 100;
  // Máxima experiencia a ganar en un combate
  $confganacombexpmax = 600;
  // Mínima experiencia a perder en un combate
  $confganacombexpmin = -600;

  // Cuántos jugadores mostrar alrededor en la clasificación
  $confalredhigh = 3;
  // Cuántos clanes mostrar alrededor en la clasificación
  $confalredchigh = 3;

  // Intervalo en segundos entre comer  
  $confnocomerhasta = 2;

  // Intervalo en segundos entre explorar 
  $confnoexplorarhasta = 2;



  // no es necesario modificar estos valores
  $imgroot = $root;
  $confurl = $root;
  $conftitlebar = '<img src="'.$root.'/img/logo.png" alt="Logo" class="logo"/>';
  $confadminmail = $confmail;
  $confmailn = $conftitle;
  $confmailname = $conftitle;
  $confsmtpuser = '';
  $confsmtppass = '';
  $confsmtp = '127.0.0.1';
  $conftp = '';

?>
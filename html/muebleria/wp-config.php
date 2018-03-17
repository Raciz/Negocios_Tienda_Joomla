<?php
/** 
 * Configuración básica de WordPress.
 *
 * Este archivo contiene las siguientes configuraciones: ajustes de MySQL, prefijo de tablas,
 * claves secretas, idioma de WordPress y ABSPATH. Para obtener más información,
 * visita la página del Codex{@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} . Los ajustes de MySQL te los proporcionará tu proveedor de alojamiento web.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

define('FS_METHOD', 'DIRECT');
// ** Ajustes de MySQL. Solicita estos datos a tu proveedor de alojamiento web. ** //
/** El nombre de tu base de datos de WordPress */
define('DB_NAME', 'Muebleria');

/** Tu nombre de usuario de MySQL */
define('DB_USER', 'root');

/** Tu contraseña de MySQL */
define('DB_PASSWORD', 'negocios');

/** Host de MySQL (es muy probable que no necesites cambiarlo) */
define('DB_HOST', 'localhost');

/** Codificación de caracteres para la base de datos. */
define('DB_CHARSET', 'utf8mb4');

/** Cotejamiento de la base de datos. No lo modifiques si tienes dudas. */
define('DB_COLLATE', '');

/**#@+
 * Claves únicas de autentificación.
 *
 * Define cada clave secreta con una frase aleatoria distinta.
 * Puedes generarlas usando el {@link https://api.wordpress.org/secret-key/1.1/salt/ servicio de claves secretas de WordPress}
 * Puedes cambiar las claves en cualquier momento para invalidar todas las cookies existentes. Esto forzará a todos los usuarios a volver a hacer login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', '_?8jP7[%:?K.]+dYYKF+Ggo}LWCXR+mumymh>Vloa~x.Nrnz$mjwCR)pCA[vkmNx');
define('SECURE_AUTH_KEY', '{:G49OZHX6a3,2D?[<Te&wi6g7ND4l`9,ax@23bYvecc[ (BG8JXKwfo//KREvn7');
define('LOGGED_IN_KEY', '@-wp~n$Xs>^El*Ro&S.ooM}J%K+krFrP,S[iNfJ]7}d$J2`MMe&HY[hU[i-3a|CD');
define('NONCE_KEY', '20zRAMoN@MmUU1H1ZV^bI9zPbP2HV921x[.*t[9C }cO,LBf..SvG+_*8{dOc-$*');
define('AUTH_SALT', ' I[AGD-Nv(o:#+B([qu1ad)M:7}EO pg=u|zsXodr<gb|7La>RYVL+LXWWWo(N~Z');
define('SECURE_AUTH_SALT', 'l<[@>4TOGos7Cv6</?BJ/Tp1&]IVvMsT/&EIDL0:y%+5lq8Zj@Z@*MRN>1y~TX4i');
define('LOGGED_IN_SALT', 'kQ|zdK?Z0x8,?bND.]o[@@n6HLEL@~6<b9Np^MOL4GU+KE=ILG0~g~[{(Rbwy#<*');
define('NONCE_SALT', 'q$/h.SO^^=bGDWV $Z)T$WL4=ULIwpn,@T+Ll<o1b j==WxwPWr{r7.?WXXy!.U!');

/**#@-*/

/**
 * Prefijo de la base de datos de WordPress.
 *
 * Cambia el prefijo si deseas instalar multiples blogs en una sola base de datos.
 * Emplea solo números, letras y guión bajo.
 */
$table_prefix  = 'wp_';


/**
 * Para desarrolladores: modo debug de WordPress.
 *
 * Cambia esto a true para activar la muestra de avisos durante el desarrollo.
 * Se recomienda encarecidamente a los desarrolladores de temas y plugins que usen WP_DEBUG
 * en sus entornos de desarrollo.
 */
define('WP_DEBUG', false);

/* ¡Eso es todo, deja de editar! Feliz blogging */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');


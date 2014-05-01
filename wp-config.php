<?php

// Determine instance for dev or prod environments
$instance = (preg_match("~\.net~i", $_SERVER['HTTP_HOST'])) ? 'dev' : 'prod';

// Determine instance for CMS environment
if($_SERVER['HTTP_HOST'] == 'alpha-cms.eardish.net' || $_SERVER['HTTP_HOST'] == 'alpha-web.eardish.net') {
  $instance = 'prod';
}

// Determine instance for qa environment
if($_SERVER['HTTP_HOST'] == 'alpha-qa-web.eardish.net') {
  $instance = 'qa';
}

// Parse out developer name from instance in dev environments
$developer = ($instance == 'dev') ? preg_replace("~-web\.eardish\.net.*$~i", '', $_SERVER['HTTP_HOST']) : NULL;

// Setup edhost var for full url in code
//$edhost = ($instance == 'prod') ? 'eardish.com' : $_SERVER['HTTP_HOST'];
$edhost = $_SERVER['HTTP_HOST'];

// Set up wordpress url overrides
define('WP_HOME', "http://{$edhost}");
define('WP_SITEURL', "http://{$edhost}");

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
switch($instance) {
  case 'prod':
  	define('DB_HOST', 'alpha-db.eardish.net');
  	define('DB_USER', 'alphawebdb');
  	define('DB_PASSWORD', 'GLLTtpr5dbKLL');
  	define('DB_NAME', 'alphawebdb');
  	define('MFP_HOST', 'mfp.eardish.com');
    define('CDN', 'cdn.eardish.net');
    define('CDNA', 'cdna.eardish.net');
    define('CDNI', 'cdni.eardish.net');
    define('CDNV', 'cdnv.eardish.net');
	  break;
  case 'qa':
  	define('DB_HOST', 'alpha-db.eardish.net');
  	define('DB_USER', 'qaalphawebdb');
  	define('DB_PASSWORD', 'GLLTtpr5dbKLL');
  	define('DB_NAME', 'qaalphawebdb');
  	define('MFP_HOST', 'alpha-qa-mfp.eardish.net');
    define('CDN', 'devcdn.eardish.net');
    define('CDNA', 'devcdna.eardish.net');
    define('CDNI', 'devcdni.eardish.net');
    define('CDNV', 'devcdnv.eardish.net');
	  break;
  case 'dev':
  	define('DB_HOST', 'alpha-db.eardish.net');
  	define('DB_USER', 'devalphawebdb');
  	define('DB_PASSWORD', 'GLLTtpr5dbKLL');
  	define('DB_NAME', 'devalphawebdb');
  	define('MFP_HOST', $developer.'-mfp.eardish.net');
    define('CDN', 'devcdn.eardish.net');
    define('CDNA', 'devcdna.eardish.net');
    define('CDNI', 'devcdni.eardish.net');
    define('CDNV', 'devcdnv.eardish.net');
	  break;
}

/*** CDN Setup ***/

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**
 * Adding this to set config for time date
 * @TODO dont know if this overrides any other settings or has issues.
 * @TODO this is also defined in other areas as UTC.
 * 
 */
define('TIMEZONE', 'UTC');
date_default_timezone_set(TIMEZONE); 

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '3IIeZ|zfoY|Z/-<nq7zF.T`^w64y||XBl`,J^>66(* Tq@|9Bk4+B.&D)m+IGQ(c');
define('SECURE_AUTH_KEY',  'zo20dNho_0gt%4GxSz$[3s{{nH]p^t$ATPa/{g-lWlg;%w}9k/%S5PR2L<Ij]UOx');
define('LOGGED_IN_KEY',    ']VRWG-*[M>~.o:vEsX4ttR| 0=u{P:/wOK ;U.tM+sT2 50Icjb.J5:bd%F.z|8k');
define('NONCE_KEY',        'F[407Z2aNRB-X,^YAZ6[f@@=y~%`n(FKx=(F3#Fz3!iGz6p1YvI|[{SCMV<+5@AC');
define('AUTH_SALT',        'BP#^h#]B|%}u{,eFrR3 $rb`E8xkZNS>lTetxV d,apR]9Qm3x>^<<#3oqv+9WFX');
define('SECURE_AUTH_SALT', 'liLgatee$LNoXlSz@0_ef(DE?@Vz,x0SBlr2~vyCX s4oAzI`_vyK-3-sQP(eD4n');
define('LOGGED_IN_SALT',   '^!FCvlM;q,1|ZVWfx+v-{F,oI:p+NG_|v|YZc-O<,^L``{Z7F7RgRzzE$Cfxoqno');
define('NONCE_SALT',       'U`[rkoCUP9DKx2/_B$uxqM0h7 N6:n&5.2M~B.~zr}W{67 w=D$fEmY)(Ox5^W|N');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

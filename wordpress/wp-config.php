<?php
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
/** The name of the database for WordPress */
define('DB_NAME', 'db_mixfs');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '111');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Jn|*Pp0m^^b:C7>?UR^nm005(To(X~p|I$=jl3@%O4DheGacHCQ/sFA.{$H[TDy-');
define('SECURE_AUTH_KEY',  'fD2$tFqZ6FsjN](M0Zc)<YgtVQ-37]y!U-CWexIa#uueJtU(2moCZV/yj~:~Rk;X');
define('LOGGED_IN_KEY',    'OipS)htr y&A}cjcK@vZl<v`W.B$vW7eM,va3<aZ8YJzFj8jd{9[TJ2jd^P1-TvC');
define('NONCE_KEY',        'W<aqjWVu)SQES!MgE`97;i%RdG Gm<DM?K%XPtcV.:hEai-mHX~ts:[r`9mvh(cF');
define('AUTH_SALT',        '/UGj9pgI&N;&$Naz%^[h6GKsVmQ90t^VB=B+~%f%%ZyS+vXkDP*OILnj9#:@{b/%');
define('SECURE_AUTH_SALT', 'u VwWSG! 9P+Z1vk25YCYWCdlr/0JiJuIRbfcw>f{:I&u1:p7laWm;dB5D_hI*SP');
define('LOGGED_IN_SALT',   '0KOUJh<b/&QVCFLR_Y!UX:hE,&1kP?@br,V64}r,Xzt,Hmydom0n:K:1my]5io%l');
define('NONCE_SALT',       'ekgu+^vObUkW(z}Oc,H(|q fp9#*zv^[&G@Q3q0+%)[4~?G[^(k1:f[;fVq4(^V4');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'fs_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', 'zh_CN');

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

<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'Kinszowka_panel');

/** MySQL database username */
define('DB_USER', 'server');

/** MySQL database password */
define('DB_PASSWORD', '2zXAnbJYjATmCrRE');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'Jur+n 2#MJF$/E3,A8*F^,:+t=!6Y)[SfE6AAI=5OpR#n%]AumO*1G7(<T5.YFKZ');
define('SECURE_AUTH_KEY',  '+*a/3yh)dq$Pt-fAcBXz+^K~oJpADFAs6GSOw}0843Vz@c,[.lw:lec/e`>$#S0<');
define('LOGGED_IN_KEY',    '-<V_NMi9jpP-LdTF.@|.-{!(Uy6rSbrs`hu5{%;}O>/<x5Y6#y3Y#pSn- U-yNYl');
define('NONCE_KEY',        'b=2-$o!1K>u%47{qBd41(q,zPX9bfFA+Z}}^D*D=}@%eTK:96+rBxU3daSg[Ax`J');
define('AUTH_SALT',        'q^9yuaww)BDl%2Y=tA1hFHs+2B75.BuuS-e_$>%-U,]ZOp@h=5|(u<t;R?;-Z8%T');
define('SECURE_AUTH_SALT', 'C<(Wf4!&-Dv+?P<F};4MD`ZoC?e]X}Fn$}MHtJEJl}8|Ni%t5)wPQ$`*:vf#Ac#<');
define('LOGGED_IN_SALT',   '^P9y}V~.l1zpW0~B4(5Mw-1#7F+?oamDge3nL=IkFdc^%CT^yN`Y8?>A*zbJPzwJ');
define('NONCE_SALT',       '$So-9y@(OP?Hx;QcL=d]DdBB}Q8n}{w_|i/nmW*9ho_x.G*l%U3n]4 cO/yP&JvW');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

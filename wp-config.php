<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '}?l7I_~nu!qG@N_{sLE8jT|^/G`cX[vXclQZOy_x[Exf>3pg8to/py?J@`V{!HW&' );
define( 'SECURE_AUTH_KEY',   'vk[B^cGB./Bm{<=swB0w$!c@|g|-`&`M(Rg+,:yFXgz5jF<bj<2&!1BGIWva+bHt' );
define( 'LOGGED_IN_KEY',     ']37;t/A00+z~Jd&kkoe_=|jR]/ppKBnN|$#@Ur<{8h&>+I;%sl../Dp7Ll&7xJ~;' );
define( 'NONCE_KEY',         '{nnYI[qx8YKl87}l;V%t1F6ZNe+kmM2dCis7+ea$[@NRa$[~cgyA_qT# Z]`i[E7' );
define( 'AUTH_SALT',         'tf^Zu1Mm-A5JBXwA[@]%=4[Yd4wCxH_xl+.PbIkdH<VKo>V$@Ixd!>AEU=RQBD|<' );
define( 'SECURE_AUTH_SALT',  '{ocn!=zA0uET2fJJp85}F)M{R9haJ.2bRLmb$U<;utvXw}iE:>;ej?)!mNWryc{?' );
define( 'LOGGED_IN_SALT',    '+|YV.PhFRNp+^pp7,%X +_![1$Xx~kd#F!FbzVwm^!7!^8673^Ni&~lZb.BMvC& ' );
define( 'NONCE_SALT',        'D`cA|&hM3A/9kxL}UFpo*]bGcLwVcnB$]Arw$qwXo w~_kPfJx=[%04]w&m)KuWC' );
define( 'WP_CACHE_KEY_SALT', 'VWU^X<z+?|=/>9lUv1E6XRKf(=`%00gh@bA?X:.13ksOYx[b}0V[f#OD@`nij;(,' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

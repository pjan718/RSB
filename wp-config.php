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
define('DB_NAME', 'rsb');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'flushingsb3st');

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
define('AUTH_KEY',         'T2`JE18FQ|P;|fZFXv^(`SlnM5xamgGz8Z`fkZ%/e|)ZstVWj r`0Xz~HX(iT5mv');
define('SECURE_AUTH_KEY',  ' d9c65[7Oq,^JtX*2?jF3;6Q&ORAc<1>5c,g@Q(ZXQHk[_({ Ai>JoK/(J$G;%@!');
define('LOGGED_IN_KEY',    'ZHfp1~b76Lt9CjN,nP&_5Q&jiEfES@k%$rPSaI<B59a!1M./=m.*ajU?d:@PcJ>P');
define('NONCE_KEY',        'g5BOCH68bhU4 ,cz*vviUO:|)/o WC:|yH>y<T4j(/ay~e%k]*UJ1bGB2thBdRV4');
define('AUTH_SALT',        '_$,F= 4/^?AF:E$+3}t%py]3t1PFG)>D%=2oQ1b?-W64*_8/s[)_|T(Jkm^5swH,');
define('SECURE_AUTH_SALT', '@e2<Q5;m|#wOP_(dO57^(c])`t1cf#n+Y xQ $:#S2S#1%uAso=Wh721V~vf,&Ez');
define('LOGGED_IN_SALT',   '%HlY|joxrj~iC)|9V(TS Q^iXfh-A@#m-f.=[5Qq!;rdrJeS F0hVBb`>I/$NH{@');
define('NONCE_SALT',       '2uor0wR:`Sgw4yT~CmS0.;S&_ar7akMoa(&_MieQob_`lw5`wk9P2WQ|vY7;&Cm^');

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

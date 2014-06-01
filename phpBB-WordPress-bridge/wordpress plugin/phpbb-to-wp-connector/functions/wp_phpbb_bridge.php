<?php
/** 
* @package phpBB to WP connector
* @version $Id: 1.5.0
* @copyright (c) 2013-2014 danielx64.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author Danielx64
 * @based off: phpBB 3.0.9 :: BRIDGE phpBB & WordPress -> WordPress root/wp-content/themes/phpBB/includes
 * @orginal author: leviatan21 - http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
 * 
 */

/**
* @ignore
**/

/**
 * Hierarchy :
 * 
 * functions.php
 * includes/wp_phpbb_bridge.php
 * includes/wp_phpbb_bridge_core.php
 * index.php
 */

define('IN_WP_PHPBB_BRIDGE', true);
define('WP_PHPBB_BRIDGE_ROOT',  dirname( __FILE__ ) . '/');
define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
define('WP_TABLE_PREFIX', $table_prefix);

// Make this variable global before initialize phpbb
if (function_exists('wp_get_current_user'))
{
	wp_set_current_user(0);
	$wp_user = wp_get_current_user();
}

// Version number (only used for the installer)
@define('WP_PHPBB_BRIDGE_VERSION', '0.0.9');

// Without this we cannot include phpBB 3.0.x scripts.
if (!defined('IN_PHPBB'))
{
	define('IN_PHPBB', true);
}


// Include core classes
if (!file_exists(WP_PHPBB_BRIDGE_ROOT . 'wp_phpbb_bridge_core.' . PHP_EXT))
{
	die('<p>No "Bridge" core found. Check the "'. WP_PHPBB_BRIDGE_ROOT . 'wp_phpbb_bridge_core.' . PHP_EXT . '" file.</p>');
}
require(WP_PHPBB_BRIDGE_ROOT . 'wp_phpbb_bridge_core.' . PHP_EXT);

// Initialise settings
bridge::set_config();
$propress_options = get_option( 'phpbbtowp' );

// Include common phpBB files and functions.
if (!file_exists($propress_options['phpbb_path']. 'common.' . PHP_EXT))
{
	die('<p>No "phpBB" common found. Check the "' . PHPBB_ROOT_PATH . 'common.' . PHP_EXT . '" file.</p>');
}
require($propress_options['phpbb_path'] . 'common.' . PHP_EXT);

if (!defined('PHPBB_USE_BOARD_URL_PATH'))
{
	@define('PHPBB_USE_BOARD_URL_PATH', true);
}

// Initialise phpbb
phpbb::initialise();

//phpbb::$user->add_lang(array('viewtopic', 'posting', 'ucp', 'mods/wp_phpbb_bridge'));

@define('PHPBB_INCLUDED', true);


/**
 * A function with a very simple but powerful method to encrypt a string with a given key.
 * 
 * 	Usage : $sring_encrypted = encrypt("String to Encrypt", "Secret Key");
 * 	Based off : http://www.emm-gfx.net/2008/11/encriptar-y-desencriptar-cadena-php/
 * 	Updated to work in WP by leviatan21
 *
 * @param (string)	$string		String to Encrypt
 * @param (string)	$key		Secret Key			( Options : AUTH_KEY, SECURE_AUTH_KEY, LOGGED_IN_KEY, NONCE_KEY, AUTH_SALT, SECURE_AUTH_SALT, LOGGED_IN_SALT, NONCE_SALT )
 * @return (string)	encrypted string
 */
function wp_phpbb_encrypt($string = '', $key = SECURE_AUTH_SALT)
{
	// Load pluggable functions.
	if (!function_exists('wp_salt'))
	{
		require(ABSPATH . WPINC . '/pluggable.php');
	}

	$result = '';
//	$key = "Secret Key";
//	$key = phpbb::$user->session_id;
	$key = wp_salt($key);
//	$key = utf8_normalize_nfc(request_var('key', $key, true));

	for ($i = 0; $i < strlen($string); $i++)
	{
		$char	 = substr($string, $i, 1);
		$keychar = substr($key, ($i % strlen($key)) -1, 1);
		$char	 = chr(ord($char) + ord($keychar));
		$result .= $char;
	}
	return base64_encode($result);
}

/**
 * A function with a very simple but powerful method to decrypt a string with a given key.
 * 
 * 	Usage : $sring_decrypt = decrypt("String to decrypt", "Secret Key");
 * 	Based off : http://www.emm-gfx.net/2008/11/encriptar-y-desencriptar-cadena-php/
 * 	Updated to work in WP by leviatan21
 *
 * @param (string)	$string		String to decrypt
 * @param (string)	$key		Secret Key			( Options : AUTH_KEY, SECURE_AUTH_KEY, LOGGED_IN_KEY, NONCE_KEY, AUTH_SALT, SECURE_AUTH_SALT, LOGGED_IN_SALT, NONCE_SALT )
 * @return (string)	decrypted string
*/
function wp_phpbb_decrypt($string = '', $key = SECURE_AUTH_SALT)
{
	// Load pluggable functions.
	if (!function_exists('wp_salt'))
	{
		require(ABSPATH . WPINC . '/pluggable.php');
	}

	$result = '';
//	$key = "Secret Key";
//	$key = phpbb::$user->session_id;
	$key = wp_salt($key);
//	$key = utf8_normalize_nfc(request_var('key', $key, true));
	$string = base64_decode($string);

	for ($i = 0; $i < strlen($string); $i++)
	{
		$char	 = substr($string, $i, 1);
		$keychar = substr($key, ($i % strlen($key)) -1, 1);
		$char	 = chr(ord($char) - ord($keychar));
		$result .= $char;
	}
	return $result;
}

?>
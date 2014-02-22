<?php
/**
 * 
 * @package: phpBB 3.0.9 :: BRIDGE phpBB & WordPress -> WordPress root/wp-content/themes/phpBB/includes
 * @version: $Id: wp_phpbb_bridge_core.php, v0.0.9 2011/10/01 11:10:01 leviatan21 Exp $
 * @copyright: leviatan21 < info@mssti.com > (Gabriel) http://www.mssti.com/phpbb3/
 * @license: http://opensource.org/licenses/gpl-license.php GNU Public License 
 * @author: leviatan21 - http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
 * 
 */

/**
 * @ignore
 */

if (!defined('IN_WP_PHPBB_BRIDGE'))
{
	exit;
}

class bridge
{
	/**
	 * Bridge configuration member
	 *
	 * @var bridge_config
	 */
	public static $config;

	/**
	 * Reads a configuration file with an assoc. config array
	 *
	 * @param boolean $force	force to update WP settings
	 */
	public static function set_config($force = false)
	{
		global $wp_phpbb_bridge_config;

		// Some default options
	$propress_options = get_option( 'theme_propress_options' );
	$phpbb_root_path =  $propress_options['phpbb_script_path'];

		// bypass our own settings
		$path	 = $phpbb_root_path;


		// Measn the plugin is not enabbled yet!
		// or the plugin is not set yet!

		// Check against WP settings
		$wp_phpbb_bridge_settings = $propress_options;

		// If checks fails, display the proper message


		if (defined('WP_ADMIN') && WP_ADMIN == true)
		{
			define('PHPBB_ROOT_PATH', '../' . $path);
		}
		else
		{
			define('PHPBB_ROOT_PATH', $path);
		}

		self::$config = $wp_phpbb_bridge_config;

		// Make that phpBB itself understands out paths
		global $phpbb_root_path, $phpEx;
	$propress_options = get_option( 'theme_propress_options' );

		$phpbb_root_path = $propress_options['phpbb_script_path'];
		$phpEx = PHP_EXT;
	}
	/**
	 * Check the Bridge settings...
	 *
	 * @param (bolean) $active
	 * @param (string) $path
	 * @param (string) $theme
	 * @return (array)
	 */
	public static function wp_phpbb_bridge_check($active = false, $path = '../phpBB/', $theme = '')
	{
		$error = false;
		$message = '';
		$action = '';


		return array('error' => $error, 'message' => $message, 'action' => $action);
	}
}

/**
 * phpBB class that will be used in place of globalising these variables.
 * 
 * Based off : Titania 0.3.11
 * * File : titania/includes/core/phpbb.php
 */
class phpbb
{
	/** @var auth phpBB Auth class */
	public static $auth;

	/** @var cache phpBB Cache class */
	public static $cache;

	/** @var config phpBB Config class */
	public static $config;

	/** @var db phpBB DBAL class */
	public static $db;

	/** @var template phpBB Template class */
	public static $template;

	/** @var user phpBB User class */
	public static $user;

	/**
	 * Absolute Wordpress and phpBB Path
	 *
	 * @var string
	 */
	public static $absolute_phpbb_script_path;
	public static $absolute_wordpress_script_path;
	public static $absolute_phpbb_url_path;
	/**
	 * Static Constructor.
	 */
	public static function initialise()
	{
		//global $wpdb;
		//$wpdb = self::wp_phpbb_get_wp_db();
		
		global $auth, $config, $db, $template, $user, $cache;

		self::$auth		= &$auth;
		self::$config	= &$config;
		self::$db		= &$db;
		self::$template	= &$template;
		self::$user		= &$user;
		self::$cache	= &$cache;

		// Set the absolute wordpress/phpbb path
		$propress_options = get_option( 'theme_propress_options' );
		self::$absolute_phpbb_script_path = $propress_options['phpbb_script_path'];
//		self::$absolute_wordpress_script_path = phpbb::$config['wordpress_script_path'];
		self::$absolute_phpbb_url_path = phpbb::$config['wp_phpbb_bridge_board_path'];

		// enhance phpbb $config data with WP $config data
		self::wp_get_config();

		// Start session management
		if (!defined('PHPBB_INCLUDED'))
		{
			self::$user->session_begin();
			self::$auth->acl(self::$user->data);
			self::$user->setup();
		}

		$action = request_var('action', '');
		if (($action != 'logout' || $action != 'log-out') && !defined('PHPBB_INAJAX'))
	//	if ($action != 'logout' || $action != 'log-out')
		{
			self::wp_phpbb_sanitize_user();
		}

	}

	/**
	 * Load the correct database class file.
	 *
	 * This function is used to load the database class file either at runtime or by
	 * wp-admin/setup-config.php. We must globalize $wpdb to ensure that it is
	 * defined globally by the inline code in wp-db.php.
	 *
	 * @since 2.5.0
	 * @global $wpdb WordPress Database Object
	 * 
	 * Based off : wordpress 3.1.3
	 * File : wordpress/wp-includes/load.php
	 */
	public static function wp_phpbb_get_wp_db()
	{
		global $wpdb;

		require_once(ABSPATH . WPINC . '/wp-db.php');

		if (@file_exists(WP_CONTENT_DIR . '/db.php'))
		{
			require_once(WP_CONTENT_DIR . '/db.php');
		}

		$wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

		$wpdb->set_prefix(WP_TABLE_PREFIX);

		return $wpdb;
	}

	/**
	 * Update phpbb user data with wp user data
	 * 	And update wp user data with phpbb user data
	 *
	 * based off the WP add-on by Jason Sanborn <jsanborn@simplicitypoint.com> http://www.e-xtnd.it/wp-phpbb-bridge/
	 */
	public static function wp_phpbb_sanitize_user()
	{
		global $wp_user;

		$wp_user_id = self::wp_phpbb_get_userid();

	//	if ($wp_user_id != 0)
	//	if ($wp_user_id <= 0 && is_user_logged_in())
		if (!phpbb::$user->data['is_registered'])
		{
			wp_logout();
		//	wp_redirect('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		}
		else if ($wp_user_id > 0 && $wp_user_id != $wp_user->ID)
		{
			wp_set_current_user($wp_user_id);
			wp_set_auth_cookie($wp_user_id, true, false);
		}

		// Get the WP user data
		//$wp_user_data = get_userdata($wp_user_id);
		$wp_user_data = get_userdata($wp_user->ID);

		if (!isset($wp_user_data->phpbb_userid) || $wp_user_data->phpbb_userid == 0 || $wp_user_data->phpbb_userid != self::$user->data['user_id'])
		{
			$wp_user_data = self::$user->data['user_id'];
			update_metadata('user', $wp_user_id, 'phpbb_userid', $wp_user_data);
		}

		// If all went fine, we doesnt' needed this values anymore (added at functions.php -> function wp_phpbb_phpbb_loginbox_head())
		if (isset($wp_user_data->phpbb_userid) && isset($wp_user_data->WPphpBBlogin))
		{
			delete_user_meta($wp_user_id, 'WPphpBBlogin');
		}

		$default_userdata = array(
			'ID'			=> $wp_user_id,
			'phpbb_userid'	=> self::$user->data['user_id'],
			'user_url'		=> self::$user->data['user_website'],
			'user_email'	=> self::$user->data['user_email'],
			'nickname'		=> self::$user->data['username'],
			'jabber'		=> self::$user->data['user_jabber'],
			'aim'			=> self::$user->data['user_aim'],
			'yim'			=> self::$user->data['user_yim'],
		);

		self::$user->data['wp_user'] = array_merge($default_userdata, (array) $wp_user_data);
	//	print_r(self::$user->data);
	}

	/**
	 * Get the WP user ID, based off the phpBB user ID
	 *
	 * @param (integer)	 	$wp_user_id	a default value, in WP the anonymous user is ID 0
	 * @return (integer)	$wp_user_id
	 */
	public static function wp_phpbb_get_userid($wp_user_id = 0)
	{
		global $wpdb;

		if (self::$user->data['user_type'] == USER_FOUNDER && self::$user->data['user_id'] == self::$config['wp_phpbb_bridge_forum_founder_user_id'])
		{
			return (int) self::$config['wp_phpbb_bridge_blog_founder_user_id'];
		}

		if (self::$user->data['user_type'] == USER_NORMAL || self::$user->data['user_type'] == USER_FOUNDER)
		{
		//	$id_list = $wpdb->get_col($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'phpbb_userid' AND meta_value = %d", self::$user->data['user_id']));
			$usermeta_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'phpbb_userid' AND meta_value = %s", self::$user->data['user_id']));

		//	if (empty($id_list))
			if (!$usermeta_id)
			{
		//		return (int) $wp_user_id;
				$users_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_login = %s", self::$user->data['username']));

				if ($users_id)
				{
					return (int) $users_id;
				}
			}
			else
			{
		//		return (int) $id_list[0];
				return (int) $usermeta_id;
			}
		}

		return $wp_user_id;
	}

	/**
	* Set and Force some variables
	* We do this instead made an ACP module for phpBB to manage this bridge configurations
	*/
	public static function wp_get_config()
	{
		$wp_phpbb_bridge_forum_founder_user_id	= phpbb::$config['wp_phpbb_bridge_blog_founder_user_id'];
		$wp_phpbb_bridge_blog_founder_user_id	= phpbb::$config['wp_phpbb_bridge_forum_founder_user_id'];
		$wp_phpbb_bridge_post_forum_id			= phpbb::$config['wp_phpbb_bridge_post_forum_id'];
		$wp_phpbb_bridge_widgets_column_width	= phpbb::$config['wp_phpbb_bridge_widgets_column_width'];
		$wp_phpbb_bridge_comments_avatar_width	= phpbb::$config['wp_phpbb_bridge_comments_avatar_width'];

		self::$config = array_merge(self::$config, array(
			// Disable to call the function leave_newly_registered()
			'new_member_post_limit'					=> null,
			// The ID of user forum founder
			'wp_phpbb_bridge_forum_founder_user_id'	=> (int) $wp_phpbb_bridge_forum_founder_user_id,
			// The ID of user blog founder
			'wp_phpbb_bridge_blog_founder_user_id'	=> (int) $wp_phpbb_bridge_blog_founder_user_id,
			// For the moment the ID of you forum where to post a new entry whenever is published in the Wordpress
			'wp_phpbb_bridge_post_forum_id'			=> (int) $wp_phpbb_bridge_post_forum_id,
			// The left column width, in pixels
			'wp_phpbb_bridge_widgets_column_width'	=> (int)  $wp_phpbb_bridge_widgets_column_width,
			// The width size of avatars in comments, in pixels
			'wp_phpbb_bridge_comments_avatar_width'	=> (int) $wp_phpbb_bridge_comments_avatar_width,
		));
	}

	/**
	* Include a phpBB includes file
	*
	* @param string $file The name of the file
	* @param string|bool $function_check Bool false to ignore; string function name to check if the function exists (and not load the file if it does)
	* @param string|bool $class_check Bool false to ignore; string class name to check if the class exists (and not load the file if it does)
	* 
	* Based off : Titania 0.3.11
	* File : titania/includes/core/phpbb.php
	*/
	public static function _include($file, $function_check = false, $class_check = false)
	{
		if ($function_check !== false)
		{
			if (function_exists($function_check))
			{
				return;
			}
		}

		if ($class_check !== false)
		{
			if (class_exists($class_check))
			{
				return;
			}
		}

		// Make that phpBB itself understands out paths
		global $phpbb_root_path, $phpEx;
	//	$phpbb_root_path = PHPBB_ROOT_PATH;
	//	$phpEx = PHP_EXT;
		$propress_options = get_option( 'theme_propress_options' );
		include($propress_options['phpbb_script_path']. "includes/" . $file . "." . PHP_EXT);
	}

	/**
	* Shortcut for phpbb's append_sid function (do not send the root path/phpext in the url part)
	*
	* @param mixed $url
	* @param mixed $params
	* @param mixed $is_amp
	* @param mixed $session_id
	* @return string
	* 
	* Based off : Titania 0.3.11
	* File : titania/includes/core/phpbb.php
	*/
	public static function append_sid($script, $params = false, $is_amp = true, $session_id = false)
	{
		return append_sid(self::$absolute_phpbb_url_path . $script . '.' . PHP_EXT, $params, $is_amp, $session_id);
	}

	
	public static function wp_phpbb_user_logged()
	{
		$is_wp_user_logged_in = $is_phpbb_user_logged_in = (self::$user->data['user_id'] != ANONYMOUS) ? true : false;

		if (function_exists('is_user_logged_in'))
		{
			$is_wp_user_logged_in = (is_user_logged_in()) ? true : false;
		}		
		return ($is_phpbb_user_logged_in || $is_wp_user_logged_in) ? true : false;
	}
}

?>

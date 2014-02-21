<?php
/**
 * @package Wordparess
 * @version 1.6
 */
/*
Plugin Name: Wordpress to PHPBB
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: WordPress - phpBB Integration Mod This Birdge makes possible to integrate your phpBB into your Wordpress Blog, sharing users. If the phpBB users do not exist in WP it will be automatically created as a "Subscriber" Want to have wordpress match your forum style? Get the theme instead.
Author: Matt Mullenweg
Version: 1.6
Author URI: http://ma.tt/
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

include_once dirname( __FILE__ ) . '/functions/wp-crosspost.php';
include_once dirname( __FILE__ ) . '/functions/options.php';
include_once dirname( __FILE__ ) . '/functions/custom.php';
require( ABSPATH . WPINC . '/pluggable.php' );

	if (!defined('IN_WP_PHPBB_BRIDGE'))
	{
		global $wp_phpbb_bridge_config, $phpbb_root_path, $phpEx;
		global $auth, $config, $db, $template, $user, $cache;
		include_once dirname( __FILE__ ) . '/functions/wp_phpbb_bridge.php';
	}
add_action('publish_post', 'wp_phpbb_posting', 10, 2);


add_filter( 'logout_url', 'wp_phpbb_logout' );
function wp_phpbb_logout()
{
	$temp =  phpbb::$config['wp_phpbb_bridge_board_path'];
	 return $temp.'ucp.php?mode=logout&amp;sid='.phpbb::$user->session_id;
}
?>

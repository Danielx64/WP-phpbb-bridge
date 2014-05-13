<?php
/**
* @package Wordparess
* @version 1.6
*/
/*
Plugin Name: phpBB to WP connector (plugin version)
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: WordPress - phpBB Integration Mod This Birdge makes possible to integrate your phpBB into your Wordpress Blog, sharing users. If the phpBB users do not exist in WP it will be automatically created as a "Subscriber" Want to have wordpress match your forum style? Get the theme instead.
Author: Matt Mullenweg
Version: 1.6
Author URI: http://ma.tt/
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

include_once dirname( __FILE__ ) . '/functions/wp-crosspost.php';
add_action( 'publish_post', 'wp_phpbb_posting', 10, 2);
include_once dirname( __FILE__ ) . '/functions/options.php';
include_once dirname( __FILE__ ) . '/functions/wp-profile.php';
if( !class_exists( 'WP_United_Plugin' ) ) {
	require_once(dirname( __FILE__ ) . '/functions/base-classes.php');
	require_once(dirname( __FILE__ ) . '/functions/plugin-main.php');
	global $wpUnited;
	$wpUnited = new WP_United_Plugin();
}
$wpUnited->wp_init();
require( ABSPATH . WPINC . '/pluggable.php' );
global $pagenow;
if (!defined('WP_DONTLOAD')) {
	if (!preg_match('/\/wp-admin\/(themes.php|plugins.php)/', $_SERVER['REQUEST_URI'])) {
			include_once dirname(__FILE__) . '/functions/wp_phpbb_bridge.php';
			add_filter('login_url', 'wp_phpbb_login');
			add_filter('logout_url', 'wp_phpbb_logout');
			add_filter('register_url', 'wp_phpbb_register');
	}
}




function wp_phpbb_logout()
{
	$temp = generate_board_url() . '/';
	return $temp . 'ucp.php?mode=logout&amp;sid=' . phpbb::$user->session_id;
}

function wp_phpbb_register()
{
	$temp = generate_board_url() . '/';
	return $temp . 'ucp.php?mode=register';
}

function wp_phpbb_login()
{
	$redirect = request_var('redirect', home_url(add_query_arg(array())));
	$temp = generate_board_url() . '/';
	return $temp . 'ucp.php?mode=login&amp;redirect=' . $redirect;
}

// Thank-you Dion Designs :)
function show_phpbb_link($content)
{
	if (!defined('IN_WP_PHPBB_BRIDGE')) {
		global $wp_phpbb_bridge_config, $phpbb_root_path, $phpEx;
		global $auth, $config, $db, $template, $user, $cache;
		include( dirname( __FILE__ ) . '/functions/wp_phpbb_bridge.php');
	}
	$postID = get_the_ID();
	if (empty($postID)) {
		return $content;
	}
	$sql = 'SELECT topic_id, forum_id, topic_replies FROM ' . TOPICS_TABLE . ' WHERE topic_wp_xpost = ' . $postID;
	$result = phpbb::$db->sql_query($sql);
	$post_data = phpbb::$db->sql_fetchrow($result);
	$board_url = generate_board_url(false) . '/';
	$web_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : PHPBB_ROOT_PATH;
	$replies = $post_data['topic_replies'];

	if ($post_data) {
		$rsuffix = $rtext = $rbutton = '';
		if ($replies) {
			if ($replies != 1) {
				$rsuffix = 's';
			}
			$rbutton = '&nbsp;&nbsp;&nbsp;&nbsp;<a class="button1" href="' . $web_path . 'viewtopic.php?f=' . $post_data['forum_id'] . '&amp;t=' . $post_data['topic_id'] . '">View the Discussion</a>';
			$rtext = '' . $replies . ' Comment' . $rsuffix;
		}
		$content .= '<div class="xpost-link">' . '<a class="button1" href="' . $web_path . 'posting.php?mode=reply&amp;f=' . $post_data['forum_id'] . '&amp;t=' . $post_data['topic_id'] . '">Comment On this Article</a>' . $rbutton . '</div>' . $rtext;
	}
	return $content;
}
add_filter('the_content', 'show_phpbb_link');


// Don't nag users who can't switch themes.
if ( ! is_admin() || ! current_user_can( 'switch_themes' ) )
return;

function wphpbb_admin_notice()
{
	if (isset($_GET['wphpbb-dismiss']))
		set_theme_mod('wphpbb', true);

	$dismiss = get_theme_mod('wphpbb', false);
	if ($dismiss)
		return;
	?>
	<div class="updated wphpbb-notice">
		<p><?php printf(__('In order for this bridge to work correctly, you will <a target="_blank" href="%s">need to configure it</a>. <a href="%s">I have already configured it.</a>', 'wp_phpbb3_bridge'), admin_url('themes.php?page=propress-settings'), add_query_arg('wphpbb-dismiss', 1)); ?></p>
	</div>
<?php
}
add_action( 'admin_notices', 'wphpbb_admin_notice' );

?>

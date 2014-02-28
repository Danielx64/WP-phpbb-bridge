<?php
/**
*
* @author Danielx64 (Daniel)
* @package umil
* @copyright (c) 2013 Danielx64
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('UMIL_AUTO', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

define('IN_PHPBB', true);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/wp_phpbb_bridge');

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

$options = array(
	'phpbb2wp_wppath'	=> array('lang' => 'WPHPBB_INFO', 'validate' => 'string', 'type' => 'text:40:255', 'explain' => true),
);

$mod_name = 'WP_MOD_TITLE';
$version_config_name = 'wpphpbbver';

$versions = array(
	'0.8.0' => array(

		'table_column_add' => array(
			array(TOPICS_TABLE, 'topic_wp_xpost', array('MTEXT_UNI', '0')),
		),

		'config_add' => array(
			array('phpbb2wp_wppath', request_var('phpbb2wp_wppath', ''), false),
		),

		'cache_purge' => array(
			array('template'),
			array('theme'),
		),
	),
	'0.8.1' => array(
		'custom' => 'ubm_custom_install',

		'cache_purge' => array(
			array('template'),
			array('theme'),
		),
	),
	'0.8.3' => array(

		'config_add' => array(
			array('show_homepage', '0'),
			array('homepage_url', 'http://your_own_site.com'),
			array('homepage_title', 'Your homepage title'),
		),

		'cache_purge' => array(
			array('template'),
			array('theme'),
		),
	),
	'0.8.5' => array(

		'module_add' => array(
			array('acp', 'ACP_BOARD_CONFIGURATION', array(
				'module_basename' => 'wp_phpbb_bridge',
				'module_enabled' => 1,
				'module_display' => 1,
				'module_langname' => 'ACP_WP_PHPBB_BRIDGE',
				'module_mode' => 'manage',
				'module_auth' => '',
			),
			),
		),

		'config_add' => array(
			array('wp_phpbb_bridge_post_forum_id', '2'),
			array('wp_phpbb_bridge_board_path', 'http://your_own_site.com/forums/'),
			array('wp_phpbb_bridge_widgets_column_width', '300'),
			array('wp_phpbb_bridge_comments_avatar_width', '32'),
			array('wp_phpbb_bridge_forum_founder_user_id', '2'),
			array('wp_phpbb_bridge_blog_founder_user_id', '1'),
		),

		'cache_purge' => array(
			array('template'),
			array('theme'),
		),
	),
	'0.8.6' => array(
		'custom' => 'ubm_custom_install',
		'cache_purge' => array(
			array('template'),
			array('theme'),
		),
	),
	'0.8.7' => array(
		'custom' => 'ubm_custom_install',
		'cache_purge' => array(
			array('template'),
			array('theme'),
		),
	),
	'1.0.1' => array(
		'cache_purge' => array(
			array('template'),
			array('theme'),
		),
	),
	'1.0.2' => array(
		'config_add' => array(
			array('crosspostcontent', '0'),
		),
		'cache_purge' => array(
			array('template'),
			array('theme'),
		),
	),
);

function ubm_custom_install($action, $version)
{
	global $db, $user, $umil, $phpbb_root_path, $phpEx;
	if ($action == 'uninstall') {
		$umil->config_update(array(
			array('auth_method', 'db'),
		));
	}
	if ($action == 'install') {
		switch ($version) {
			case '0.8.1' :
				$db->sql_query('ALTER TABLE ' . TOPICS_TABLE . 'CHANGE `topic_wp_xpost` `topic_wp_xpost` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL');
				$umil->umil_end();
				break;
			case '0.8.6' :
				$db->sql_query('ALTER TABLE ' . TOPICS_TABLE . ' ADD INDEX xpost (topic_wp_xpost)');
				$umil->umil_end();
				break;
			case '0.8.7' :
				$umil->config_update(array(
					array('auth_method', 'phpbb2wp'),
				));
				$umil->umil_end();
				break;
		}
	}
}

include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

?>
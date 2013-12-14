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

$mod_name = 'WP_MOD_TITLE';
$version_config_name = 'wpphpbbver';

$versions = array(
	'0.8.0'		=> array(

		'table_column_add' => array(
			array(TOPICS_TABLE, 'topic_wp_xpost', array('MTEXT_UNI', '0')),
		),
		
		'config_add'	=> array(
						array('phpbb2wp_wppath', '/../../../'),

		),

		'cache_purge'	=> array(
			array('template'),
			array('theme'),
		),
	),
	'0.8.1'		=> array(
		'custom'	=> 'ubm_custom_install',

		'cache_purge'	=> array(
			array('template'),
			array('theme'),
		),
	),
);

function ubm_custom_install($action, $version)
{
	global $db, $user, $umil, $phpbb_root_path, $phpEx;

	switch ($version)
	{
		case '0.8.1' :
				$db->sql_query('ALTER TABLE ' . TOPICS_TABLE . '  CHANGE `topic_wp_xpost` `topic_wp_xpost` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL');
				$umil->umil_end();
		break;
	}
}

include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

?>
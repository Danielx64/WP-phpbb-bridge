<?php
/**
*
* @package phpBB3 phpBB to WordPress
* @version $Id$
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
			array(TOPICS_TABLE, 'topic_wp_xpost', array('MTEXT_UNI', '')),
		),
		
		'config_add'	=> array(
						array('phpbb2wp_wppath', '/../../../'),

		),

		'cache_purge'	=> array(
			array('template'),
			array('theme'),
		),
	),
);

include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

?>
<?php
/**
 * 
 * @package: phpBB 3.0.9 :: BRIDGE phpBB & WordPress -> root/includes/acp/
 * @version: $Id: acp_wp_phpbb_bridge.php, v0.0.9 2011/08/25 11:08:25 leviatan21 Exp $
 * @copyright: leviatan21 < info@mssti.com > (Gabriel) http://www.mssti.com/phpbb3/
 * @license: http://opensource.org/licenses/gpl-license.php GNU Public License 
 * @author: leviatan21 - http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
 * 
 */
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_wp_phpbb_bridge
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;

		$user->add_lang('mods/wp_phpbb_bridge');

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'acp_board';
		add_form_key($form_key);

		/**
		*	Validation types are:
		*		string, int, bool,
		*		script_path (absolute path in url - beginning with / and no trailing slash),
		*		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		*/
		switch ($mode)
		{
			case 'manage':

				$display_vars = array(
					'title'   => 'WP_PHPBB_BRIDGE_MANAGE',
					'vars'   => array(
						'legend1'	=> 'WP_PHPBB_BRIDGE_BASIC',
						'show_homepage'			=> array('lang' => 'SHOW_HOMEPAGE',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'homepage_url'			=> array('lang' => 'HOMEPAGE_URL',			'validate' => 'string',	'type' => 'text:40:255', 'explain' => true),
						'homepage_title'		=> array('lang' => 'HOMEPAGE_TITLE',		'validate' => 'string',	'type' => 'text:40:255', 'explain' => true),
						'wp_phpbb_bridge_post_forum_id'			=> array('lang' => 'WP_PHPBB_BRIDGE_XPOST',			'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => true),

						'legend2'	=> 'WP_PHPBB_BRIDGE_PATHS',
						'wp_phpbb_bridge_board_path'			=> array('lang' => 'WP_PHPBB_BRIDGE_FORUM_URL',			'validate' => 'string',	'type' => 'text:40:255', 'explain' => true),
						'phpbb2wp_wppath'						=> array('lang' => 'WPHPBB_INFO',			'validate' => 'string',	'type' => 'text:40:255', 'explain' => true),
						'legend3'	=> 'WP_PHPBB_BRIDGE_STYLE',
						'wp_phpbb_bridge_widgets_column_width'			=> array('lang' => 'WP_PHPBB_BRIDGE_SIDEBAR',		'validate' => 'int:0','type' => 'text:40:255', 'method' => false, 'explain' => true),
						'wp_phpbb_bridge_comments_avatar_width'			=> array('lang' => 'WP_PHPBB_BRIDGE_AV',			'validate' => 'int:0', 'type' =>'text:40:255', 'method' => false, 'explain' => true),

						'legend4'	=> 'Founders IDs',
						'wp_phpbb_bridge_forum_founder_user_id'			=> array('lang' => 'PHPBBFOUNDER',		'validate' => 'int:0','type' => 'text:40:255', 'method' => false, 'explain' => false),
						'wp_phpbb_bridge_blog_founder_user_id'			=> array('lang' => 'WPFOUNDER',			'validate' => 'int:0', 'type' =>'text:40:255', 'method' => false, 'explain' => false),

						'legend5'	=> 'ACP_SUBMIT_CHANGES',					)
				);

				

			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}

		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				set_config($config_name, $config_value);
			}
		}

		if ($submit)
		{
			add_log('admin', 'LOG_WP_PHPBB_BRIDGE_SETTINGS');

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->tpl_name = 'acp_board';
		$this->page_title = $display_vars['title'];

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars),
				)
			);

			unset($display_vars['vars'][$config_key]);
		}
	}
}

?>
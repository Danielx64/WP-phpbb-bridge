<?php
/** 
* @package phpBB to WP connector
* @version $Id: 1.5.0
* @copyright (c) 2013-2014 danielx64.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author Danielx64
*
*/

// Thank-you Dion Designs :)
function show_phpbb_link($content)
{
	if (!defined('IN_WP_PHPBB_BRIDGE')) {
		global  $phpbb_root_path;
		include(TEMPLATEPATH . '/includes/wp_phpbb_bridge.php');
	}
	$postID = get_the_ID();
	if (empty($postID)) {
		return $content;
	}
	
	$sql = 'SELECT topic_id, forum_id, topic_replies FROM ' . TOPICS_TABLE . ' WHERE topic_wp_xpost = ' . $postID;
	$result = phpbb::$db->sql_query($sql);
	$post_data = phpbb::$db->sql_fetchrow($result);
	$phpbb_root_path =  generate_board_url() . '/';
	$replies = $post_data['topic_replies'];

	if ($post_data) {
		$rsuffix = $rtext = $rbutton = '';
		if ($replies) {
			if ($replies != 1) {
				$rsuffix = 's';
			}
			$rbutton = '&nbsp;&nbsp;&nbsp;&nbsp;<a class="button1" href="' . $phpbb_root_path . 'viewtopic.php?f=' . $post_data['forum_id'] . '&amp;t=' . $post_data['topic_id'] . '">View the Discussion</a>';
			$rtext = '<|DD|>' . $replies . ' Comment' . $rsuffix;
		}
		$content .= '<div class="xpost-link">' . '<a class="button1" href="' . $phpbb_root_path . 'posting.php?mode=reply&amp;f=' . $post_data['forum_id'] . '&amp;t=' . $post_data['topic_id'] . '">Comment On this Article</a>' . $rbutton . '</div>' . $rtext;
	}
	return $content;
}
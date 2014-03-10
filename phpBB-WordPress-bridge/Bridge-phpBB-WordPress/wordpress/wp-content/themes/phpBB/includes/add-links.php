<?php

// Thank-you Dion Designs :)
function show_phpbb_link($content)
{
	if (!defined('IN_WP_PHPBB_BRIDGE')) {
		global $wp_phpbb_bridge_config, $phpbb_root_path, $phpEx;
		global $auth, $config, $db, $template, $user, $cache;
		include(TEMPLATEPATH . '/includes/wp_phpbb_bridge.php');
	}
	$postID = get_the_ID();
	if (empty($postID)) {
		return $content;
	}
	$sql = 'SELECT topic_id, forum_id, topic_replies FROM ' . TOPICS_TABLE . ' WHERE topic_wp_xpost = ' . $postID;
	$result = phpbb::$db->sql_query($sql);
	$post_data = phpbb::$db->sql_fetchrow($result);
	$board_url = generate_board_url(false) . '/';
	$web_path = phpbb::$config['wp_phpbb_bridge_board_path'];
	$replies = $post_data['topic_replies'];

	if ($post_data) {
		$rsuffix = $rtext = $rbutton = '';
		if ($replies) {
			if ($replies != 1) {
				$rsuffix = 's';
			}
			$rbutton = '&nbsp;&nbsp;&nbsp;&nbsp;<a class="button1" href="' . $web_path . 'viewtopic.php?f=' . $post_data['forum_id'] . '&amp;t=' . $post_data['topic_id'] . '">View the Discussion</a>';
			$rtext = '<|DD|>' . $replies . ' Comment' . $rsuffix;
		}
		$content .= '<div class="xpost-link">' . '<a class="button1" href="' . $web_path . 'posting.php?mode=reply&amp;f=' . $post_data['forum_id'] . '&amp;t=' . $post_data['topic_id'] . '">Comment On this Article</a>' . $rbutton . '</div>' . $rtext;
	}
	return $content;
}
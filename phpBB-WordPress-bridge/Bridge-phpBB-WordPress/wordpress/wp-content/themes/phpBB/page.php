<?php
/**
 * 
 * @package: phpBB 3.0.9 :: BRIDGE phpBB & WordPress -> WordPress root/wp-content/themes/phpBB
 * @version: $Id: page.php, v0.0.9 2011/10/25 11:10:25 leviatan21 Exp $
 * @copyright: leviatan21 < info@mssti.com > (Gabriel) http://www.mssti.com/phpbb3/
 * @license: http://opensource.org/licenses/gpl-license.php GNU Public License 
 * @author: leviatan21 - http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
 * 
 */

/**
* @ignore
**/

require_once('includes/wp_phpbb_bridge.php'); 

$postrow = '';//$commentrow = $autor = array();

$topic_title = $topic_link = '';

$post_id = 0;
if (have_posts()) {
	while (have_posts()) {
		the_post();

		// Retrieve the ID of the current item in the WordPress Loop
		$post_id = get_the_ID();

		//
		$postrow = array(
			'POST_ID' => $post_id,
			// Generate urls for letting the moderation control panel being accessed in different modes
			'S_POST_ACTIONS' => (current_user_can('delete_post', $post_id) || current_user_can('edit_post', $post_id)) ? true : false, //	'publish_posts' or 'edit_posts' is for create
			'U_POST_EDIT' => get_edit_post_link($post_id),

			// This both links looks similar, but the return is quite differente according the EMPTY_TRASH_DAYS 
			'U_POST_DELETE' => (!EMPTY_TRASH_DAYS) ? get_delete_post_link($post_id) : '',
			'U_POST_TRASH' => (EMPTY_TRASH_DAYS) ? get_delete_post_link($post_id) : '',

			'MINI_POST_IMG' => phpbb::$user->img('icon_post_target', 'POST'),
			'U_MINI_POST' => apply_filters('the_permalink', get_permalink()) . "#post-$post_id",
			'POST_SUBJECT' => censor_text(get_the_title()),
			'MESSAGE' => censor_text(wp_do_action('the_content')),
		);

		$topic_title = $postrow['POST_SUBJECT'];
		$topic_link = $postrow['U_MINI_POST'];
		phpbb::$template->assign_block_vars('navlinks', array(
				'FORUM_NAME' => $topic_title,
				'U_VIEW_FORUM' => $topic_link,
			)
		);

		// Dump vars into template
		phpbb::$template->assign_block_vars('postrow', $postrow);
	}

	$board_url = generate_board_url(false) . '/';
	$redirect = request_var('redirect', home_url());
	$web_path = phpbb::$config['wp_phpbb_bridge_board_path'];

	// Assign post specific vars
	phpbb::$template->assign_vars(array(
		'IN_SINGLE' => true,
		'U_TOPIC' => $topic_link,
		'TOPIC_SUBJECT' => $topic_title,

		'EDIT_IMG' => phpbb::$user->img('icon_post_edit', 'EDIT_POST'),
		'DELETE_IMG' => phpbb::$user->img('icon_post_delete', 'DELETE_POST'),
		'TRASH_IMG' => phpbb::wp_imageset('icon_wp_trash', 'WP_COMMENT_TRASH_EXPLAIN', 'TRASH_IMG_CLASS'),
		'UNTRASH_IMG' => phpbb::wp_imageset('icon_wp_untrash', 'WP_COMMENT_UNTRASH_EXPLAIN', 'UNTRASH_IMG_CLASS'),
	));
}

phpbb::page_sidebar();

phpbb::page_header();

phpbb::page_footer();

?>
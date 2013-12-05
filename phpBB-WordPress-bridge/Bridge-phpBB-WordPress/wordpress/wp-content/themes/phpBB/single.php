<?php
/**
 * 
 * @package: phpBB 3.0.9 :: BRIDGE phpBB & WordPress -> WordPress root/wp-content/themes/phpBB
 * @version: $Id: single.php, v0.0.9 2011/10/25 11:10:25 leviatan21 Exp $
 * @copyright: leviatan21 < info@mssti.com > (Gabriel) http://www.mssti.com/phpbb3/
 * @license: http://opensource.org/licenses/gpl-license.php GNU Public License 
 * @author: leviatan21 - http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
 * 
 */

/**
* @ignore
**/

require_once('includes/wp_phpbb_bridge.php'); 

$postrow = $commentrow = $autor = array();

$topic_title = $topic_link = '';

$post_id = 0;
if (have_posts())
{
	while (have_posts())
	{
		the_post();

		// Retrieve the ID of the current item in the WordPress Loop
		$post_id = get_the_ID();

		// Retrieve the time at which the post was written. returns timestamp
		$post_date_time = get_post_time('U', false, $post_id, false);

		//
		$postrow = array(
			'FEATURED_IMG'		=> '<div style="float:left;margin:0 15px 5px 0">' . get_the_post_thumbnail($post_id, 'dd-featured', array('class' => 'featured_image')) . '</div><br />',
			'POST_ID'			=> $post_id,
			'POST_DATE'			=> phpbb::$user->format_date($post_date_time, false, true),
			// Generate urls for letting the moderation control panel being accessed in different modes
			'S_POST_ACTIONS'	=> (current_user_can('delete_post', $post_id) || current_user_can('edit_post', $post_id)) ? true : false, //	'publish_posts' or 'edit_posts' is for create
			'U_POST_EDIT'		=> get_edit_post_link($post_id),

			// This both links looks similar, but the return is quite differente according the EMPTY_TRASH_DAYS 
			'U_POST_DELETE'		=> (!EMPTY_TRASH_DAYS) ? get_delete_post_link($post_id) : '',
			'U_POST_TRASH'		=> (EMPTY_TRASH_DAYS) ? get_delete_post_link($post_id) : '',

			'MINI_POST_IMG'		=> phpbb::$user->img('icon_post_target', 'POST'),
			'U_MINI_POST'		=> apply_filters('the_permalink', get_permalink()) . "#post-$post_id",
			'POST_SUBJECT'		=> censor_text(get_the_title()),
			'MESSAGE'			=> censor_text(wp_do_action('the_content')),

			'POST_TAGS'			=> get_the_tag_list(phpbb::$user->lang['WP_TITLE_TAGS'] . ': ', ', ', ''),
			'POST_CATS'			=> sprintf(phpbb::$user->lang['WP_POSTED_IN'], get_the_category_list(', ')),
		);

		$topic_title = $postrow['POST_SUBJECT'];
		$topic_link = $postrow['U_MINI_POST'];

		$autor = phpbb::phpbb_the_autor_full($post->post_author, false, true);
		$postrow = array_merge($postrow, $autor);

		// Dump vars into template
		phpbb::$template->assign_block_vars('postrow', $postrow);
	}

	$board_url = generate_board_url(false) . '/';		
	$redirect = request_var('redirect', home_url(add_query_arg(array())));
	$web_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : PHPBB_ROOT_PATH;

	// Assign post specific vars
	phpbb::$template->assign_vars(array(
		'IN_SINGLE'				=> true,
		'U_TOPIC'				=> $topic_link,
		'TOPIC_SUBJECT'			=> $topic_title,

		'EDIT_IMG' 				=> phpbb::$user->img('icon_post_edit', 'EDIT_POST'),
		'DELETE_IMG' 			=> phpbb::$user->img('icon_post_delete', 'DELETE_POST'),
		'TRASH_IMG' 			=> phpbb::wp_imageset('icon_wp_trash', 'WP_COMMENT_TRASH_EXPLAIN', 'TRASH_IMG_CLASS'),
		'UNTRASH_IMG' 			=> phpbb::wp_imageset('icon_wp_untrash', 'WP_COMMENT_UNTRASH_EXPLAIN', 'UNTRASH_IMG_CLASS'),
		'SPAM_IMG' 				=> phpbb::wp_imageset('icon_wp_spam', 'WP_COMMENT_SPAM_EXPLAIN', 'SPAM_IMG_CLASS'),
		'UNSPAM_IMG'			=> phpbb::wp_imageset('icon_wp_nospam', 'WP_COMMENT_UNSPAM_EXPLAIN', 'UNSPAM_IMG_CLASS'),
		'APPROVE_IMG'			=> phpbb::wp_imageset('icon_wp_approve', 'WP_COMMENT_APPROVE_EXPLAIN', 'APPROVE_IMG_CLASS'),
		'UNAPPROVE_IMG'			=> phpbb::wp_imageset('icon_wp_unapprove', 'POST_UNAPPROVED', 'UNAPPROVE_IMG_CLASS'),

		'REPORTED_IMG'			=> phpbb::$user->img('icon_topic_reported', 'POST_REPORTED'),
		'UNAPPROVED_IMG'		=> phpbb::$user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),

		// Pagination
		'PREVIOUS_ENTRIE'		=> wp_do_action('adjacent_post_link', phpbb::$user->lang['PREVIOUS_ENTRIE'] . ' %link', '%title', false, '', true),
		'NEXT_ENTRIE'			=> wp_do_action('adjacent_post_link', '%link ' . phpbb::$user->lang['NEXT_ENTRIE'], '%title', false, '', false),
	));
}

phpbb::page_sidebar($post_id);

phpbb::page_header();

phpbb::page_footer();

?>
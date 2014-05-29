<?php
/** 
* @package phpBB to WP connector
* @version $Id: 1.5.0
* @copyright (c) 2013-2014 danielx64.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author Danielx64
 * @based off: phpBB 3.0.9 :: BRIDGE phpBB & WordPress -> WordPress root/wp-content/themes/phpBB/includes
 * @orginal author: leviatan21 - http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
 * 
 */

/**
* @ignore
**/

require_once('includes/wp_phpbb_bridge.php');

$topicrow = $autor = array();

$have_posts = false;

if (have_posts()) {
	$have_posts = true;

	while (have_posts()) {
		the_post();
		// Retrieve the ID of the current item in the WordPress Loop
		$post_id = get_the_ID();
		// Retrieve the time at which the post was written. returns timestamp
		$post_date_time = get_post_time('U', false, $post_id, false);

		//
		list($content) = explode('<|DD|>', wp_do_action('the_content', phpbb::$user->lang['WP_READ_MORE']));
		$topicrow = array(
			'FEATURED_IMG' => '<div style="float:left;margin:0 15px 5px 0">' . get_the_post_thumbnail($post_id, 'dd-featured-mini', array('class' => 'featured_image')) . '</div>',
			'POST_ID' => $post_id,
			'POST_DATE' => phpbb::$user->format_date($post_date_time, false, true),
			'U_POST_EDIT' => get_edit_post_link($post_id),
			// This both links looks similar, but the return is quite differente according the EMPTY_TRASH_DAYS 
			'U_POST_DELETE' => (!EMPTY_TRASH_DAYS) ? get_delete_post_link($post_id) : '',
			'U_POST_TRASH' => (EMPTY_TRASH_DAYS) ? get_delete_post_link($post_id) : '',

			'MINI_POST_IMG' => $user->img('icon_post_target', 'POST'),
			'U_MINI_POST' => apply_filters('the_permalink', get_permalink()),
			'POST_SUBJECT' => censor_text(get_the_title()),
			'MESSAGE' => censor_text($content),

			'POST_TAGS' => get_the_tag_list(phpbb::$user->lang['WP_TITLE_TAGS'] . ': ', ', ', '<br />'),
			'POST_CATS' => sprintf(phpbb::$user->lang['WP_POSTED_IN'], get_the_category_list(', ')),
		);

		$autor = phpbb::phpbb_the_autor_full($post->post_author, false);
		$topicrow = array_merge($topicrow, $autor);

		// Dump vars into template
		phpbb::$template->assign_block_vars('topicrow', $topicrow);
	}
}

// Assign index specific vars
phpbb::$template->assign_vars(array(
	'IN_SINGLE' => false,
	'IN_ERROR' => !$have_posts,

	'EDIT_IMG' => phpbb::$user->img('icon_post_edit', 'EDIT_POST'),
	'DELETE_IMG' => phpbb::$user->img('icon_post_delete', 'DELETE_POST'),
	'GOTO_PAGE_IMG' => phpbb::$user->img('icon_post_target', 'GOTO_PAGE'),

	// Display navigation to next/previous pages when applicable 
	'NEXT_ENTRIE' => ($wp_query->max_num_pages > 1) ? get_next_posts_link(phpbb::$user->lang['NEXT_ENTRIE']) : '',
	'PREVIOUS_ENTRIE' => ($wp_query->max_num_pages > 1) ? get_previous_posts_link(phpbb::$user->lang['PREVIOUS_ENTRIE']) : '',
));


/** Recent Topics is managed within WP widgets : WP_Widget_phpbb_recet_topics **/
phpbb::page_sidebar();

phpbb::page_header();

phpbb::page_footer();

?>
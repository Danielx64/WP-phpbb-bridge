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
		$topicrow = array(
			'POST_ID' => $post_id,
			'POST_DATE' => phpbb::$user->format_date($post_date_time, false, true),
			'MINI_POST_IMG' => $user->img('icon_post_target', 'POST'),
			'U_MINI_POST' => apply_filters('the_permalink', get_permalink()),
			'POST_SUBJECT' => get_the_title(),
			'MESSAGE' => get_the_excerpt(),

			'POST_TAGS' => get_the_tag_list(phpbb::$user->lang['WP_TITLE_TAGS'] . ': ', ', ', '<br />'),
			'POST_CATS' => sprintf(phpbb::$user->lang['WP_POSTED_IN'], get_the_category_list(', ')),
		);

		// Dump vars into template
		phpbb::$template->assign_block_vars('topicrow', $topicrow);
	}
}

// Assign index specific vars
phpbb::$template->assign_vars(array(
	// We use the same template as index
	'IN_SINGLE' => false,
	'IN_SEARCH' => true,
	'IN_ERROR' => !$have_posts,
));

phpbb::page_sidebar();

phpbb::page_header();

phpbb::page_footer();

/**
 * Adds a pretty "Jump to entry" link to custom post excerpts.
 * 
 * @return string Excerpt with a pretty "Continue Reading" link
 */
add_filter('get_the_excerpt', 'wp_phpbb_the_excerpt');
function wp_phpbb_the_excerpt($output)
{
	$output .= '<ul class="searchresults">
			<li><a href="' . get_permalink() . '" class="' . ((phpbb::$user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left') . '">' . phpbb::$user->lang['WP_JUMP_TO_POST'] . '</a></li>
		</ul>';
	return $output;
}

?>
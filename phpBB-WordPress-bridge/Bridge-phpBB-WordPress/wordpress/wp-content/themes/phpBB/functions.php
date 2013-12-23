<?php
/**
 * 
 * @package: phpBB 3.0.9 :: BRIDGE phpBB & WordPress -> WordPress root/wp-content/themes/phpBB
 * @version: $Id: functions.php, v0.0.9 2011/12/10 11:12:10 leviatan21 Exp $
 * @copyright: leviatan21 < info@mssti.com > (Gabriel) http://www.mssti.com/phpbb3/
 * @license: http://opensource.org/licenses/gpl-license.php GNU Public License 
 * @author: leviatan21 - http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
 * 
 */

/**
* @ignore
**/

require( get_template_directory() . '/includes/options.php' );
require( get_template_directory() . '/includes/custom.php' );

// Hide WordPress Admin Bar
add_filter('show_admin_bar', '__return_false');

// remove junk from head
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);

function phpbb_bridge_setup() {
	// This theme supports a custom header image
	add_theme_support( 'custom-header' );

	// This theme supports post thumbnails
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'dd-featured', 384, 256 );
	add_image_size( 'dd-featured-mini', 96, 64 );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary'   => __( 'Header Menu', 'wp_phpbb3_bridge' ),
		'secondary' => __( 'Footer Menu', 'wp_phpbb3_bridge' ),
	) );
	
	load_theme_textdomain( 'wp_phpbb3_bridge', get_template_directory() . '/langs' );

}
add_action( 'after_setup_theme', 'phpbb_bridge_setup' );

if (!defined('WP_ADMIN'))
{
	add_filter( 'logout_url', 'wp_phpbb_logout' );
}
function wp_phpbb_logout()
{
	$temp =  phpbb::$config['wp_phpbb_bridge_board_path'];
	 return !is_admin() ? $temp.'ucp.php?mode=logout&amp;sid='.phpbb::$user->session_id : '';
}

/**
 * Add a form field with the phpbb user session ID
 *
 */
function phpbb_login_form()
{
	global $phpbb_session_id;
	echo '<input type="hidden" name="sid" value="' . $phpbb_session_id . '" />';
}


/**
 * Insert some js files and or Extra layout 2 columns
 */
function wp_phpbb_stylesheet()
{
		$blog_stylesheet = '<style type="text/css">
/** Style on-the-fly **/
.section-blog #wp-phpbb-bridge-container {
	margin-right: -' . ((int) phpbb::$config['wp_phpbb_bridge_widgets_column_width'] + 10) . 'px;
}
.section-blog #content {
	margin-right: ' . ((int) phpbb::$config['wp_phpbb_bridge_widgets_column_width'] + 10) . 'px;
}
.section-blog #wp-phpbb-bridge-primary {
	width: ' . (int) phpbb::$config['wp_phpbb_bridge_widgets_column_width'] . 'px;
}
</style>' . "\n";

		echo $blog_stylesheet;
}

/**
 * Insert some js files
 */
function propress_enqueue_js_scripts() {
	wp_enqueue_style( 'phpbb-style', get_template_directory_uri() . '/style.css');

}
add_action( 'wp_enqueue_scripts', 'propress_enqueue_js_scripts' );

/**
 * Pagination routine, generates page number sequence
 * 
 * Based off : phpbb3.0.8
 * File : phpbb/includes/functions.php
 */
function wp_generate_pagination($base_url, $num_items, $per_page, $on_page)
{
	$seperator = '<span class="page-sep">' . phpbb::$user->lang['COMMA_SEPARATOR'] . '</span>';
	$total_pages = ceil($num_items / $per_page);

	if ($total_pages == 1 || !$num_items)
	{
		return false;
	}

	global $wp_rewrite;
	global $paged;

	$paged = $on_page;
	$page_delim = 'cpage=';
	$url_delim = (strpos($base_url, '?') === false) ? '?' : ((strpos($base_url, '?') === strlen($base_url) - 1) ? '' : '&amp;');
	$url_delim2 = '';

	if ($wp_rewrite->using_permalinks())
	{
		$page_delim = 'comment-page-';
		$url_delim = '';
		$url_delim2 = '/#comments';
	}

	$page_string = ($on_page == 1) ? '<strong>1</strong>' : '<a href="' . $base_url . '">1</a>';
	$max_pages = min(ceil($num_items / $total_pages), 4);

	if ($total_pages > 5)
	{
		$start_cnt = min(max(1, $on_page - $max_pages), $total_pages - 5);
		$end_cnt = max(min($total_pages, $on_page + $max_pages), 6);

		$page_string .= ($start_cnt > 1) ? ' ... ' : $seperator;

		for ($i = $start_cnt + 1; $i < $end_cnt; $i++)
		{
			$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "{$url_delim}{$page_delim}" . $i . $url_delim2 . '">' . $i . '</a>';
			if ($i < $end_cnt - 1)
			{
				$page_string .= $seperator;
			}
		}

		$page_string .= ($end_cnt < $total_pages) ? ' ... ' : $seperator;
	}
	else
	{
		$page_string .= $seperator;

		for ($i = 2; $i < $total_pages; $i++)
		{
			$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "{$url_delim}{$page_delim}" . $i . $url_delim2 . '">' . $i . '</a>';
			if ($i < $total_pages)
			{
				$page_string .= $seperator;
			}
		}
	}

	$page_string .= ($on_page == $total_pages) ? '<strong>' . $total_pages . '</strong>' : '<a href="' . $base_url . "{$url_delim}{$page_delim}" . $total_pages . '">' . $total_pages . '</a>';

	return $page_string;
}

/**
 * Generate topic pagination
 * 
 * Based off : phpbb3.0.8
 * File : phpbb/includes/functions_display.php
 */
function wp_topic_generate_pagination($url, $replies, $per_page)
{
	$url_delim = (strpos($url, '?') === false) ? '?' : ((strpos($url, '?') === strlen($url) - 1) ? '' : '&amp;');

	if (($replies + 1) > $per_page)
	{
		$total_pages = ceil(($replies + 1) / $per_page);
		$pagination = '';

		$times = 1;
		for ($j = 0; $j < $replies + 1; $j += $per_page)
		{
			$pagination .= '<a href="' . $url . ($j == 0 ? '' : "{$url_delim}cpage=" . $times) . '">' . $times . '</a>';
			if ($times == 1 && $total_pages > 5)
			{
				$pagination .= ' ... ';

				// Display the last three pages
				$times = $total_pages - 3;
				$j += ($total_pages - 4) * $per_page;
			}
			else if ($times < $total_pages)
			{
				$pagination .= '<span class="page-sep">' . phpbb::$user->lang['COMMA_SEPARATOR'] . '</span>';
			}
			$times++;
		}
	}
	else
	{
		$pagination = '';
	}

	return $pagination;
}

/**
 * Capture the output of a function, which simply echo's a string. 
 * 	Capture the echo into a variable without actually echo'ing the string. 
 * 	You can do so by leveraging PHP's output buffering functions. Here's how you do it:
 *
 * @param string $tag The name of the action to be executed.
 * @param mixed $arg,... Optional additional arguments which are passed on to the functions hooked to the action.
 * @return null Will return null if $tag does not exist in $wp_filter array
 */
function wp_do_action($tag)
{
	// Retrieve arguments list
    $_args = func_get_args();

    // Delete the first argument which is the class name
    $_className = array_shift($_args);

	ob_start();

	call_user_func_array($tag, $_args);

	$echo = ob_get_contents();

	ob_end_clean();

	return $echo;
}

// Register sidebars by running wp_phpbb_widgets_init() on the widgets_init hook.
add_action('widgets_init', 'wp_phpbb_widgets_init');
/**
 * Register widgetized area, and available widdgets for the bridge.
 */
function wp_phpbb_widgets_init()
{
	// Register Single Sidebar
	register_sidebar(
		array(
			'id'			=> 'wp_phpbb-widget-area',
			'name'			=> __('Primary Widget Area', 'wp_phpbb3_bridge'),
			'description'	=> __('The primary widget area.', 'wp_phpbb3_bridge'),
			'before_widget'	=> "\n" . '<div class="panel bg3">' . "\r\t" . '<div class="inner"><span class="corners-top"><span></span></span>' . "\n\t\t",
			'after_widget'	=> "\n\t" . '<span class="corners-bottom"><span></span></span></div>' . "\r" . '</div>' . "\n",
			'before_title'	=> '<h3>',
			'after_title'	=> '</h3>' . "\n",
		)
	);

	unregister_widget('WP_Nav_Menu_Widget');

	register_widget('WP_Widget_phpbb_recet_topics');
}

// Add a filter for the widget title by running wp_phpbb_widget_title() on the widget_title hook
add_filter('widget_title', 'wp_phpbb_widget_title');
/**
 * If the widget have no title, just add a "nonbreacking space" instead
 *
 * @param (string) $title
 * @return (string) $title or space
 */
function wp_phpbb_widget_title($title)
{
	$title = (!empty($title)) ? $title : '&nbsp;';
	return $title;
}

/**
 * Enter description here...
 *
 * based off the WP add-on by Jason Sanborn <jsanborn@simplicitypoint.com> http://www.e-xtnd.it/wp-phpbb-bridge/
 */
class WP_Widget_phpbb_recet_topics extends WP_Widget
{
	// Defaults Settings
	var $defaults = array(
		'title'				=> 'Recent topics',
		'forums'			=> '2',
		'total'				=> 5,
		'showForum'			=> 1,
		'showUsername'		=> 1,
	);

	function WP_Widget_phpbb_recet_topics()
	{
		// Widget settings.
		$widget_ops = array(
			'classname' => 'wp_phpbb_recet_topics',
			'description' => __('Allows you to display a list of recent topics within a specific forum id\'s.', 'wp_phpbb3_bridge'),
		);

		// Create the widget
		$this->WP_Widget('phpbb3-topics-widget', __('phpBB3 Topics Widget', 'wp_phpbb3_bridge'), $widget_ops);
	}

	function form($instance)
	{
		$instance = wp_parse_args($instance, $this->defaults);

		?>
		<div class="widget-content">
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo _e('Title:', 'wp_phpbb3_bridge'); ?></label>
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('forums'); ?>"><?php echo _e('Forums:', 'wp_phpbb3_bridge'); ?></label>
				<input name="<?php echo $this->get_field_name('forums'); ?>" type="text" id="<?php echo $this->get_field_id('forums'); ?>" value="<?php echo esc_attr($instance['forums']); ?>" />
				<small><?php _e('Enter the id of the forum you like to get topics from. You can get topics from more than one forums by seperating the forums id with commas. ex: 3,5,6,12', 'wp_phpbb3_bridge'); ?></small>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('total'); ?>"><?php echo _e('Total results:', 'wp_phpbb3_bridge'); ?></label>
				<input name="<?php echo $this->get_field_name('total'); ?>" type="text" id="<?php echo $this->get_field_id('total'); ?>" value="<?php echo $instance['total']; ?>" />
			</p>
		</div>
		<?php
	}

	function update($new_instance, $old_instance)
	{
		$instance = array(
			'title'				=> strip_tags($new_instance['title']),
			'forums'			=> (isset($new_instance['forums']) && $new_instance['forums']) ? strip_tags($new_instance['forums']) : '2',
			'total'				=> (isset($new_instance['total']) && $new_instance['total']) ? absint($new_instance['total']) : 5,
			'showForum'			=> (isset($new_instance['showForum']) && $new_instance['showForum']) ? 1 : 1,
			'showUsername'		=> (isset($new_instance['showUsername']) && $new_instance['showUsername']) ? 1 : 1,
		);

		return $instance;
	}

	function widget($args, $instance)
	{
		// Only run this widget on index page
		if (is_home() || is_front_page())
		{
		//	echo $before_widget . $before_title . $title . $after_title;
			phpbb::phpbb_recet_topics($instance, $this->defaults);
		//	echo $after_widget;
		}
	}
}

/**
 * Hooks a function on to a specific action.
 *
 * @param string $tag The name of the action to which the $function_to_add is hooked.
 * @param callback $function_to_add The name of the function you wish to be called.
 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
 * @param int $accepted_args optional. The number of arguments the function accept (default 1).
**/
$wp_phpbb_posting = (int) get_option('wp_phpbb_bridge_post_forum_id');

add_action('publish_post', 'wp_phpbb_posting', 10, 2);

/**
 * Called whenever a new entry is published in the Wordpress.
 *
 * @param integer $post_ID
 * @param object $post
 */
function wp_phpbb_posting($post_ID, $post)
{
	if ($post->post_status != 'publish')
	{
		return false;
	}

	global $table_prefix, $wp_user;

	if (!defined('IN_WP_PHPBB_BRIDGE'))
	{
		global $wp_phpbb_bridge_config, $phpbb_root_path, $phpEx;
		global $auth, $config, $db, $template, $user, $cache;
		require( get_template_directory() . '/includes/wp_phpbb_bridge.php' );
	}

	if (!phpbb::$config['wp_phpbb_bridge_post_forum_id'])
	{
		return false;
	}

	// Define some initial variables
	$mode = 'post';
	$forum_id = $topic_id = $post_id = 0;
	$post_data = $poll = array();
	$message_prefix = '';
	$message_tail = '';
	$subject_prefix = '';

	// We need to know some basic information in all cases before we do anything.
	// We are ading a new entry or we are editting ?
	$forum_id = phpbb::$config['wp_phpbb_bridge_post_forum_id'];
	$topic_id = $post_id = '';

	$sql = 'SELECT t.*, p.* FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
		WHERE t.topic_wp_xpost = ' . $post_ID . '
		AND p.topic_id = t.topic_id
		AND p.post_id = t.topic_first_post_id';
	$result = phpbb::$db->sql_query($sql);
	$post_data = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);
	if ($post_data)
	{
		$mode = 'edit';
		$forum_id = $post_data['forum_id'];
		$topic_id = $post_data['topic_id'];
		$post_id = $post_data['post_id'];	
	} else
	{
		$post_data = array('forum_id' => $forum_id);
	}

	if (!$post_data)
	{
		return false;
	}

	if (!function_exists('submit_post'))
	{
		include($propress_options['phpbb_script_path']. "includes/functions_posting." . PHP_EXT);
	}
	if (!class_exists('bitfield'))
	{
		include($propress_options['phpbb_script_path']. "includes/functions_content." . PHP_EXT);
	}
	if (!class_exists('parse_message'))
	{
		include($propress_options['phpbb_script_path'] . "includes/message_parser." . PHP_EXT);
	}
	$message_parser = new parse_message();

	// Get the post link
	$entry_link = get_permalink($post_ID);

	// Get the post text
	$message = $post->post_content;

	// if have "read more", cut it!
	if (preg_match('/<!--more(.*?)?-->/', $message, $matches))
	{
		list($main, $extended) = explode($matches[0], $message, 2);
		// Strip leading and trailing whitespace
		$main = preg_replace('/^[\s]*(.*)[\s]*$/', '\\1', $main);
		$message = $main . "\n\n" . '[url=' . $entry_link . ']' . phpbb::$user->lang['WP_READ_MORE'] . '[/url]';
	}

	// Get the post subject
	$subject = $post->post_title;

	// Add a Post prefix for the blog (if we have a language string filled)
	if (phpbb::$user->lang['WP_BLOG_POST_PREFIX'] != '')
	{
		$message_prefix .= sprintf(phpbb::$user->lang['WP_BLOG_POST_PREFIX'], '[url=' . $entry_link . ']', '[/url]');
	}

	// Add a Post tail for the blog (if we have a language string filled)
	if (phpbb::$user->lang['WP_BLOG_POST_TAIL'] != '')
	{
		$entry_tags = get_the_tag_list(phpbb::$user->lang['WP_TITLE_TAGS'] . ': ', ', ', "\n\n");
		$entry_cats = sprintf(phpbb::$user->lang['WP_POSTED_IN'] , get_the_category_list(', '));

		if ($entry_tags || $entry_cats)
		{
			$message_tail .= phpbb::$user->lang['WP_BLOG_POST_TAIL'] . (($entry_tags) ? $entry_tags : '') . (($entry_tags && $entry_cats) ? " | " : '') . (($entry_cats) ? $entry_cats : '') . "\n";
		}
	}

	$message = (($message_prefix) ? $message_prefix . "\n\n" : '') . $message . (($message_tail) ? "\n\n" . $message_tail : '');

	// Sanitize the post text
	$message = utf8_normalize_nfc(request_var('message', $message, true));
	// Sanitize the post subject
	$subject = utf8_normalize_nfc(request_var('subject', $subject, true));

	// Add a subject prefix for the blog (if we have a language string filled)
	if (phpbb::$user->lang['WP_BLOG_SUBJECT_PREFIX'] != '')
	{
		$subject_prefix = phpbb::$user->lang['WP_BLOG_SUBJECT_PREFIX'];
	}

	$subject = $subject_prefix . $subject;

	// Setup the settings we need to send to submit_post
	global $data;
	$data = wp_phpbb_post_data($message, $subject, $topic_id, $post_id, phpbb::$user->data, $post_data, $message_parser);

	submit_post($mode, $subject, phpbb::$user->data['username'], POST_NORMAL, $poll, $data, true);

	// Update post meta data and add the phpbb post ID
	if ($mode == 'post')
	{
		$phpbb_forum_id = (isset($data['forum_id']) && $data['forum_id']) ? $data['forum_id'] : 0;
		$phpbb_topic_id = (isset($data['topic_id']) && $data['topic_id']) ? $data['topic_id'] : 0;
		$phpbb_post_id = (isset($data['post_id']) && $data['post_id']) ? $data['post_id'] : 0;
		if ($phpbb_forum_id != 0 && $phpbb_topic_id != 0 && $phpbb_post_id != 0)
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . ' SET topic_wp_xpost = ' . $post_ID .  " WHERE topic_id =".$phpbb_topic_id;
			$result = $db->sql_query($sql);
		}
	}
}

// Setup the settings we need to send to submit_post
function wp_phpbb_post_data($message, $subject, $topic_id, $post_id, $user_row, $post_data, $message_parser)
{
	$message = wp_phpbb_html_to_bbcode($message);
	$forumid =  phpbb::$config['wp_phpbb_bridge_post_forum_id'];

	$message_parser->message = $message;
	$message_parser->parse(true, true, true);

	$data = array(
		'post_id'				=> $post_id,
		'topic_id'				=> $topic_id,
		'forum_id'				=> $forumid,
		'icon_id'				=> (isset($post_data['enable_sig'])) ? (bool) $post_data['enable_sig'] : true,
		'topic_status'			=> 1,
		'topic_title'			=> $subject,

		'topic_type'			=> POST_NORMAL,
		'enable_sig'			=> (isset($post_data['enable_sig'])) ? $post_data['enable_sig'] : true,
		'enable_bbcode'			=> (isset($post_data['enable_bbcode'])) ? $post_data['enable_bbcode'] : true,
		'enable_smilies'		=> (isset($post_data['enable_smilies'])) ? $post_data['enable_smilies'] : true,
		'enable_urls'			=> (isset($post_data['enable_urls'])) ? $post_data['enable_urls'] : true,
		'post_time'				=> time(),

		'notify'				=> (isset($post_data['notify'])) ? $post_data['notify'] : false,
		'notify_set'			=> (isset($post_data['notify_set'])) ? $post_data['notify_set'] : false,
		'poster_id'				=> $user_row['user_id'],
		'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
		'bbcode_uid'			=> $message_parser->bbcode_uid,
		'message'				=> $message_parser->message,
		'message_md5'			=> (string) md5($message_parser->message),

		'post_edit_locked'		=> (isset($post_data['post_edit_locked'])) ? $post_data['post_edit_locked'] : false,
		'force_approved_state'	=> (isset($post_data['force_approved_state'])) ? $post_data['force_approved_state'] : true,

	);

	// Merge the data we grabbed from the forums/topics/posts tables
	$data = array_merge($post_data, $data);
	return $data;
}

/**
 * Function convert HTML to BBCode 
 * 	Cut down from DeViAnThans3's version Originally (C) DeViAnThans3 - 2005 (GPL v2)
 * 	and from rss.php & feed.php
 * 	We have made several changes and fixes. 
 */
function wp_phpbb_html_to_bbcode(&$string)
{
	// Strip slashes !
//	$string = stripslashes($string);

//	$string = strip_tags($string, '<p><a><img><br><strong><em><blockquote><b><u><i><ul><ol><li><code>');

	$from = array(
		"#<a.*?href=\'(.*?)\'.*?>(.*?)<\/a>#is",
		'#<a.*?href=\"(.*?)\".*?>(.*?)<\/a>#is',

		'#<img.*?src="(.*?)".*?\/>#is',
		'#<img.*?src="(.*?)".*?>#is',

		'#<code.*?>#is',
		'#<\/code>#is',

		'#<blockquote.*?>#is',
		'#<\/blockquote>#is',

		'#<(span|div) style=\"font-size: ([\-\+]?\d+)(px|em|\%);\">(.*?)<\/(span|div)>#is',

		'#<li.*?>#is',
		'#<\/li>#is',
		'#<ul.*?>#is',
		'#<\/ul>#is',
		'#<ol.*?>#is',
		'#<\/ol>#is',

		'#<(i|em).*?>#is',
		'#<\/(i|em)>#is',
		'#<(span|div) style=\"font-style: italic;.*?\">(.*?)<\/(span|div)>#is',

		'#<(b|strong).*?>#is',
		'#<\/(b|strong)>#is',

		'#<(u|ins).*?>#is',
		'#<\/(u|ins)>#is',
		'#<(span|div) style=\"text-decoration: underline;.*?\">(.*?)<\/(span|div)>#is',

		'#<(span|div) style=\"color: \#(.*?);\">(.*?)<\/(span|div)>#is',
		'#<font.*?color=\"([a-z\-]+)\".*?>(.*?)<\/font>#is',
		'#<font.*?color=\"\#(.*?)\".*?>(.*?)<\/font>#is',

		'#<p.*?>#is',
		'#<\/p>#is',
		'#<br.*?>#is',

		// treat "del" and "strike" as undeline
		'#<(del|strike).*?>#is',
		'#<\/(del|strike)>#is',
		
		'#<dt><\/dt>#is',
	);

	$to = array(
		'[url=\\1]\\2[/url]',
		'[url=\\1]\\2[/url]',

		'[img]\\1[/img]',
		'[img]\\1[/img]',

		'[code]',
		'[/code]',

		'[quote]',
		'[/quote]',

		"[size=\\2]\\4[/size]",

		'[*]',
		'',
		'[list]',
		'[/list]',
		'[list=1]',
		'[/list]',

		'[i]',
		'[/i]',
		'[i]\\2[/i]',

		'[b]',
		'[/b]',

		'[u]',
		'[/u]',
		'[u]\\2[/u]',

		'[color=#\\2]\\3[/color]',
		'[color=\\1]\\2[/color]',
		'[color=#\\1]\\2[/color]',

		'',
		"\n",
		"\n",

		'[u]',
		'[/u]',
		
		"\n",
	);

	$string = preg_replace($from, $to, $string);

	// Remove all JavaScript Event Handlers
	$string = preg_replace('#(onabort|onblur|onchange|onclick|ondblclick|onerror|onfocus|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onresize|onselect|onsubmit|onunload)="(.*?)"#si', '', $string);

	// Remove embed and objects, but leaving a link to the video
	// Use (<|&lt;) and (>|&gt;) because can be contained into [code][/code]
	$string = preg_replace('/(<|&lt;)object[^>]*?>.*?(value|src)=(.*?)(^|[\n\t (>]).*?object(>|&gt;)/', ' <a href=$3 target="_blank"><strong>object</strong></a>',$string);
	$string = preg_replace('/(<|&lt;)embed[^>]*?>.*?(value|src)=(.*?)(^|[\n\t (>]).*?embed(>|&gt;)/', ' <a href=$3 target="_blank"><strong>embed</strong></a>',$string);

	// Potentially Malicious HTML Tags ?
	// Remove some specials html tag, because somewhere there are a mod to allow html tags ;)
	// Use (<|&lt;) and (>|&gt;) because can be contained into [code][/code]
	$string = preg_replace(
		array (
			'@(<|&lt;)head[^>]*?(>|&gt;).*?(<|&lt;)/head(>|&gt;)@siu',
			'@(<|&lt;)style[^>]*?(>|&gt;).*?(<|&lt;)/style(>|&gt;)@siu',
			'@(<|&lt;)script[^>]*?.*?(<|&lt;)/script(>|&gt;)@siu',
			'@(<|&lt;)applet[^>]*?.*?(<|&lt;)/applet(>|&gt;)@siu',
			'@(<|&lt;)noframes[^>]*?.*?(<|&lt;)/noframes(>|&gt;)@siu',
			'@(<|&lt;)noscript[^>]*?.*?(<|&lt;)/noscript(>|&gt;)@siu',
			'@(<|&lt;)noembed[^>]*?.*?(<|&lt;)/noembed(>|&gt;)@siu',
			'@(<|&lt;)iframe([^[]+)iframe(>|&gt;)@iu',
			'@(<|&lt;)/?((frameset)|(frame)|(iframe))@iu',
		),
		array (
			'[code]head[/code]',
			'[code]style[/code]',
			'[code]script[/code]',
			'[code]applet[/code]',
			'[code]noframes[/code]',
			'[code]noscript[/code]',
			'[code]noembed[/code]',
			'[code]iframe[/code]',
			'[code]frame[/code]',
		),
	$string);

	// prettify estranged tags
	$string = str_replace("&nbsp;", " ", $string); 
	$string = str_replace('&amp;lt;', '<', $string);
	$string = str_replace('&amp;gt;', '>', $string);
	$string = str_replace('&lt;', '<', $string);
	$string = str_replace('&gt;', '>', $string);
	$string = str_replace('&quot;', '"', $string);
	$string = str_replace('&amp;', '&', $string);

//	$string = htmlspecialchars($string); 
	// kill any remaining
	$string = strip_tags($string);

	// Other control characters
//	$string = preg_replace('#(?:[\x00-\x1F\x7F]+|(?:\xC2[\x80-\x9F])+)#', '', $string);

	return $string;
}

add_action( 'show_user_profile', 'wp_phpbb_add_extra_profile_fields', 10 );
add_action( 'edit_user_profile', 'wp_phpbb_add_extra_profile_fields', 10 );
function wp_phpbb_add_extra_profile_fields($user)
{
	if (!current_user_can('edit_user', $user->ID))
	{
		return false;
	}

	$phpbb_user_id = (isset($user->phpbb_userid) && $user->phpbb_userid) ? $user->phpbb_userid : 0;
	?>
	<table class="form-table">
		<tr>
			<th><label for="phpbb_userid"><?php _e('phpBB user ID', 'wp_phpbb3_bridge'); ?></label></th>
			<td><input type="text" name="phpbb_userid" id="phpbb_userid" value="<?php echo $phpbb_user_id ?>" class="regular-text" /><br />
				<span class="description"><?php _e("If you would like to change the phpBB user ID type a new one. This action will connect this user with one at your phpBB board.", 'wp_phpbb3_bridge'); ?></span></td>
		</tr>
	</table>
	<?php
}

add_action( 'personal_options_update', 'wp_phpbb_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'wp_phpbb_save_extra_profile_fields' );
function wp_phpbb_save_extra_profile_fields($user_id)
{
	if (!current_user_can('edit_user', $user_id))
	{
		return false;
	}

	$phpbb_user_id = (isset($_POST['phpbb_userid']) && $_POST['phpbb_userid'] != 0) ? $_POST['phpbb_userid'] : 0;
	if ($phpbb_user_id == 0)
	{
		delete_user_meta($user_id, 'phpbb_userid');
	}
	else
	{
		update_user_meta($user_id, 'phpbb_userid', $phpbb_user_id);
	}
}

// Thank-you Dion Designs :)
function show_phpbb_link($content) {
	if (!defined('IN_WP_PHPBB_BRIDGE'))
	{
		global $wp_phpbb_bridge_config, $phpbb_root_path, $phpEx;
		global $auth, $config, $db, $template, $user, $cache;
		include(TEMPLATEPATH . '/includes/wp_phpbb_bridge.php');
	}
	$postID = get_the_ID();
	if(empty($postID)) {
		return $content;
	}
	$sql = 'SELECT topic_id, forum_id, topic_replies FROM ' . TOPICS_TABLE . ' WHERE topic_wp_xpost = ' . $postID;
	$result = phpbb::$db->sql_query($sql);
	$post_data = phpbb::$db->sql_fetchrow($result);
	$board_url = generate_board_url(false) . '/';
	$web_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : PHPBB_ROOT_PATH;
	$replies = $post_data['topic_replies'];

	if ($post_data) {
		$rsuffix = $rtext = $rbutton = '';
		if ($replies) {
			if ($replies != 1) {
				$rsuffix = 's';
			}
			$rbutton = '&nbsp;&nbsp;&nbsp;&nbsp;<a class="button1" href="' . $web_path . 'viewtopic.php?f='. $post_data['forum_id'] . '&amp;t=' . $post_data['topic_id'] . '">View the Discussion</a>';
			$rtext =  '<|DD|>' . $replies . ' Comment' . $rsuffix;
		}
		$content .= '<div class="xpost-link">' . '<a class="button1" href="' . $web_path . 'posting.php?mode=reply&amp;f=' . $post_data['forum_id'] . '&amp;t=' . $post_data['topic_id'] . '">Comment On this Article</a>' . $rbutton . '</div>' . $rtext;
	}
	return $content;
}
add_filter('the_content', 'show_phpbb_link');


// Don't nag users who can't switch themes.
if ( ! is_admin() || ! current_user_can( 'switch_themes' ) )
	return;

function wphpbb_admin_notice() {
	if ( isset( $_GET['wphpbb-dismiss'] ) )
		set_theme_mod( 'wphpbb', true );

	$dismiss = get_theme_mod( 'wphpbb', false );
	if ( $dismiss )
		return;
	?>
	<div class="updated wphpbb-notice">
		<p><?php printf( __( 'In order for this bridge to work correctly, you will <a target="_blank" href="%s">need to configure it</a>. <a href="%s">I have already configured it.</a>', 'wp_phpbb3_bridge' ), admin_url('themes.php?page=propress-settings'), add_query_arg( 'wphpbb-dismiss', 1 ) ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'wphpbb_admin_notice' );

?>
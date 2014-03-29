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


//Load up external files
require( get_template_directory() . '/settings/wp-united.php' );
require( get_template_directory() . '/includes/options.php' );
//require( get_template_directory() . '/includes/custom.php' );
require( get_template_directory() . '/includes/updater.php' );
require( get_template_directory() . '/includes/add-links.php' );
require( get_template_directory() . '/includes/wp-profile.php' );

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

add_action( 'admin_notices', 'wphpbb_admin_notice' );
add_action( 'personal_options_update', 'wp_phpbb_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'wp_phpbb_save_extra_profile_fields' );
add_action( 'after_setup_theme', 'phpbb_bridge_setup' );
add_action( 'widgets_init', 'wp_phpbb_widgets_init');
add_action( 'show_user_profile', 'wp_phpbb_add_extra_profile_fields', 10 );
add_action( 'edit_user_profile', 'wp_phpbb_add_extra_profile_fields', 10 );
add_action( 'publish_post', 'wp_phpbb_posting', 10, 2);
add_action( 'wp_enqueue_scripts', 'propress_enqueue_js_scripts' );

if (!defined('WP_ADMIN')) {
	add_filter('logout_url', 'wp_phpbb_logout');
	add_filter('login_url', 'wp_phpbb_login');
	add_filter('register_url', 'wp_phpbb_register');
	add_filter('the_content', 'show_phpbb_link');
	add_filter('the_excerpt', 'show_phpbb_link');
}

add_filter('widget_title', 'wp_phpbb_widget_title');
add_filter('pre_set_site_transient_update_themes', 'check_for_update');
add_filter('themes_api', 'my_theme_api_call', 10, 3);

function phpbb_bridge_setup()
{
	// This theme supports a custom header image
	add_theme_support('custom-header');

	// This theme supports post thumbnails
	add_theme_support('post-thumbnails');
	add_image_size('dd-featured', 384, 256);
	add_image_size('dd-featured-mini', 96, 64);

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus(array(
		'primary' => __('Header Menu', 'phpbbwpconnect'),
		'secondary' => __('Footer Menu', 'phpbbwpconnect'),
	));
}

function wp_phpbb_logout()
{
	$temp =  generate_board_url() . '/';
	return !is_admin() ? $temp.'ucp.php?mode=logout&amp;sid='.phpbb::$user->session_id : '';
}

function wp_phpbb_login()
{
	$redirect = request_var('redirect', home_url(add_query_arg(array())));
	$temp =  generate_board_url() . '/';
	return $temp.'ucp.php?mode=login&amp;redirect='.$redirect;
}

function wp_phpbb_register()
{
	$temp = generate_board_url() . '/';
	return $temp . 'ucp.php?mode=register';
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
function propress_enqueue_js_scripts()
{
	wp_enqueue_style('phpbb-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_script( 'phpbb-script', get_template_directory_uri() . '/js/jquery.validate.js', array( 'jquery' ));
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
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


/**
 * Register widgetized area, and available widdgets for the bridge.
 */
function wp_phpbb_widgets_init()
{
	// Register Single Sidebar
	register_sidebar(
		array(
			'id'			=> 'wp_phpbb-widget-area',
			'name'			=> __('Primary Widget Area', 'phpbbwpconnect'),
			'description'	=> __('The primary widget area.', 'phpbbwpconnect'),
			'before_widget'	=> "\n" . '<div class="panel bg3">' . "\r\t" . '<div class="inner"><span class="corners-top"><span></span></span>' . "\n\t\t",
			'after_widget'	=> "\n\t" . '<span class="corners-bottom"><span></span></span></div>' . "\r" . '</div>' . "\n",
			'before_title'	=> '<h3>',
			'after_title'	=> '</h3>' . "\n",
		)
	);

	register_widget('WP_Widget_phpbb_recet_topics');
}

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
	);

	function WP_Widget_phpbb_recet_topics()
	{
		// Widget settings.
		$widget_ops = array(
			'classname' => 'wp_phpbb_recet_topics',
			'description' => __('Allows you to display a list of recent topics within a specific forum id\'s.', 'phpbbwpconnect'),
		);

		// Create the widget
		$this->WP_Widget('phpbb3-topics-widget', __('phpBB3 Topics Widget', 'phpbbwpconnect'), $widget_ops);
	}

	function form($instance)
	{
		$instance = wp_parse_args($instance, $this->defaults);

		?>
		<div class="widget-content">
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo _e('Title:', 'phpbbwpconnect'); ?></label>
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('forums'); ?>"><?php echo _e('Forums:', 'phpbbwpconnect'); ?></label>
				<input name="<?php echo $this->get_field_name('forums'); ?>" type="text" id="<?php echo $this->get_field_id('forums'); ?>" value="<?php echo esc_attr($instance['forums']); ?>" />
				<small><?php _e('Enter the id of the forum you like to get topics from. You can get topics from more than one forums by seperating the forums id with commas. ex: 3,5,6,12', 'phpbbwpconnect'); ?></small>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('total'); ?>"><?php echo _e('Total results:', 'phpbbwpconnect'); ?></label>
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
		);

		return $instance;
	}

	function widget($args, $instance)
	{
			phpbb::phpbb_recet_topics($instance, $this->defaults);
	}
}

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
		global $auth, $config, $db, $template, $user, $cache, $wpdb;
		require( get_template_directory() . '/includes/wp_phpbb_bridge.php' );
	}

	if (!phpbb::$config['wp_phpbb_bridge_post_forum_id'])
	{
		return false;
	}

	if ($_POST['crosspost_enable'] == 'n')
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
		include($propress_options['phpbb_path']. "includes/functions_posting." . PHP_EXT);
	}
	if (!class_exists('bitfield'))
	{
		include($propress_options['phpbb_path']. "includes/functions_content." . PHP_EXT);
	}
	if (!class_exists('parse_message'))
	{
		include($propress_options['phpbb_path'] . "includes/message_parser." . PHP_EXT);
	}
	$message_parser = new parse_message();

	// Get the post link
	$entry_link = get_permalink($post_ID);

	// Get the post text
	if (!empty($post->post_excerpt)) {
		$message = __('Please use this topic to discuss', 'phpbbwpconnect') . ' <a href="' . $entry_link . '">' . $post->post_title . '</a>' . "\n\n" . '<blockquote>' . apply_filters('the_excerpt', $post->post_excerpt) . '</blockquote>';
	}
	else {
		$message = __('Please use this topic to discuss', 'phpbbwpconnect') . ' <a href="' . $entry_link . '">' . $post->post_title . '</a>';
	}

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
	global $data, $hack, $wpdb ;
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

// Don't nag users who can't switch themes.
if ( ! is_admin() || ! current_user_can( 'switch_themes' ) )
	return;

function wphpbb_admin_notice() {
	if ( isset( $_GET['phpbbtowp-dismiss'] ) )
		set_theme_mod( 'phpbbtowp', true );

	$dismiss = get_theme_mod( 'phpbbtowp', false );
	if ( $dismiss )
		return;
	?>
	<div class="updated wphpbb-notice">
		<p><?php printf( __( 'In order for this bridge to work correctly, you will <a target="_blank" href="%s">need to configure it</a>. <a href="%s">I have already configured it.</a>', 'phpbbwpconnect' ), admin_url('themes.php?page=wp-united-setup'), add_query_arg( 'phpbbtowp-dismiss', 1 ) ); ?></p>
	</div>
	<?php
}

// Code taken from BridgeDD by Dion Designs.
add_action('personal_options', 'no_options_start');
add_action('show_user_profile', 'no_options_end');
add_action('edit_user_profile', 'no_options_end');

// inhibit display of user options in dashboard, let user/admin know what's going on
function no_options_start() {
	echo '</table><style type="text/css">#bdd_junk tr, #bdd_junk h3{display:none}#bdd_junk #bdd_role{display:table-row}#bdd_junk table{margin:0}</style><div class="update-nag" style="margin-top:10px"><h3>' . __('Please set all other User options in phpBB.', 'phpbbwpconnect') . '</h3></div>';
	ob_start();
}

function no_options_end() {
	$stuff = ob_get_clean();
	$stuff = str_replace(array("\n","\t",'<tr><th><label for="role">'), array('','','<tr id="bdd_role"><th><label for="role">'), $stuff);
	echo '<div id="bdd_junk">' . $stuff . '</div>';
}


add_action('edit_form_top', 'crosspost_message', 10, 1);
// let users know to set excerpts if they want an excerpt to be cross-posted
function crosspost_message($post) {
		echo '<div id="postbox-container-1" class="update-nag">
<div><div id="postWPUstatusdiv" style"background:white;"><h3><span>Cross-post to Forums?</span></h3>
<div class="inside"><div><div>'
	. __('If you would like to cross-post an excerpt, please create an excerpt. ', 'phpbbwpconnect')
	. __('Click the Screen Options tab above to display the Excerpt box.', 'phpbbwpconnect') .'
	</div><br>'
	. __('Do you want this post to be cross posted to your forum?', 'phpbbwpconnect') . ':<strong>
	<input name="crosspost_enable" value="y" checked="checked" type="radio">' . __('Yes', 'phpbbwpconnect') . '
	<input name="crosspost_enable" value="n" type="radio">' . __('No', 'phpbbwpconnect') . '</strong></div></div></div></div></div>';
}

?>
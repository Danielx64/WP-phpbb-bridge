<?php
/**
 * @package Wordparess
 * @version 1.6
 */

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
		include_once dirname( __FILE__ ) . '/wp_phpbb_bridge.php';
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

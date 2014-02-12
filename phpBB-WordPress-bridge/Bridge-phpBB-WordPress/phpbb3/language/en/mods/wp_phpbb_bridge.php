<?php
/**
 * 
 * @package: phpBB 3.0.9 :: BRIDGE phpBB to WordPress -> root/language/en/mods :: [en][English]
 * @version: $Id: wp_phpbb_bridge.php, v0.0.9 2011/10/25 11:10:25 leviatan21 Exp $
 * @copyright: leviatan21 < info@mssti.com > (Gabriel) http://www.mssti.com/phpbb3/
 * @license: http://opensource.org/licenses/gpl-license.php GNU Public License 
 * @author: leviatan21 - http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
 * 
 */

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. "Message %d" is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., "Click %sHERE%s" is fine
// Reference : http://www.phpbb.com/mods/documentation/phpbb-documentation/language/index.php#lang-use-php
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'WP_PHPBB_BRIDGE_MANAGE'					=> 'phpBB to WordPress Settings',
	'WP_PHPBB_BRIDGE_MANAGE_EXPLAIN'			=> 'Welcome to phpBB to WordPress Settings Management Section.<br />Here you can determine the basic operation of the Bridge in relation to phpBB.',
	'WP_PHPBB_BRIDGE_BASIC'						=> 'Basic settings',
	'WP_PHPBB_BRIDGE_PATHS'						=> 'Path settings',
	'WP_PHPBB_BRIDGE_STYLE'						=> 'Style settings',
	'WP_PHPBB_BRIDGE_WPFOUNDER'					=> 'WordPress founder ID',
	'WP_PHPBB_BRIDGE_PHPBBFOUNDER'				=> 'phpBB founder ID',

	'HOMEPAGE_URL'								=> 'Homepage url',
	'HOMEPAGE_URL_EXPLAIN'						=> 'Enter here your homepage’s URL. You can either use something like "../site", if your homepage is at the same domain, or use the absolute path for other domains.. ',
	
	
	'CROSSPOSTCONTENT'							=> 'Crosspost post content?',
	'CROSSPOSTCONTENT_EXPLAIN'					=> 'Do you want to crosspost the post content? For phpBB.com style select no',

	'HOMEPAGE_TITLE'							=> 'Homepage title',
	'HOMEPAGE_TITLE_EXPLAIN'					=> 'This title will show when you have your mouse over your homepage link. If you don´t want to show a title, just leave this field blank.',
	'SHOW_HOMEPAGE'								=> 'Show your homepage URL on the header',
	'SHOW_HOMEPAGE_EXPLAIN'						=> 'If you select "No" your homepage URL will not be shown on the header',
	'WPHPBB_INFO'								=> 'WordPress Path',
	'WPHPBB_INFO_EXPLAIN'						=> 'This is the path to your WordPress installation. Put in the full path to your wordpress root folder. An example would be /home/danielx/public_html/',
	'WP_PHPBB_BRIDGE_FORUM_URL'					=> 'Forum Path',
	'WP_PHPBB_BRIDGE_FORUM_URL_EXPLAIN'			=> 'Enter here your forum’s URL. An example would be http://localhost/forums/',
	'WP_PHPBB_BRIDGE_SIDEBAR'					=> 'Widgets column width',
	'WP_PHPBB_BRIDGE_SIDEBAR_EXPLAIN'			=> 'The right column width, in pixels.',
	'WP_PHPBB_BRIDGE_AV'						=> 'Avatars width',
	'WP_PHPBB_BRIDGE_AV_EXPLAIN'				=> 'The width size of avatars, in pixels',
	'WP_PHPBB_BRIDGE_XPOST'						=> 'Post forum ID',
	'WP_PHPBB_BRIDGE_XPOST_EXPLAIN'				=> 'What forum do you want your crossposted posts to go to?',


	// the page title numbering
	'WP_PAGE_NUMBER'			=> 'Page %s',
	'WP_MOD_TITLE'				=> 'phpBB to WordPress',

	// footer
	'WP_DEBUG_NOTE'				=> '%d queries. %s seconds.',

	// Navbar
	'WP_TITLE_BLOG'				=> 'Blog',
	'WP_TITLE_BLOG_EXPLAIN'		=> 'Click here to go to the Blog',
	'WP_TITLE_FORUM'			=> 'Forum',
	'WP_TITLE_FORUM_EXPLAIN'	=> 'Click here to go to the Forum',
	'WP_ADMIN_PANEL'			=> 'Dashboard',

	// Sidebar
	'WP_AUTHOR_TITLE'			=> 'Author',
	'WP_FORUM_POSTS'			=> 'Forum posts',
	'WP_BLOG_POSTS'				=> 'Blog posts',
	'WP_SEARCH_USER_POSTS'		=> 'Search user’s posts',
	'WP_TITLE_PAGES'			=> 'Pages',
	'WP_TITLE_ARCHIVES'			=> 'Archives',
	'WP_TITLE_CATEGORIES'		=> 'Categories',	
	'WP_TITLE_TAGS'				=> 'Tags',
	'WP_TITLE_TAG_CLOUD'		=> 'Cloud tags',
	'WP_TITLE_BOOKMARKS'		=> 'Bookmarks',
	'WP_TITLE_META'				=> 'Meta',
	'WP_TITLE_RECENT_TOPICS'	=> 'Recent Topics',

	// Login/Logout
	'WP_LOGIN_FAILED'			=> 'You were not logged in, as the request did not match your session. Please contact the board administrator if you continue to experience problems.',
	'WP_LOGIN_WAIT'				=> 'Please wait',
	'WP_INVALID_UNSERIALIZE'	=> 'The field “Wordpress user login” has no valid data.',
	'WP_INVALID_ENCRYPT_VALUE'	=> 'The field “ciphered” has no valid data.',
	'WP_INVALID_LOGIN_VALUE'	=> 'The field “Wordpress user login” has an invalid value.',
	'WP_INVALID_USERID_VALUE'	=> 'The field “Wordpress user id” has an invalid value.',

	// Search
//	'WP_TITLE_SEARCH'				=> 'Blog Search',
	'WP_SEARCH_NOT_FOUND'			=> 'Not Found',
	'WP_SEARCH_NOT_FOUND_EXPLAIN'	=> 'Sorry, but you are looking for something that isn’t here.',
	'WP_JUMP_TO_POST'				=> 'Jump to entries',

	// WP entries
	'WP_POST_NOT_FOUND_EXPLAIN'	=> 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.',
	'WP_READ_MORE'				=> 'Read full entry »',
	'WP_POSTED_IN'				=> 'Posted in: %s',

	'WP_POST_TOPIC'				=> 'Create a new entry',
	'WP_COMMENTS_PASSWORED'		=> 'Enter your password to view comments.',
	'WP_COMMENTS_TO'			=> ' to “%s” ',
	// Index & Topics navigation
	'PREVIOUS_ENTRIE'			=> '« Previous Entries',
	'NEXT_ENTRIE'				=> 'Next Entries » ',

	// Moderation actions
	'WP_COMMENT_APPROVE'				=> 'Approve',
	'WP_COMMENT_APPROVE_EXPLAIN'		=> 'Approve this comment',
	'WP_COMMENT_UNAPPROVE'				=> 'Unapprove',
	'WP_COMMENT_UNAPPROVE_EXPLAIN'		=> 'Unapprove this comment',
	'WP_COMMENT_UNAPPROVED'				=> 'This comment is waiting for approval',
	'WP_COMMENT_EDIT'					=> 'Edit',
	'WP_COMMENT_EDIT_EXPLAIN'			=> 'Edit comment',
	'WP_COMMENT_REPLY'					=> 'Reply',
	'WP_COMMENT_REPLY_EXPLAIN'			=> 'Reply to this comment',
	'WP_COMMENT_SPAM'					=> 'Spam',
	'WP_COMMENT_SPAM_EXPLAIN'			=> 'Mark this comment as spam',
	'WP_COMMENT_REPORTED_NOTE'			=> 'This comment is marked as Spam',
	'WP_COMMENT_UNSPAM'					=> 'Not Spam',
	'WP_COMMENT_UNSPAM_EXPLAIN'			=> 'Mark this comment as not Spam',
	'WP_COMMENT_TRASH'					=> 'Trash',
	'WP_COMMENT_TRASH_EXPLAIN'			=> 'Move this comment to the trash',
	'WP_COMMENT_UNTRASH'				=> 'Not Trash',
	'WP_COMMENT_UNTRASH_EXPLAIN'		=> 'Restore this comment from the trash',
	'WP_COMMENT_UNTRASHED_NOTE'			=> 'This comment is in the Trash',
	'WP_COMMENT_DELETE'					=> 'Delete',
	'WP_COMMENT_DELETE_EXPLAIN'			=> 'Delete Permanently',

	'WP_ERROR_GENERAL'					=> 'Not Found',
	'WP_ERROR_404'						=> 'Apologies, but the page you requested could not be found. Perhaps searching will help.',
	'WP_TITLE_ARCHIVE_EXPLAIN'			=> 'You are currently browsing the <a href="%1$s/">%2$s</a> blog archives.',
	'WP_TITLE_CATEGORIES_EXPLAIN'		=> 'You are currently browsing the archives for the <em>%s</em> category.',
	'WP_TITLE_ARCHIVE_DAY_EXPLAIN'		=> 'You are currently browsing the <a href="%1$s/">%2$s</a> blog archives for the day %3$s.',
	'WP_TITLE_ARCHIVE_MONTH_EXPLAIN'	=> 'You are currently browsing the <a href="%1$s/">%2$s</a> blog archives for %3$s.',
	'WP_TITLE_ARCHIVE_YEAR_EXPLAIN'		=> 'You are currently browsing the <a href="%1$s/">%2$s</a> blog archives for the year %3$s.',
	'WP_TITLE_ARCHIVE_SEARCH_EXPLAIN' 	=> 'You have searched the <a href="%1$s/">%2$s</a> blog archives for <strong>&#8216;%3$s&#8217;</strong>.',

	// pbpbb posting 
	'WP_BLOG_SUBJECT_PREFIX'			=> '[BLOG]: ',
	'WP_BLOG_POST_PREFIX'				=> 'This is a [b]Blog entry[/b]. To read the original post, please Click » %1$s HERE %2$s',
	'WP_BLOG_POST_TAIL'					=> '[b]Entry details: [/b]',

));

?>
<?php 
/** 
* @package phpBB to WP connector
* @version $Id: 1.5.0
* @copyright (c) 2013-2014 danielx64.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author Danielx64
* 
* @based off WP-United
* @orginal author John Wells wp-united.com
*/

/**
 */
if ( !defined('IN_PHPBB') && !defined('ABSPATH') ) exit;


/**
 * Adds a traling slash to a string if one is not already present.
 * @param string $path
 * @return string modified path
 */
function add_trailing_slash($path) {
	return ( $path[strlen($path)-1] == "/" ) ? $path : $path . "/";
}


/**
 * Clean and standardise a provided file path
 */
function clean_path($value) {
	$value = trim($value);
	$value = str_replace('\\', '/', $value);
	$value = (get_magic_quotes_gpc()) ? stripslashes($value) : $value;
	return $value;
}



/**
 * General error handler for arbitrating phpBB & WordPress errors.
 */
function wpu_msg_handler($errno, $msg_text, $errfile, $errline) {
	global $phpbbForum, $IN_WORDPRESS;
	switch ($errno) {
		case E_NOTICE:
		case E_WARNING:
			return false;
		break;
	}
	if(!$IN_WORDPRESS) {
		return msg_handler($errno, $msg_text, $errfile, $errline);
	}
	 return false;
}

function wpu_ajax_header() {
	header('Content-Type: application/xml; charset=UTF-8'); 
	header('Cache-Control: private, no-cache="set-cookie"');
	header('Expires: 0');
	header('Pragma: no-cache');
	
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
}

function wpu_get_curr_page_link() {
	
	$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === false ? 'http://' : 'https://';
	$currURL = $protocol . $_SERVER['HTTP_HOST']  . htmlspecialchars($_SERVER['REQUEST_URI']);
	return $currURL;
}

function wpu_get_doc_root() {
	$docRoot =  (isset($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF']) );
	$docRoot = @realpath($docRoot); 
	$docRoot = str_replace( '\\', '/', $docRoot);
	$docRoot = ($docRoot[strlen($docRoot)-1] == '/') ? $docRoot : $docRoot . '/';
	return $docRoot;
}

function wpu_js_translate($content) {
	echo wpu_fix_translation($content);
}

function wpu_fix_translation($content) {
	return str_replace(array("\n", "\r", "'"), array('', '', "\'"), $content);
}

function wpu_reload_page_if_no_post() {
	global $phpbbForum;
	
	// don't reloads if something has been POSTed to phpBB or WordPress.
	$fStateChanged = $phpbbForum->foreground();
	$hasPostVars = sizeof($_POST);
	$phpbbForum->restore_state($fStateChanged);
	if($hasPostVars || sizeof($_POST)) {
		return false;
	}
	
	$currPage = wpu_get_curr_page_link();
	
	// prevent infinite reloads
	if(stristr($currPage, 'wpureload=1') !== false) {
		return false;
	}
	$currPage .= (stristr($currPage, '?') !== false) ? '&wpureload=1' : '?wpureload=1';
	
	// OK, let's do it... Reload one way or another.
	if(!headers_sent()) {
		header('Location: ' . $currPage, true, 302);
		exit();
	} else {
				   
		echo '<script type="text/javascript">';
		echo 'window.location.href="'.$currPage.'";';
		echo '</script>';
		echo '<noscript>';
		echo '<meta http-equiv="refresh" content="0;url='.$currPage.'" />';
		echo '</noscript>';
		exit();
	}
}

// Done. End of file.
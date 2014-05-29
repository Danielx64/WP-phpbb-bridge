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
	Much of this was taken from wp-united and is only used to create the option page.
	While it is not ideal, that's all I got for now.
 */
if( !class_exists( 'WP_United_Plugin' ) ) {
	require_once(get_template_directory() . '/settings/base-classes.php');
	require_once(get_template_directory() . '/settings/plugin-main.php');
	global $wpUnited;
	$wpUnited = new WP_United_Plugin();
}
$wpUnited->wp_init();

// That's all. Happy blogging!
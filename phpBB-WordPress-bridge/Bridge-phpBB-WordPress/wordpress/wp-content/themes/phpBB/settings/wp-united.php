<?php

/*
Plugin Name: WP-United: phpBB WordPress Integration
Plugin URI: http://www.wp-united.com
Description: WP-United connects to your phpBB forum and integrates user sign-on, behaviour and theming. Once your forum is up and running, you should not disable this plugin.
Author: John Wells
Author URI: http://www.wp-united.com
Version: 0.9.2.5
Text Domain: wp-united
Domain Path: /languages
Last Updated: 26 March 2013
* 
*/

/** The WP-United class may be called as a base class from the phpBB side, or a fully fledged plugin class from here. 
 *  This file could be invoked from either side to instantiate the object.
 *  The WP-United class then decorates itself with a cross-package settings object.
 */
if( !class_exists( 'WP_United_Plugin' ) ) {
	require_once(get_template_directory() . '/settings/base-classes.php');
	require_once(get_template_directory() . '/settings/plugin-main.php');
	global $wpUnited;
	$wpUnited = new WP_United_Plugin();
}
$wpUnited->wp_init();

// That's all. Happy blogging!
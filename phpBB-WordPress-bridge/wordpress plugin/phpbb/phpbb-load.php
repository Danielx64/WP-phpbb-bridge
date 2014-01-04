<?php
/**
 * @package Wordparess
 * @version 1.6
 */
/*
Plugin Name: Wordpress to PHPBB
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: WordPress - phpBB Integration Mod This Birdge makes possible to integrate your phpBB into your Wordpress Blog, sharing users. If the phpBB users do not exist in WP it will be automatically created as a "Subscriber" Want to have wordpress match your forum style? Get the theme instead.
Author: Matt Mullenweg
Version: 1.6
Author URI: http://ma.tt/
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

include_once dirname( __FILE__ ) . '/functions/wp-clean.php';
include_once dirname( __FILE__ ) . '/functions/wp-profile.php';


?>

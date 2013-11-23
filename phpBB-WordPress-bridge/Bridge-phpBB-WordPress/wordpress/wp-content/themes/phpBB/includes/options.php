<?php
/**
 * propressTheme Options
 *
 * This file defines the Options for the propressTheme.
 * 
 * Theme Options Functions
 * 
 *  - Define Default Theme Options
 *  - Register/Initialize Theme Options
 *  - Define Admin Settings Page
 *  - Register Contextual Help
 * 
 * @package 	propress* @copyright	Copyright (c) 2011, Chip Bennett
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 (or newer)
 *
 * @since 		propress1.0
 */

/**
 * Globalize the variable that holds the Theme Options
 * 
 * @global	array	$propress_options	holds Theme options
 */
global $propress_options;

/**
 * propressTheme Settings API Implementation
 *
 * Implement the WordPress Settings API for the 
 * propressTheme Settings.
 * 
 * @link	http://codex.wordpress.org/Settings_API	Codex Reference: Settings API
 * @link	http://ottopress.com/2009/wordpress-settings-api-tutorial/	Otto
 * @link	http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/	Ozh
 */
function propress_register_options(){
	require( get_template_directory() . '/includes/options-register.php' );
}
// Settings API options initilization and validation
add_action( 'admin_init', 'propress_register_options' );

/**
 * Setup the Theme Admin Settings Page
 * 
 * Add "propressOptions" link to the "Appearance" menu
 * 
 * @uses	propress_get_settings_page_cap()	defined in \functions\wordpress-hooks.php
 */
function propress_add_theme_page() {
	// Globalize Theme options page
	global $propress_settings_page;
	// Add Theme options page
	$propress_settings_page = add_theme_page(
		// $page_title
		// Name displayed in HTML title tag
		__( '[BRIDGE] phpBB to Wordpress', 'propress' ), 
		// $menu_title
		// Name displayed in the Admin Menu
		__( '[BRIDGE] phpBB to Wordpress', 'propress' ), 
		// $capability
		// User capability required to access page
		propress_get_settings_page_cap(), 
		// $menu_slug
		// String to append to URL after "themes.php"
		'propress-settings', 
		// $callback
		// Function to define settings page markup
		'propress_admin_options_page'
	);
}
// Load the Admin Options page
add_action( 'admin_menu', 'propress_add_theme_page' );

/**
 * propressTheme Settings Page Markup
 * 
 * @uses	propress_get_current_tab()	defined in \functions\custom.php
 * @uses	propress_get_page_tab_markup()	defined in \functions\custom.php
 */
function propress_admin_options_page() { 
	// Determine the current page tab
	$currenttab = propress_get_current_tab();
	// Define the page section accordingly
	$settings_section = 'propress_' . $currenttab . '_tab';
	?>

	<div class="wrap">
		<?php propress_get_page_tab_markup(); ?>
		<?php if ( isset( $_GET['settings-updated'] ) ) {
    			echo '<div class="updated"><p>';
				echo __( 'BRIDGE phpBB & WordPress Plugin settings updated successfully.', 'propress' );
				echo '</p></div>';
		} ?>
		<form action="options.php" method="post">
		<?php 
			// Implement settings field security, nonces, etc.
			settings_fields('theme_propress_options');
			// Output each settings section, and each
			// Settings field in each section
			do_settings_sections( $settings_section );
		?><br />
			If you are not sure what is the full path to your forum then create a file "ie: mypath.php" into the folder phpBB3 and enter the following code in it:
			 <code><?php echo $_SERVER['SCRIPT_FILENAME']; ?></code> You will get something like this :<br /><code><?php echo $_SERVER['SCRIPT_FILENAME']; ?></code>
			 <br />
			 The above code will return to you the full path to your forum + the file name "mypath.php".
			 <br />
			 Place the "mypath.php" with the "config.php" and then copy all that path to use it in the theme configuration.
			 <br />
			 Hope this help you :)
<br />
			<?php submit_button( __( 'Save Settings', 'propress' ), 'primary', 'theme_propress_options[submit-' . $currenttab . ']', false ); ?>
			<?php submit_button( __( 'Reset Defaults', 'propress' ), 'secondary', 'theme_propress_options[reset-' . $currenttab . ']', false ); ?>
		</form>
	</div>
<?php 
}

/**
 * propressTheme Option Defaults
 * 
 * Returns an associative array that holds 
 * all of the default values for all Theme 
 * options.
 * 
 * @uses	propress_get_option_parameters()	defined in \functions\options.php
 * 
 * @return	array	$defaults	associative array of option defaults
 */
function propress_get_option_defaults() {
	// Get the array that holds all
	// Theme option parameters
	$option_parameters = propress_get_option_parameters();
	// Initialize the array to hold
	// the default values for all
	// Theme options
	$option_defaults = array();
	// Loop through the option
	// parameters array
	foreach ( $option_parameters as $option_parameter ) {
		$name = $option_parameter['name'];
		// Add an associative array key
		// to the defaults array for each
		// option in the parameters array
		$option_defaults[$name] = $option_parameter['default'];
	}
	// Return the defaults array
	return $option_defaults;
}

/**
 * propressTheme Option Parameters
 * 
 * Array that holds parameters for all options for
 * propress. The 'type' key is used to generate
 * the proper form field markup and to sanitize
 * the user-input data properly. The 'tab' key
 * determines the Settings Page on which the
 * option appears, and the 'section' tab determines
 * the section of the Settings Page tab in which
 * the option appears.
 * 
 * @return	array	$options	array of arrays of option parameters
 */
function propress_get_option_parameters() {

    $options = array(
		'wp_phpbb_bridge' => array(
			'name' => 'wp_phpbb_bridge',
			'title' => __( 'Enable Bridge', 'propress' ),
			'type' => 'checkbox',
			'description' => __( 'This will make the Bridge unavailable to use.', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => true
		),
		'phpbb_root_path' => array(
			'name' => 'phpbb_root_path',
			'title' => __( 'Path to phpBB: (*) ', 'propress' ),
			'type' => 'text',
			'sanitize' => 'text',
			'description' => __( 'The path where phpBB is located relative to the domain name. ', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => './forums/'
		),
		'phpbb_script_path' => array(
			'name' => 'phpbb_script_path',
			'title' => __( 'Server root path to phpBB: (*) ', 'propress' ),
			'type' => 'text',
			'sanitize' => 'text',
			'description' => __( 'Relative path from the server root.', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => 'forums/'
		),
		'wordpress_script_path' => array(
			'name' => 'wordpress_script_path',
			'title' => __( 'Server root path to WordPress: (*)', 'propress' ),
			'type' => 'text',
			'sanitize' => 'text',
			'description' => __( 'Relative path from the server root.', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => '/'
		),

		'wp_phpbb_bridge_permissions_forum_id' => array(
			'name' => 'wp_phpbb_bridge_permissions_forum_id',
			'title' => __( 'Permissions forum ID: ', 'propress' ),
			'type' => 'text',
			'sanitize' => 'absint',
			'description' => __( 'The number of your Forum (not Category) where to use permissions.', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => '2'
		),
		'wp_phpbb_bridge_widgets_column_width' => array(
			'name' => 'wp_phpbb_bridge_widgets_column_width',
			'title' => __( 'Widgets column width:', 'propress' ),
			'type' => 'text',
			'sanitize' => 'absint',
			'description' => __( 'The right column width, in pixels. ', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => '300'
		),
		'wp_phpbb_bridge_comments_avatar_width' => array(
			'name' => 'wp_phpbb_bridge_comments_avatar_width',
			'title' => __( 'Comments avatars width: ', 'propress' ),
			'type' => 'text',
			'sanitize' => 'absint',
			'description' => __( 'The width size of avatars in comments, in pixels.', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => '32'
		),
		'usesth' => array(
			'name' => 'usesth',
			'title' => __( 'Full Path to ucp.php', 'propress' ),
			'type' => 'text',
			'sanitize' => 'url',
			'description' => __( '', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => 'http://localhost/forums/ucp.php'
		),
		'wp_phpbb_bridge_blog_founder_user_id' => array(
			'name' => 'wp_phpbb_bridge_blog_founder_user_id',
			'title' => __( 'Wordpress founder ID:', 'propress' ),
			'type' => 'text',
			'sanitize' => 'absint',
			'description' => __( '', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => '1'
		),
		'wp_phpbb_bridge_forum_founder_user_id' => array(
			'name' => 'wp_phpbb_bridge_forum_founder_user_id',
			'title' => __( 'Forum founder ID:', 'propress' ),
			'type' => 'text',
			'sanitize' => 'absint',
			'description' => __( '', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => '2'
		),
		'wp_phpbb_bridge_post_forum_id' => array(
			'name' => 'wp_phpbb_bridge_post_forum_id',
			'title' => __( 'Post forum ID:', 'propress' ),
			'type' => 'text',
			'sanitize' => 'absint',
			'description' => __( '', 'propress' ),
			'section' => 'header',
			'tab' => 'general',
			'since' => '1.2',
			'default' => '1'
		),

    );
    return apply_filters( 'propress_get_option_parameters', $options );
}

/**
 * Get propressTheme Options
 * 
 * Array that holds all of the defined values
 * for propressTheme options. If the user 
 * has not specified a value for a given Theme 
 * option, then the option's default value is
 * used instead.
 *
 * @uses	propress_get_option_defaults()	defined in \functions\options.php
 * 
 * @uses	get_option()
 * @uses	wp_parse_args()
 * 
 * @return	array	$propress_options	current values for all Theme options
 */
function propress_get_options() {
	// Get the option defaults
	$option_defaults = propress_get_option_defaults();
	// Globalize the variable that holds the Theme options
	global $propress_options;
	// Parse the stored options with the defaults
	$propress_options = wp_parse_args( get_option( 'theme_propress_options', array() ), $option_defaults );
	// Return the parsed array
	return $propress_options;
}

/**
 * Separate settings by tab
 * 
 * Returns an array of tabs, each of
 * which is an indexed array of settings
 * included with the specified tab.
 *
 * @uses	propress_get_option_parameters()	defined in \functions\options.php
 * @uses	propress_get_settings_page_tabs()	defined in \functions\options.php
 * 
 * @return	array	$settingsbytab	array of arrays of settings by tab
 */
function propress_get_settings_by_tab() {
	// Get the list of settings page tabs
	$tabs = propress_get_settings_page_tabs();
	// Initialize an array to hold
	// an indexed array of tabnames
	$settingsbytab = array();
	// Loop through the array of tabs
	foreach ( $tabs as $tab ) {
		$tabname = $tab['name'];
		// Add an indexed array key
		// to the settings-by-tab 
		// array for each tab name
		$settingsbytab[] = $tabname;
	}
	// Get the array of option parameters
	$option_parameters = propress_get_option_parameters();
	// Loop through the option parameters
	// array
	foreach ( $option_parameters as $option_parameter ) {
		$optiontab = $option_parameter['tab'];
		$optionname = $option_parameter['name'];
		// Add an indexed array key to the 
		// settings-by-tab array for each
		// setting associated with each tab
		$settingsbytab[$optiontab][] = $optionname;
		$settingsbytab['all'][] = $optionname;
	}
	// Return the settings-by-tab
	// array
	return $settingsbytab;
}
 
/**
 * propressTheme Admin Settings Page Tabs
 * 
 * Array that holds all of the tabs for the
 * propressTheme Settings Page. Each tab
 * key holds an array that defines the 
 * sections for each tab, including the
 * description text.
 * 
 * @uses	propress_get_varietal_text()	defined in \functions\options-register.php
 * 
 * @return	array	$tabs	array of arrays of tab parameters
 */
function propress_get_settings_page_tabs() {
	
	$tabs = array( 
        'general' => array(
			'name' => 'general',
			'title' => __( 'General', 'propress' ),
			'sections' => array(
				'header' => array(
					'name' => 'header',
					'title' => __( 'Gemeral  Options', 'propress' ),
					'description' => __( 'Manage bridge settings', 'propress' )
				),
			)
		),
    );
	return apply_filters( 'propress_get_settings_page_tabs', $tabs );
}

function propress_get_settings_page_cap() {
	return 'edit_theme_options';
}
// Hook into option_page_capability_{option_page}
add_action( 'option_page_capability_propress-settings', 'propress_get_settings_page_cap' );

?>
<?php
/**
 * Theme Options Settings API
 *
 * This file implements the WordPress Settings API for the 
 * Options for the Theme.
 * 
 * @package 	propress* @copyright	Copyright (c) 2011, Chip Bennett
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 (or newer)
 *
 * @since 		propress1.0
 */

/**
 * Register Theme Settings
 * 
 * Register theme_propress_options array to hold
 * all Theme options.
 * 
 * @link	http://codex.wordpress.org/Function_Reference/register_setting	Codex Reference: register_setting()
 * 
 * @param	string		$option_group		Unique Settings API identifier; passed to settings_fields() call
 * @param	string		$option_name		Name of the wp_options database table entry
 * @param	callback	$sanitize_callback	Name of the callback function in which user input data are sanitized
 */
register_setting( 
	// $option_group
	'theme_propress_options', 
	// $option_name
	'theme_propress_options', 
	// $sanitize_callback
	'propress_options_validate' 
);


/**
 * Theme register_setting() sanitize callback
 * 
 * Validate and whitelist user-input data before updating Theme 
 * Options in the database. Only whitelisted options are passed
 * back to the database, and user-input data for all whitelisted
 * options are sanitized.
 * 
 * @link	http://codex.wordpress.org/Data_Validation	Codex Reference: Data Validation
 * 
 * @param	array	$input	Raw user-input data submitted via the Theme Settings page
 * @return	array	$input	Sanitized user-input data passed to the database
 */
function propress_options_validate( $input ) {
	// This is the "whitelist": current settings
	$valid_input = propress_get_options();
	// Get the array of Theme settings, by Settings Page tab
	$settingsbytab = propress_get_settings_by_tab();
	// Get the array of option parameters
	$option_parameters = propress_get_option_parameters();
	// Get the array of option defaults
	$option_defaults = propress_get_option_defaults();
	// Get list of tabs
	$tabs = propress_get_settings_page_tabs();
	
	// Determine what type of submit was input
	$submittype = 'submit';	
	foreach ( $tabs as $tab ) {
		$resetname = 'reset-' . $tab['name'];
		if ( ! empty( $input[$resetname] ) ) {
			$submittype = 'reset';
		}
	}
	
	// Determine what tab was input
	$submittab = 'general';	
	foreach ( $tabs as $tab ) {
		$submitname = 'submit-' . $tab['name'];
		$resetname = 'reset-' . $tab['name'];
		if ( ! empty( $input[$submitname] ) || ! empty($input[$resetname] ) ) {
			$submittab = $tab['name'];
		}
	}
	global $wp_customize;
	// Get settings by tab
	$tabsettings = ( isset ( $wp_customize ) ? $settingsbytab['all'] : $settingsbytab[$submittab] );
	// Loop through each tab setting
	foreach ( $tabsettings as $setting ) {
		// If no option is selected, set the default
		$valid_input[$setting] = ( ! isset( $input[$setting] ) ? $option_defaults[$setting] : $input[$setting] );
		
		// If submit, validate/sanitize $input
		if ( 'submit' == $submittype ) {
		
			// Get the setting details from the defaults array
			$optiondetails = $option_parameters[$setting];
			// Get the array of valid options, if applicable
			$valid_options = ( isset( $optiondetails['valid_options'] ) ? $optiondetails['valid_options'] : false );
			
			// Validate checkbox fields
			if ( 'checkbox' == $optiondetails['type'] ) {
				// If input value is set and is true, return true; otherwise return false
				$valid_input[$setting] = ( ( isset( $input[$setting] ) && true == $input[$setting] ) ? true : false );
			}
			// Validate radio button fields
			else if ( 'radio' == $optiondetails['type'] ) {
				// Only update setting if input value is in the list of valid options
				$valid_input[$setting] = ( array_key_exists( $input[$setting], $valid_options ) ? $input[$setting] : $valid_input[$setting] );
			}
			// Validate select fields
			else if ( 'select' == $optiondetails['type'] ) {
				// Only update setting if input value is in the list of valid options
				$valid_input[$setting] = ( array_key_exists( $input[$setting], $valid_options ) ? $input[$setting] : $valid_input[$setting] );
			}
			// Validate text input and textarea fields
			else if ( ( 'text' == $optiondetails['type'] || 'textarea' == $optiondetails['type'] ) ) {
				// Validate no-HTML content
				if ( 'nohtml' == $optiondetails['sanitize'] ) {
					// Pass input data through the wp_filter_nohtml_kses filter
					$valid_input[$setting] = wp_filter_nohtml_kses( $input[$setting] );
				}
				// Validate HTML content
				if ( 'html' == $optiondetails['sanitize'] ) {
					// Pass input data through the wp_filter_kses filter
					$valid_input[$setting] = wp_filter_kses( $input[$setting] );
				}
				if ( 'url' == $optiondetails['sanitize'] ) {
					// Pass input data through the esc_url filter
					$valid_input[$setting] = esc_url( $input[$setting], $protocols = 'http');
				}
				if ( 'absint' == $optiondetails['sanitize'] ) {
					// Pass input data through the absint filter
					$valid_input[$setting] = absint( $input[$setting] );
				}
			}
			// Validate custom fields
		} 
		// If reset, reset defaults
		elseif ( 'reset' == $submittype ) {
			// Set $setting to the default value
			$valid_input[$setting] = $option_defaults[$setting];
		}
	}
	return $valid_input;		

}

/**
 * Globalize the variable that holds 
 * the Settings Page tab definitions
 * 
 * @global	array	Settings Page Tab definitions
 */
global $propress_tabs;
$propress_tabs = propress_get_settings_page_tabs();
/**
 * Call add_settings_section() for each Settings 
 * 
 * Loop through each Theme Settings page tab, and add 
 * a new section to the Theme Settings page for each 
 * section specified for each tab.
 * 
 * @link	http://codex.wordpress.org/Function_Reference/add_settings_section	Codex Reference: add_settings_section()
 * 
 * @param	string		$sectionid	Unique Settings API identifier; passed to add_settings_field() call
 * @param	string		$title		Title of the Settings page section
 * @param	callback	$callback	Name of the callback function in which section text is output
 * @param	string		$pageid		Name of the Settings page to which to add the section; passed to do_settings_sections()
 */
foreach ( $propress_tabs as $tab ) {
	$tabname = $tab['name'];
	$tabsections = $tab['sections'];
	foreach ( $tabsections as $section ) {
		$sectionname = $section['name'];
		$sectiontitle = $section['title'];
		// Add settings section
		add_settings_section(
			// $sectionid
			'propress_' . $sectionname . '_section',
			// $title
			$sectiontitle,
			// $callback
			'propress_sections_callback',
			// $pageid
			'propress_' . $tabname . '_tab'
		);
	}
}

/**
 * Callback for add_settings_section()
 * 
 * Generic callback to output the section text
 * for each Plugin settings section. 
 * 
 * @uses	propress_get_settings_page_tabs()	Defined in /functions/options.php
 * 
 * @param	array	$section_passed	Array passed from add_settings_section()
 */
function propress_sections_callback( $section_passed ) {
	global $propress_tabs;
	$propress_tabs = propress_get_settings_page_tabs();
	foreach ( $propress_tabs as $tabname => $tab ) {
		$tabsections = $tab['sections'];
		foreach ( $tabsections as $sectionname => $section ) {
			if ( 'propress_' . $sectionname . '_section' == $section_passed['id'] ) {
				?>
				<p><?php echo $section['description']; ?></p>
				<?php
			}
		}
	}
}

/**
 * Globalize the variable that holds 
 * all the Theme option parameters
 * 
 * @global	array	Theme options parameters
 */
global $option_parameters;
$option_parameters = propress_get_option_parameters();
/**
 * Call add_settings_field() for each Setting Field
 * 
 * Loop through each Theme option, and add a new 
 * setting field to the Theme Settings page for each 
 * setting.
 * 
 * @link	http://codex.wordpress.org/Function_Reference/add_settings_field	Codex Reference: add_settings_field()
 * 
 * @param	string		$settingid	Unique Settings API identifier; passed to the callback function
 * @param	string		$title		Title of the setting field
 * @param	callback	$callback	Name of the callback function in which setting field markup is output
 * @param	string		$pageid		Name of the Settings page to which to add the setting field; passed from add_settings_section()
 * @param	string		$sectionid	ID of the Settings page section to which to add the setting field; passed from add_settings_section()
 * @param	array		$args		Array of arguments to pass to the callback function
 */
foreach ( $option_parameters as $option ) {
	$optionname = $option['name'];
	$optiontitle = $option['title'];
	$optiontab = $option['tab'];
	$optionsection = $option['section'];
	$optiontype = $option['type'];
	if ( 'custom' != $optiontype ) {
		add_settings_field(
			// $settingid
			'propress_setting_' . $optionname,
			// $title
			$optiontitle,
			// $callback
			'propress_setting_callback',
			// $pageid
			'propress_' . $optiontab . '_tab',
			// $sectionid
			'propress_' . $optionsection . '_section',
			// $args
			$option
		);
	} if ( 'custom' == $optiontype ) {
		add_settings_field(
			// $settingid
			'propress_setting_' . $optionname,
			// $title
			$optiontitle,
			//$callback
			'propress_setting_' . $optionname,
			// $pageid
			'propress_' . $optiontab . '_tab',
			// $sectionid
			'propress_' . $optionsection . '_section'
		);
	}
}

/**
 * Callback for get_settings_field()
 */
function propress_setting_callback( $option ) {
	$propress_options = propress_get_options();
	$option_parameters = propress_get_option_parameters();
	$optionname = $option['name'];
	$optiontitle = $option['title'];
	$optiondescription = $option['description'];
	$fieldtype = $option['type'];
	$fieldname = 'theme_propress_options[' . $optionname . ']';
	
	// Output checkbox form field markup
	if ( 'checkbox' == $fieldtype ) {
		?>
		<input type="checkbox" name="<?php echo $fieldname; ?>" <?php checked( $propress_options[$optionname] ); ?> />
		<?php
	}
	// Output radio button form field markup
	else if ( 'radio' == $fieldtype ) {
		$valid_options = array();
		$valid_options = $option['valid_options'];
		foreach ( $valid_options as $valid_option ) {
			?>
			<input type="radio" name="<?php echo $fieldname; ?>" <?php checked( $valid_option['name'] == $propress_options[$optionname] ); ?> value="<?php echo $valid_option['name']; ?>" />
			<span>
			<?php echo $valid_option['title']; ?>
			<?php if ( $valid_option['description'] ) { ?>
				<span style="padding-left:5px;"><em><?php echo $valid_option['description']; ?></em></span>
			<?php } ?>
			</span>
			<br />
			<?php
		}
	}
	// Output select form field markup
	else if ( 'select' == $fieldtype ) {
		$valid_options = array();
		$valid_options = $option['valid_options'];
		?>
		<select name="<?php echo $fieldname; ?>">
		<?php 
		foreach ( $valid_options as $valid_option ) {
			?>
			<option <?php selected( $valid_option['name'] == $propress_options[$optionname] ); ?> value="<?php echo $valid_option['name']; ?>"><?php echo $valid_option['title']; ?></option>
			<?php
		}
		?>
		</select>
		<?php
	} 
	// Output text input form field markup
	else if ( 'text' == $fieldtype ) {
		?>
		<input type="text" name="<?php echo $fieldname; ?>" value="<?php echo wp_filter_nohtml_kses( $propress_options[$optionname] ); ?>" />
		<?php
	} 
	// Output the setting description
	?>
	<span class="description"><?php echo $optiondescription; ?></span>
	<?php
}
?>
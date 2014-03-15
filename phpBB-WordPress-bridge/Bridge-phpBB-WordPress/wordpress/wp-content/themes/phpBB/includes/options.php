<?php
/**
 * Theme Options
 *
 */

/**
 * Globalize the variable that holds the Theme Options
 * 
 * @global	array	$propress_options	holds Theme options
 */
global $propress_options;

/**
 * Setup the Theme Admin Settings Page
 * 
 * Add "options" link to the "Appearance" menu

 */
function propress_add_theme_page() {
	// Globalize Theme options page
	global $propress_settings_page;
	// Add Theme options page
	$propress_settings_page = add_theme_page(
		// $page_title
		// Name displayed in HTML title tag
		__( 'phpBB to WP connector configuration', 'wp_phpbb3_bridge' ),
		// $menu_title
		// Name displayed in the Admin Menu
		__( 'phpBB to WP connector configuration', 'wp_phpbb3_bridge' ),
		// $capability
		// User capability required to access page
		'manage_options', 
		// $menu_slug
		// String to append to URL after "themes.php"
		'wp-united-setup', 
		// $callback
		// Function to define settings page markup
		'wpu_setup_menu'
	);
}
// Load the Admin Options page
add_action( 'admin_menu', 'propress_add_theme_page' );

function wpu_setup_menu() {
	global $wpUnited, $phpbbForum;

	?>
	<div class="wrap" id="wp-united-setup">
		<?php screen_icon('options-general'); ?>
		<h2> <?php _e('phpBB to WP connector Setup / Status', 'wp-united'); ?> </h2>
		<p><?php _e('phpBB to WP connector needs to connect to phpBB in order to work. On this screen you can set up or disable the connection.', 'wp-united') ?></p>

		<div id="wputransmit"><p><strong><?php _e('Communicating with phpBB...', 'wp-united'); ?></strong><br /><?php _e('Please Wait...'); ?></p><img src="<?php echo $wpUnited->get_plugin_url() ?>images/settings/wpuldg.gif" /></div>

		<?php

		$needPreview = false;
		$msg = '';
		if(isset($_GET['msg'])) {
			if($_GET['msg'] == 'fail') {
				$msg = html_entity_decode(base64_decode(stripslashes_deep((string)$_POST['msgerr'])));
			} else {
				// $msg is succcess, do preview reloads to init Template Voodoo:
				$needPreview = true;
			}
		}

		$buttonDisplay = 'display: block;';


		if(!$wpUnited->is_enabled() && ($wpUnited->get_last_run() == 'working')) {
			$statusText = __('Disabled', 'wp-united');
			$statusColour = "error";
			$statusDesc = __('WP-United is disabled. Select your forum location below and then click &quot;Connect&quot;', 'wp-united');
			$buttonDisplay = 'display: block;';
			$needPreview = false;
		} else {
			switch($wpUnited->get_last_run()) {
				case 'connected':
					$statusText = __('OK', 'wp-united');
					$statusColour = "updated allok";
					$statusDesc =  __('phpBB to WP connector is connected and working.', 'wp-united');
					$buttonDisplay = 'display: none;';
					break;
				default:
					$statusText = __('Not Connected', 'wp-united');
					$statusColour = "error";
					$statusDesc = __('phpBB to WP connector is not connected yet. Select your forum location below and then click &quot;Connect&quot;', 'wp-united') . '<br /><br />' . __('You can\'t change any other settings until WP-United is connected.', 'wp-united');
					$buttonDisplay = (!$wpUnited->is_enabled()) ? 'display: block;' : 'display: none;';
					$needPreview = false;
			}
		}

		wpu_panel_warnings();

		echo "<div id=\"wpustatus\" class=\"$statusColour\"><p><strong>" . sprintf(__('Current Status: %s', 'wp-united'), $statusText) . '</strong>';
		if($wpUnited->get_last_run() == 'working' && $wpUnited->is_enabled()) {
			echo '<button style="float: right;margin-bottom: 6px;" class="button-secondary" onclick="return wpu_manual_disable(\'wp-united-setup\');">' . __('Disable', 'wp-united') . '</button>';
		}
		echo "<br /><br />$statusDesc";
		if(!empty($msg)) {
			echo '<br /><br /><strong>' . __('The server returned the following information:', 'wp-united') . "</strong><br />$msg";
		}
		echo '</p></div>';


		?>
		<h3><?php _e('phpBB Location', 'wp-united') ?></h3>
		<form name="wpu-setup" id="wpusetup" method="post" onsubmit="return wpu_transmit('wp-united-setup', this.id);">
			<?php wp_nonce_field('wp-united-setup');  ?>

			<p><?php _e('phpBB to WP connector needs to know where phpBB is installed on your server.', 'wp-united'); ?> <span id="txtselpath"><?php _e("Find and select your phpBB's config.php below.", 'wp-united'); ?></span><span id="txtchangepath" style="display: none;"><?php _e('Click &quot;Change Location&quot; to change the stored location.', 'wp-united'); ?></span></p>

			<?php

			$docRoot = wpu_get_doc_root();
			$propress_options = get_option( 'wpu-settings' );
			$phpbbPath = $propress_options['phpbb_path'];

			if($phpbbPath) {
				$showBackupPath = str_replace($docRoot, '', $phpbbPath);
				$docRootParts = explode('/', $docRoot);
				while($showBackupPath == $phpbbPath) {
					array_pop($docRoot);
					$showBackupPath = str_replace(add_trailing_slash(implode('/', $docRoot)), '', $phpbbPath);
				}
			}
			?>
			<div id="phpbbpathgroup">
				<div id="phpbbpath" style="display: none;">&nbsp;</div>
				<p id="wpubackupgroup" style="display: none;"><strong><input id="phpbbdocroot" name="phpbbdocroot" value="<?php echo $docRoot; ?>"></input><input type="text" id="wpubackupentry" name="wpubackupentry" value="<?php echo $showBackupPath; ?>"></span></input>/config.php</strong></p>
				<small><a href="#" onclick="return wpuSwitchEntryType();" id="wpuentrytype"><?php _e('I want to type the path manually', 'wp-united'); ?></a></small>
			</div>
			<p><?php _e('Path selected: ', 'wp-united'); ?><strong id="phpbbpathshow" style="color: red;"><?php _e('Not selected', 'wp-united'); ?></strong> <a id="phpbbpathchooser" href="#" onclick="return wpuChangePath();" style="display: none;"><?php _e('Change Location &raquo;', 'wp-united'); ?></a><a id="wpucancelchange" style="display: none;" href="#" onclick="return wpuCancelChange();"><?php _e('Cancel Change', 'wp-united'); ?></a></p>
			<input id="wpupathfield" type="hidden" name="wpu-path" value="notset"></input>

			<p class="submit">
				<input type="submit" style="<?php echo $buttonDisplay; ?>"; class="button-primary" value="<?php  _e('Connect', 'wp-united') ?>" name="wpusetup-submit" id="wpusetup-submit" />
			</p>
		</form>
	</div>
	<!-- off-screen measure for dynamic text box -->
	<strong id="wpu-measure" style="display: block; font-size: 11px;position: absolute;left: -10000px;top: 0px;"></strong>


	<script type="text/javascript">
		// <![CDATA[
		var transmitMessage;
		var filetreeNonce = '<?php echo wp_create_nonce ('wp-united-filetree'); ?>';
		var transmitNonce = '<?php echo wp_create_nonce ('wp-united-transmit'); ?>';
		var disableNonce = '<?php echo wp_create_nonce ('wp-united-disable'); ?>';
		var blankPageMsg = '<?php wpu_js_translate(__('Blank page received: check your error log.', 'wp-united')); ?>';
		var phpbbPath = '<?php
		 $propress_options = get_option( 'wpu-settings' );
		 echo ($propress_options['phpbb_path']) ? $propress_options['phpbb_path'] : ''; ?>';
		var fileTreeLdgText = '<?php wpu_js_translate(__('Loading...', 'wp-united')); ?>';
		var connectingText = '<?php wpu_js_translate(__('Connecting...', 'wp-united')); ?>';
		var manualText = '<?php wpu_js_translate(__('I want to type the path manually', 'wp-united')); ?>';
		var autoText = '<?php wpu_js_translate(__('Show me the file chooser', 'wp-united')); ?>';


		function wpu_hardened_init_tail() {
			createFileTree();
			<?php
			 $propress_options = get_option( 'wpu-settings' );
			 if($propress_options['phpbb_path']) { ?>
			setPath('setup');
			<?php } ?>
		}
		// ]]>
	</script>
	<?php
	add_action('admin_footer', 'wpu_hardened_script_init');
}



add_action('admin_menu', 'wpu_settings_menu');

function wpu_settings_menu() {
	global $wpUnited, $phpbbForum;

	if (!current_user_can('manage_options'))  {
		return;
	}

	if (!function_exists('add_submenu_page')) {
		return;
	}


	wp_enqueue_style('wpuSettingsStyles', get_template_directory_uri() . '/theme/settings.css');

	if(isset($_GET['page'])) {
		if(in_array($_GET['page'], array('wp-united-settings', 'wp-united-setup', 'wpu-user-mapper'))) {

			wp_enqueue_script('filetree', get_template_directory_uri() . '/js/filetree.js', array('jquery'), false, false);
			;

			wp_enqueue_script(
				'wpu-settings',
				get_template_directory_uri() . '/js/settings.js',
				array(
					'filetree',
					'jquery-ui-widget',
					'jquery-ui-tabs',
					'jquery-ui-button',
					'jquery-ui-slider',
					'jquery-ui-dialog',
					'jquery-ui-autocomplete',
					'jquery-effects-core',
					'jquery-effects-slide',
					'jquery-effects-highlight'
				),
				$wpUnited->get_version(),
				false
			);

		}
		if(in_array($_GET['page'], array('wp-united-settings', 'wp-united-setup'))) {

			wp_enqueue_style('wpuSettingsStyles', get_template_directory_uri() . 'theme/settings.css');
		}
	}
}





function wpu_panel_warnings() {
	global $wpUnited, $phpbbForum, $wpuAdminIsOrphaned;
}


/**
 * Process settings
 */
function wpu_process_settings() {
	global $wpUnited, $wpdb;

	$type = 'setup';
	if(isset($_POST['type'])) {
		if($_POST['type'] == 'wp-united-settings') {
			$type = 'settings';
		}
	}

	$data = array();

	/**
	 * First process path to phpBB
	 */
	if(!isset($_POST['wpu-path'])) {
		die('[ERROR] ' . __("ERROR: You must specify a valid path for phpBB's config.php", 'wp-united'));
	}
	$wpuPhpbbPath = (string)$_POST['wpu-path'];
	$wpuPhpbbPath = str_replace('http:', '', $wpuPhpbbPath);
	$wpuPhpbbPath = add_trailing_slash($wpuPhpbbPath);
	if(!@file_exists($wpUnited->get_plugin_path()))  {
		die('[ERROR] ' . __("ERROR:The path you selected for phpBB's config.php is not valid", 'wp-united'));
		return;
	}
	if(!@file_exists($wpuPhpbbPath . 'config.php'))  {
		die('[ERROR] ' . __("ERROR: phpBB's config.php could not be found at the location you chose", 'wp-united'));
		return;
	}
	if($type=='setup') {
		$data['phpbb_path'] = $wpuPhpbbPath;
	}

	$wpUnited->update_settings($data);


	$wpUnited->update_settings($data);
}


function wpu_panel_error($type, $text) {

	echo '<div id="message" class="error"><p>' . $text . '</p></div>';
	if($type=='settings') {
		wpu_settings_page();
	} else {
		wpu_show_setup_menu();
	}

}


function wpu_filetree() {
	if(stristr($_POST['filetree'], '..')) {
		die();
	}


	$docRoot = wpu_get_doc_root();

	$fileLoc = str_replace( '\\', '/', urldecode($_POST['filetree']));

	if(stristr($fileLoc, $docRoot) === false) {
		$fileLoc = $docRoot . $fileLoc;
		$fileLoc = str_replace('//', '/', $fileLoc);
	}

	if( @file_exists($fileLoc) ) {
		$files = scandir($fileLoc);
		natcasesort($files);
		if( count($files) > 2 ) { /* The 2 accounts for . and .. */
			echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
			// All dirs
			foreach( $files as $file ) {
				if( @file_exists($fileLoc. $file) && $file != '.' && $file != '..' && is_dir($fileLoc . $file) ) {
					echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($fileLoc . $file) . "/\">" . htmlentities($file) . "</a></li>";
				}
			}
			// All files
			foreach( $files as $file ) {
				if( @file_exists($fileLoc . $file) && $file != '.' && $file != '..' && !is_dir($fileLoc . $file) ) {
					$ext = preg_replace('/^.*\./', '', $file);
					echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($fileLoc . $file) . "\">" . htmlentities($file) . "</a></li>";
				}
			}
			echo "</ul>";
		}
	}
	die();

}

/*
	A way to initialise scripts that still works EVEN WHEN OTHER (grrrr) PLUGINS have script errors
*/
function wpu_hardened_script_init() {
	static $calledInit = false;

	if(!$calledInit) {
		$calledInit = true;
	}

	?>
	<script type="text/javascript">// <![CDATA[
		$wpu(document).ready(function() {
			wpu_hardened_init();
		});
		setTimeout('wpu_hardened_init()', 1000);
		// ]]>
	</script>
<?php
}



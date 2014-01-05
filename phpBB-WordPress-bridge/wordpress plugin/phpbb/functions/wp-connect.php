<?php

/**
* @ignore
**/

// add the admin options page
add_action('admin_menu', 'wp_phpbb3_bridge_admin_add_page');

function wp_phpbb3_bridge_admin_add_page()
{
	add_menu_page(
		'BRIDGE phpBB & WordPress Plugin Menu',
		'BRIDGE phpBB & WordPress Plugin Page',
		'manage_options',
		'wp_phpbb3_bridge_options',
		'wp_phpbb3_bridge_options_page'
	);
}

function wp_phpbb3_bridge_options_page()
{
	// must check that the user has the required capability
	if (!current_user_can('manage_options'))
	{
		wp_die(__('You do not have sufficient permissions to access this page.', 'wp_phpbb3_bridge_options'));
	}

	// Some default options
	$submit	= (isset($_POST['submit'])) ? true : false;

	$phpbb_script_path						= get_option('phpbb_script_path', $wp_phpbb_bridge_config['phpbb_script_path']);


	$_phpbb_script_path = wp_phpbb3_bridge_check_path('phpbb_script_path', $phpbb_script_path, 'config.php', true);
	if (!$_phpbb_script_path)
	{
		$submit = false;
	?>	<div id="message" class="error fade" style="padding: .5em; background-color: #BC2A4D; color: #FFFFFF; font-weight: bold;">
			<p> <?php printf(__('Could not find "Server root path to phpBB". Please check your settings and try again.<br /><samp>%s</samp> was specified as the source path.<br /><br />Cannot activate bridge.', 'wp_phpbb3_bridge_options'), $phpbb_script_path); ?> </p>
		</div>	<?php
	}
	else
	{
		$phpbb_script_path = $_phpbb_script_path;
	}

	if ($submit)
	{
		update_option('phpbb_script_path', $phpbb_script_path);
	?>
		<div id="message" class="updated fade" style="padding: .5em; background-color: #228822; color: #FFFFFF; font-weight: bold;">
			<p> <?php _e('Options saved.', 'wp_phpbb3_bridge_options'); ?> </p>
		</div>
	<?php
	}
?>
	<div class="wrap">
		<form method="post" action="">
			<h2><img class="icon16" src="<?php echo esc_url( admin_url('images/generic.png')); ?>" /> BRIDGE phpBB & WordPress</h2>
			<table class="form-table">
				<tr>
					<th>
						<label for="wp_phpbb_bridge"> <?php _e('Enable Bridge:', 'wp_phpbb3_bridge_options'); ?></label>
					</th>
					<td>
						<input type="radio" name="wp_phpbb_bridge" value="1" <?php echo (($active) ? 'id="wp_phpbb_bridge" checked="checked" ' : '') ?> /> <?php _e('Yes', 'wp_phpbb3_bridge_options'); ?>
						&nbsp;
						<input type="radio" name="wp_phpbb_bridge" value="0" <?php echo ((!$active) ? 'id="wp_phpbb_bridge" checked="checked" ' : '') ?> /> <?php _e('No', 'wp_phpbb3_bridge_options'); ?>
						<br />
						<span class="description"><?php _e('This will make the Bridge unavailable to use.', 'wp_phpbb3_bridge_options'); ?></span>
					</td>
				</tr>

				<tr>
					<th>
						<label for="phpbb_root_path"> <?php _e('Path to phpBB:', 'wp_phpbb3_bridge_options'); ?> (*)</label>
					</th>
					<td>
						<input type="text" name="phpbb_root_path" id="phpbb_root_path" style="width: 95%" value="<?php echo $phpbb_root_path; ?>" />
						<br />
						<span class="description"><?php _e('The path where phpBB is located <strong>relative</strong> to the domain name.', 'wp_phpbb3_bridge_options'); ?></span>
						<br />
						<?php _e('<b>Example :</b> <code>../phpBB/</code>&nbsp;<b>The Blog is at:</b> <code>http://www.mydomain.tld/wordpress/</code><b>The Forum is at:</b> <code>http://www.example.com/phpBB/</code>', 'wp_phpbb3_bridge_options'); ?>
					</td>
				</tr>

				<tr>
					<th>
						<label for="phpbb_script_path"> <?php _e('Server root path to phpBB:', 'wp_phpbb3_bridge_options'); ?> (*)</label>
					</th>
					<td>
						<input type="text" name="phpbb_script_path" id="phpbb_script_path" style="width: 95%" value="<?php echo $phpbb_script_path; ?>" />
						<br />&nbsp;
						<span class="description"><?php _e('Relative path from the server root.', 'wp_phpbb3_bridge_options'); ?></span>
						<br />
						<?php _e('<b>Example :</b> <code>phpBB/</code>&nbsp;<b>The Blog is at:</b> <code>http://www.mydomain.tld/wordpress/</code><b>The Forum is at:</b> <code>http://www.example.com/phpBB/</code>', 'wp_phpbb3_bridge_options'); ?>
					</td>
				</tr>

				<tr>
					<th>
						<label for="wordpress_script_path"> <?php _e('Server root path to WordPress:', 'wp_phpbb3_bridge_options'); ?> (*)</label>
					</th>
					<td>
						<input type="text" name="wordpress_script_path" id="wordpress_script_path" style="width: 95%" value="<?php echo $wordpress_script_path; ?>" />
						<br />
						<span class="description"><?php _e('Relative path from the server root.', 'wp_phpbb3_bridge_options'); ?></span>
						<br />
						<?php _e('<b>Example :</b> <code>wordpress/</code>&nbsp;<b>The Blog is at:</b> <code>http://www.mydomain.tld/wordpress/</code><b>The Forum is at:</b> <code>http://www.example.com/phpBB/</code>', 'wp_phpbb3_bridge_options'); ?>
					</td>
				</tr>

				<tr>
					<th>
						<label for="wp_phpbb_bridge_permissions_forum_id"> <?php _e('Permissions forum ID:', 'wp_phpbb3_bridge_options'); ?></label>
					</th>
					<td>
						<input type="text" name="wp_phpbb_bridge_permissions_forum_id" id="wp_phpbb_bridge_permissions_forum_id" value="<?php echo $wp_phpbb_bridge_permissions_forum_id; ?>" />
						<br />
						<span class="description"><?php _e('The number of your Forum (not Category) where to use permissions.', 'wp_phpbb3_bridge_options'); ?></span>
					</td>
				</tr>

				<tr>
					<th>
						<label for="wp_phpbb_bridge_post_forum_id"> <?php _e('Post forum ID:', 'wp_phpbb3_bridge_options'); ?></label>
					</th>
					<td>
						<input type="text" name="wp_phpbb_bridge_post_forum_id" id="wp_phpbb_bridge_post_forum_id" value="<?php echo $wp_phpbb_bridge_post_forum_id; ?>" />
						<br />
						<span class="description"><?php _e('The number of you forum where to post a new entry whenever is published in the Wordpress.', 'wp_phpbb3_bridge_options'); ?></span>
					</td>
				</tr>

				<tr>
					<th>
						<label for="wp_phpbb_bridge_widgets_column_width"> <?php _e('Widgets column width:', 'wp_phpbb3_bridge_options'); ?></label>
					</th>
					<td>
						<input type="text" name="wp_phpbb_bridge_widgets_column_width" id="wp_phpbb_bridge_widgets_column_width" value="<?php echo $wp_phpbb_bridge_widgets_column_width; ?>" />
						<br />
						<span class="description"><?php _e('The right column width, in pixels.', 'wp_phpbb3_bridge_options'); ?></span>
					</td>
				</tr>

				<tr>
					<th>
						<label for="wp_phpbb_bridge_comments_avatar_width"> <?php _e('Comments avatars width:', 'wp_phpbb3_bridge_options'); ?></label>
					</th>
					<td>
						<input type="text" name="wp_phpbb_bridge_comments_avatar_width" id="wp_phpbb_bridge_comments_avatar_width" value="<?php echo $wp_phpbb_bridge_comments_avatar_width; ?>" />
						<br />
						<span class="description"><?php _e('The width size of avatars in comments, in pixels.', 'wp_phpbb3_bridge_options'); ?></span>
					</td>
				</tr>

			</table>
			<?php submit_button(null, 'primary', 'submit');  ?>
		</form>
	</div>
	<div class="wrap">(*)
		<span class="description">
			If you are not sure what is the full path to your phpBB3 then create a file "ie: mypath.php" into the folder phpBB3 and enter the following code in it:
			 <code>echo $_SERVER['SCRIPT_FILENAME'];</code> You will get someting like this :<br /><code><?php echo $_SERVER['SCRIPT_FILENAME']; ?></code>
			 <br />
			 The above code will return to you the full path to phpBB3 + the file name "mypath.php".
			 <br />
			 Place the "mypath.php" with the "config.php" and then copy all that path to use it in the plugin configuration.
			 <br />
			 Hope this help you :)
		</span>
	</div>

<?php
}

function wp_phpbb3_bridge_check_path($var = '', $default = '', $file = '', $server_root = false)
{
	// We have to generate a full HTTP/1.1 header here since we can't guarantee to have any of the information
	// available as used by the redirect function
	$server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));
	$server_port = (!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');
	$secure 	 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;
	$script_path = (isset($_POST[$var]) && $_POST[$var]) ? trim($_POST[$var]) : $default;

	// Replace any number of consecutive backslashes and/or slashes with a single slash
	// (could happen on some proxy setups and/or Windows servers)
	$script_path = preg_replace('#[\\\\/]{2,}#', '/', $script_path);

	$url = (($secure) ? 'https://' : 'http://') . $server_name;

	if ($server_port && (($secure && $server_port <> 443) || (!$secure && $server_port <> 80)))
	{
		// HTTP HOST can carry a port number...
		if (strpos($server_name, ':') === false)
		{
			$url .= ':' . $server_port;
		}
	}

	// Add closing / if not present
	$script_path = ($script_path && substr($script_path, -1) != '/') ? $script_path . '/' : $script_path;

	$path = (($server_root) ? $_SERVER['DOCUMENT_ROOT'] . '/' : '../') . $script_path . $file;

	if (!file_exists($path))
	{
		return false;
	}

	return $script_path;
}

?>
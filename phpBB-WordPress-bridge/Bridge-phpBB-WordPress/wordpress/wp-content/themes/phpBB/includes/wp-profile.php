<?php

function wp_phpbb_save_extra_profile_fields($user_id)
{
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	$phpbb_user_id = (isset($_POST['phpbb_userid']) && $_POST['phpbb_userid'] != 0) ? $_POST['phpbb_userid'] : 0;
	if ($phpbb_user_id == 0) {
		delete_user_meta($user_id, 'phpbb_userid');
	} else {
		update_user_meta($user_id, 'phpbb_userid', $phpbb_user_id);
	}
}


function wp_phpbb_add_extra_profile_fields($user)
{
	if (!current_user_can('edit_user', $user->ID))
	{
		return false;
	}

	$phpbb_user_id = (isset($user->phpbb_userid) && $user->phpbb_userid) ? $user->phpbb_userid : 0;
	?>
	<table class="form-table">
		<tr>
			<th><label for="phpbb_userid"><?php _e('phpBB user ID', 'phpbbwpconnect'); ?></label></th>
			<td><input type="text" name="phpbb_userid" id="phpbb_userid" value="<?php echo $phpbb_user_id ?>" class="regular-text" /><br />
				<span class="description"><?php _e("If you would like to change the phpBB user ID type a new one. This action will connect this user with one at your phpBB board.", 'phpbbwpconnect'); ?></span></td>
		</tr>
	</table>
<?php
}
?>
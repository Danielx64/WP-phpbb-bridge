<?php

/******************Change this*******************/
$api_url = 'http://danielx64.com/api/';
/************************************************/

/***********************Parent Theme**************/
if (function_exists('wp_get_theme')) {
	$theme_data = wp_get_theme(get_option('template'));
	$theme_version = $theme_data->Version;
} else {
	$theme_data = get_theme_data(TEMPLATEPATH . '/style.css');
	$theme_version = $theme_data['Version'];
}
$theme_base = get_option('template');
/**************************************************/

//Uncomment below to find the theme slug that will need to be setup on the api server
//var_dump($theme_base);


function check_for_update($checked_data)
{
	global $wp_version, $theme_version, $theme_base, $api_url;

	$request = array(
		'slug' => $theme_base,
		'version' => $theme_version
	);
	// Start checking for an update
	$send_for_check = array(
		'body' => array(
			'action' => 'theme_update',
			'request' => serialize($request),
			'api-key' => md5(get_bloginfo('url'))
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);
	$raw_response = wp_remote_post($api_url, $send_for_check);
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);

	// Feed the update data into WP updater
	if (!empty($response))
		$checked_data->response[$theme_base] = $response;

	return $checked_data;
}

function my_theme_api_call($def, $action, $args)
{
	global $theme_base, $api_url, $theme_version, $api_url;

	if ($args->slug != $theme_base)
		return false;

	// Get the current version

	$args->version = $theme_version;
	$request_string = prepare_request($action, $args);
	$request = wp_remote_post($api_url, $request_string);

	if (is_wp_error($request)) {
		$res = new WP_Error('themes_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>', 'wp_phpbb3_bridge'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);

		if ($res === false)
			$res = new WP_Error('themes_api_failed', __('An unknown error occurred', 'wp_phpbb3_bridge'), $request['body']);
	}

	return $res;
}

if (is_admin())
	$current = get_transient('update_themes');
?>
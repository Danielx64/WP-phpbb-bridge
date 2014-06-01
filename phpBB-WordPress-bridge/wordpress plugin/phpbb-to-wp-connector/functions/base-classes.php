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
*
* Settings and base WP-United class. 
*/

/**
 */
if ( !defined('IN_PHPBB') && !defined('ABSPATH') ) exit;

/**
 *	A simple factory object that can store itself in phpBB.
 *  The constructor either returns ourself, initialised by WordPress, or a stored serialized 
 *  object that was passed to phpBB.
 *
*/
class WP_United_Settings {

	public
		$pluginPath = '',
		$wpPath = '',
		$wpHomeUrl = '',
		$wpBaseUrl = '',
		$pluginUrl = '',
		$wpDocRoot = '',
		$enabled = false,
		$settings = array();

	/**
	 * Not used. Use ::create() to receive an instantiated object from this factory
	 */
	public function __construct() {

	}

	/**
	 * Get an instantiated object from the factory
	 * Tries to load the settings object from WordPress. If WordPress is not available, falls back to the stored
	 * phpBB settings object
	 * @return WP_United_Settings settings object
	 */
	public static function Create() {
		$s = new WP_United_Settings();
		if(!$s->load_from_wp()) {
			return($s->load_from_phpbb());
		}
		return $s;
	}

	/**
	 * Tries to initialise from WordPress options.
	 * @return bool true on success
	 */
	private function load_from_wp() {

		if(function_exists('get_option')) {
			$savedSettings = (array)get_option('phpbbtowp');
			$defaults = $this->get_defaults();
			$this->settings = array_merge($defaults, (array)$savedSettings);

		//	$this->wpPath = ABSPATH;
			$this->pluginPath = plugin_dir_path(__FILE__);
			$this->pluginUrl = plugins_url('phpbb-to-wp-connector') . '/';
			$this->wpHomeUrl = home_url('/');
			$this->wpBaseUrl = site_url('/');
			$this->wpDocRoot = wpu_get_doc_root();
			return true;
		}
		return false;
	}

	/**
	 * Tries to initialise by restoring a serialised object from phpBB config.
	 * Returns default settings if nothing can be loaded.
	 * @return WP_United_Settings settings object
	 */
	private function load_from_phpbb() {
		global $config;

		$wpuString = '';
		$key = 1;
		while(isset( $config["wpu_settings_{$key}"])) {
			$wpuString .= $config["wpu_settings_{$key}"];
			$key++;
		}

		// convert config value into something just like me :-)
		if(!empty($wpuString)) {
			$wpuString =  gzuncompress(base64_decode($wpuString));
			$settingsObj = unserialize($wpuString);
			if(is_object($settingsObj)) {
				return $settingsObj;
			}
		}

		// failed on all accounts. Initialise ourselves with defaults
		$this->settings = $this->get_defaults();
		return $this;

	}

	/**
	 * Updates the settings in WordPress if WordPress is available
	 * @param array Array of settings to store
	 * @return void
	 */
	public function update_settings($data) {

		if(function_exists('update_option')) {
			$data = array_merge($this->settings, (array)$data);
			update_option('phpbbtowp', $data);
			$this->settings = $data;
		}
	}

	/**
	 * Get the default settings
	 * @return array the default settings
	 */
	private function get_defaults() {
		return array(
			'phpbb_path' 				=> ''
		);
	}
}


abstract class WP_United_Plugin_Base {
	protected 		
		$filters = array(),
		$actions = array(),
		$lastRun = false,
		$settings = false;
		
		
		
	public function __construct($initWithSettingsObj = false) {

		if(!$initWithSettingsObj) {
			$this->load_settings();
		} else {
			$this->settings = $initWithSettingsObj;
		}

	}

	protected function load_settings() {
		$this->settings = WP_United_Settings::Create();
	}

	public function get_plugin_path() {
		return $this->settings->pluginPath;
	}

	public function get_wp_doc_root() {
		return $this->settings->wpDocRoot;
	}
	
	public function get_wp_home_url() {
		return $this->settings->wpHomeUrl;
	}

	public function get_wp_base_url() {
		return $this->settings->wpBaseUrl;
	}

	public function get_plugin_url() {
		return $this->settings->pluginUrl;
	}

	public function is_enabled() {

		if (defined('WPU_DISABLE') && WPU_DISABLE) {
			return false;
		}

		if($this->is_wordpress_loaded()) {
			$this->settings->enabled = get_option('wpu-enabled');
		}
		return $this->settings->enabled;
	}

	public function is_working() {
		// if ABSPATH is not defined, we must be loaded from phpBB
		if(!defined('ABSPATH')) {
			return true;
		} else {
			return (defined('IN_PHPBB') && ($this->get_last_run() == 'working') && ($this->is_enabled()));
		}
	}

	/**
	 * Returns how the last fun of phpBB went
	 * @return string disconnected|connected|working
	 */
	public function get_last_run() {
	
		if(empty($this->lastRun) && $this->is_wordpress_loaded()) {
			$this->lastRun = get_option('wpu-last-run');
		}

		 return $this->lastRun;
	}	
	
	
	public function is_wordpress_loaded() {
		if(defined('ABSPATH')) {
			return true;
		} else {
			return false;
		}
	}
	
	
	
		
	protected function add_actions() { 
		foreach($this->actions as $actionArray) {
			list($action, $details, $whenToLoad) = $actionArray;

			if(!$this->should_load_filteraction($whenToLoad)) {
				continue;
			}

			switch(sizeof((array)$details)) {
				case 3:
					add_action($action, array($this, $details[0]), $details[1], $details[2]);
				break;
				case 2:
					add_action($action, array($this, $details[0]), $details[1]);
				break;
				case 1:
				default:
					add_action($action, array($this, $details));
			}
		}	
	}
	
	protected function add_filters() {
		foreach($this->filters as $filterArray) {
			list($filter, $details, $whenToLoad) = $filterArray;
			
			if(!$this->should_load_filteraction($whenToLoad)) {
				continue;
			}
			
			switch(sizeof((array)$details)) {
				case 3:
					add_filter($filter, array($this, $details[0]), $details[1], $details[2]);
				break;
				case 2:
					add_filter($filter, array($this, $details[0]), $details[1]);
				break;
				case 1:
				default:
					add_filter($filter, array($this, $details));
			}
		}	
	}	
	// Should we load this filter or action? 
	private function should_load_filteraction($whenToLoad) {
	
		if(!$this->is_enabled() && ($whenToLoad != 'all')) {
			return false;
		}
		
		switch($whenToLoad) {
			case 'all':
			case 'enabled':
				return true;
			break;
			default:

			break;
		}
		
		return true;

	}
}



abstract class WP_United_Plugin_Main_Base extends WP_United_Plugin_Base {

	protected
		
		$version = '';

	/**
	* Initialise the WP-United class
	*/
	public function __construct() {
		
		$currPath = dirname(__FILE__);
		require_once($currPath . '/functions-general.php');
		parent::__construct();
		
	}

	public function enable() {
		$this->settings->enabled = true;
		if($this->is_wordpress_loaded()) {
			update_option('wpu-enabled', true);
		}
	}
	public function disable() {
		$this->settings->enabled = false;
		if($this->is_wordpress_loaded()) {
			update_option('wpu-enabled', false);
		}
	}
	
	public function get_version($includeRevision = false) {

	}

	public function check_mod_version() {
		global $phpEx;

		static $checked = false;

		if(is_array($checked)) {
			return $checked;
		}

		global $wpuAutoPackage, $wpuReleasePackage;
		$propress_options = get_option( 'phpbbtowp' );
		$pLoc = $propress_options['phpbb_path'];

		if(empty($pLoc)) {
			$checked =  array(
				'result'	=>	'OK',
				'message'	=> 	__('The location to phpBB is not set.')
			);
			return $checked;
		}

		$checked = array(
			'result' 		=> 'OK',
			'mesage' 	=> ''
		);
		return $checked;

	}

	protected function ajax_result($errMsg, $msgType = 'message') {
		if($msgType == 'error') {
			$errMsg = '[ERROR]' . $errMsg;
		}
		die($errMsg);
	}
	
	protected function ajax_ok() {
		$this->ajax_result('OK', 'message');
	}
}

// That's all. Done.
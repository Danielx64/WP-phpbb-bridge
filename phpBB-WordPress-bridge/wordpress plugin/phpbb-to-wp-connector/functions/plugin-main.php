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

class WP_United_Plugin extends WP_United_Plugin_Main_Base {

	protected
		// Actions and filters. These are loaded as needed depending on which WP-United portions are active.
		// Format: array( event | function in this class(in an array if optional arguments are needed) | loading circumstances)
		$actions = array(
			// required actions on all pages
			array('plugins_loaded', 					'init_plugin',								'all'),  // this should be 'init', but we want to play with current_user, which comes earlier
		//	array('wp_head', 							'add_scripts',								'enabled'),

			// required admin ajax actions
			array('wp_ajax_wpu_filetree', 				'filetree',									'all'),

			array('wp_ajax_wpu_settings_transmit', 		'ajax_settings_transmit',					'all'),

		);

		
		private
			$doneInit 		= false;

	/**
	* All base init is done by the parent class.
	*/
	public function __construct() {

		parent::__construct();
		
	}
	
	/**
	 * Initialises the plugin from WordPress.
	 * This is not in the constructor, as this class can be instantiated from either phpBB or WordPress.
	 * @return void
	 */
	public function wp_init() {
	
		// (re)load our settings
		$this->load_settings();

		// add new actions and filters
		$this->add_actions();

		unset($this->actions);

	}
	
	/**
	 * The main invocation logic -- if enabled, load phpBB too!
	 * Called on plugins_loaded hook, so we can get phpBB ready in advance of user integration when set_current_user is called.
	 * @return void
	 */
	public function init_plugin() { 
		global $phpbbForum;

		if($this->has_inited()) {
			return false;
		}
		$this->doneInit = true;
		
		$shouldRun = true;

		$propress_options = get_option( 'phpbbtowp' );
		if(!$propress_options['phpbb_path'] || !WP_United_Plugin::can_connect_to_phpbb()) {
			$this->set_last_run('disconnected');
			$shouldRun = false;
		}


		if($this->get_last_run() == 'connected') {
			$shouldRun = false;
		}
		
		if($shouldRun) {
			$this->set_last_run('connected');
		}
		
		$versionCheck = $this->check_mod_version();
		if($versionCheck['result'] != 'OK') {
			$this->disable();
			$shouldRun = false;
		}
		
		if($this->is_enabled() && $shouldRun) { 

			$this->set_last_run('working');

		}

		return true; 
			
	}

	public function can_connect_to_phpbb() {
		global $wpUnited;
		$propress_options = get_option( 'phpbbtowp' );
		$rootPath = $propress_options['phpbb_path'];

		if(!$rootPath) {
			return false;
		}

		static $canConnect = false;
		static $triedToConnect = false;

		if($triedToConnect) {
			return $canConnect;
		}

		$canConnect = @file_exists($rootPath);
		$triedToConnect = true;


		return $canConnect;

	}
	/**
	 * 
	 * Admin AJAX actions
	 *
	*/
	public function filetree() {
		if(check_ajax_referer( 'wp-united-filetree')) {
			wpu_filetree();
		}
		die();
	}

	public function ajax_settings_transmit() {
		if(check_ajax_referer( 'wp-united-transmit')) {
			wpu_process_settings();
			$this->transmit_settings();
			die('OK');
		}
		die();
	}	

	/**
	 * Transmit settings to phpBB
	 * This could either be an update settings or enable rquest, or a disable request.
	 * @param bool $enable true to enable WP-United in phpBB config
	 * @return void
	 */
	public function transmit_settings($enable = true) {
		global $phpbbForum;
		
		//if WPU was disabled, we need to initialise phpBB first
		// phpbbForum is already inited, however -- we just need to load
		if (!defined('IN_PHPBB')) {
			
			$this->set_last_run('connected');

		}
	
		// store data before transmitting
		if($enable) {
			$this->enable();
		} else {
			$this->disable();
		}
		
		$dataToStore = $this->settings;
	}

	/**
	 * Returns true if WP-United has already initialised
	 * @return bool true if already inited
	 */
	public function has_inited() {
		return $this->doneInit;
	}
	
	/**
	 * A way of storing how the last run of phpBB went -- only used during connecting and enabling phpBB
	 * States transition through disconnected -> connected -> working
	 * @param string $status disconnected|connected|working
	 */
	private function set_last_run($status) {
		if($this->get_last_run() != $status) { 
			// transitions cannot go from 'working' to 'connected' if wp-united is enabled OK.
			if( ($this->lastRun == 'working') && ($status == 'connected') && $this->is_enabled() ) {
				return;
			} 
			$this->lastRun = $status;
			update_option('wpu-last-run', $status);
		}
	}
	
	/**
	 * Updates the stored WP-United settings
	 * The Wp-United class decorates itself with the stored phpBB or WordPress settings class, invoked as appropriate. 
	 * WP settings take priority
	 * @param array an array of all settings keys
	 * @return void
	 */
	public function update_settings($data) {
		$this->settings->update_settings($data);
	}
}
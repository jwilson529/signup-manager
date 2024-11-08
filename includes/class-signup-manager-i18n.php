<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://oneclickcontent.com
 * @since      1.0.0
 *
 * @package    Signup_Manager
 * @subpackage Signup_Manager/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Signup_Manager
 * @subpackage Signup_Manager/includes
 * @author     James Wilson <james@oneclickcontent.com>
 */
class Signup_Manager_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'signup-manager',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}

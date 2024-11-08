<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://oneclickcontent.com
 * @since             1.0.0
 * @package           Signup_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       Signup Manager
 * Plugin URI:        https://oneclickcontent.com
 * Description:       Manage the _signups table
 * Version:           1.0.0
 * Author:            James Wilson
 * Author URI:        https://oneclickcontent.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       signup-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SIGNUP_MANAGER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-signup-manager-activator.php
 */
function signup_manager_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-signup-manager-activator.php';
	Signup_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-signup-manager-deactivator.php
 */
function signup_manager_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-signup-manager-deactivator.php';
	Signup_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'signup_manager_activate' );
register_deactivation_hook( __FILE__, 'signup_manager_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-signup-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function signup_manager_run() {

	$plugin = new Signup_Manager();
	$plugin->run();
}
signup_manager_run();

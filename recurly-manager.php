<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              
 * @since             1.3
 * @package           Recurly_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       Recurly Manager
 * Plugin URI:
 * Description:       The Recurly Manager plugin seamlessly integrates your WordPress site with Recurly, allowing users to purchase subscription plans directly using the Recurly API. Simplify your subscription management with secure transactions and easy plan control.
 * Version:           1.3
 * Author:            BooSpot
 * Author URI:        https://boospot.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       recurly-manager
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
define( 'RECURLY_MANAGER_VERSION', '1.3' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-recurly-manager-activator.php
 */
function activate_recurly_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-recurly-manager-activator.php';
	Recurly_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-recurly-manager-deactivator.php
 */
function deactivate_recurly_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-recurly-manager-deactivator.php';
	Recurly_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_recurly_manager' );
register_deactivation_hook( __FILE__, 'deactivate_recurly_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'vendor/autoload.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-recurly-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_recurly_manager() {

	$plugin = new Recurly_Manager();
	$plugin->run();

}
run_recurly_manager();

function recurly_manager_add_settings_page( $links ) {
    $settings_link = '<a href="/wp-admin/options-general.php?page=recurly-manager">Settings</a>';
    array_unshift( $links, "$settings_link" );

    return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'recurly_manager_add_settings_page', 10 );


/**
 * A Helper function added to log anything for debugging
 *
 * @param $log
 *
 * @return void
 */
// function briks_write_log( $log ): void {

// 	if ( is_array( $log ) || is_object( $log ) ) {
// 		$log = print_r( $log, true );
// 	}
// 	error_log( basename( __FILE__ ) . ' : ' . __LINE__ . ' : ' . $log . PHP_EOL, 3, trailingslashit( get_template_directory() ) . 'debug.log' );
// }

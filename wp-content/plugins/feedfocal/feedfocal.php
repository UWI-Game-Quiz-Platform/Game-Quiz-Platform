<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              feedfocal.com
 * @since             1.0.0
 * @package           FeedFocal
 *
 * @wordpress-plugin
 * Plugin Name:       FeedFocal
 * Plugin URI:        FeedFocal
 * Description:       Easily collect customer feedback to improve your users experience.
 * Version:           1.3.2
 * Author:            FeedFocal
 * Author URI:        feedfocal.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       feedfocal
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define global variables
 */
define( 'FEEDFOCAL_VERSION', '1.3.2' );
define( 'FEEDFOCAL_PLUGIN_FILE', plugin_basename(__FILE__) );
define( 'FEEDFOCAL_PLUGIN_SETTINGS_URL', 'admin.php?page=feedfocal_projects' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-feedfocal-activator.php
 */
function activate_feedfocal() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-feedfocal-activator.php';
	FeedFocal_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-feedfocal-deactivator.php
 */
function deactivate_feedfocal() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-feedfocal-deactivator.php';
	FeedFocal_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_feedfocal' );
register_deactivation_hook( __FILE__, 'deactivate_feedfocal' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-feedfocal.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_feedfocal() {

	$plugin = new FeedFocal();
	$plugin->run();

}
run_feedfocal();

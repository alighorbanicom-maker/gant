<?php
/**
 * Plugin Name:       Project Management with Gantt & CPM
 * Plugin URI:        https://dralighorbani.com/
 * Description:       A project management plugin to create Gantt charts, perform CPM analysis, track progress, and generate reports.
 * Version:           1.0.0
 * Author:            Jules for Dr. Ali Ghorbani
 * Author URI:        https://dralighorbani.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-project-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPM_PLUGIN_VERSION', '1.0.0' );
define( 'WPM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_wp_project_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpm-activator.php';
	WPM_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_wp_project_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-project-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_project_manager() {

	$plugin = new Wp_Project_Manager();
	$plugin->run();

}
run_wp_project_manager();
?>
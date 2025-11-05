<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/includes
 * @author     Jules
 */
class WPM_Activator {

	/**
	 * Create database tables needed for the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$table_name_projects = $wpdb->prefix . 'wpm_projects';
		$sql_projects = "CREATE TABLE $table_name_projects (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			project_name tinytext NOT NULL,
            start_date date DEFAULT '0000-00-00' NOT NULL,
			created_by bigint(20) unsigned NOT NULL,
			creation_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			last_modified datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		$table_name_tasks = $wpdb->prefix . 'wpm_tasks';
		$sql_tasks = "CREATE TABLE $table_name_tasks (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			project_id mediumint(9) NOT NULL,
			task_name text NOT NULL,
			duration int(11) NOT NULL DEFAULT 0,
			dependencies text,
			progress int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_projects );
		dbDelta( $sql_tasks );
	}

}
?>
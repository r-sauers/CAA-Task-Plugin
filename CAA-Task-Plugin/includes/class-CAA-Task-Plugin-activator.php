<?php

/**
 * Adapted from https://github.com/DevinVinson/WordPress-Plugin-Boilerplate under the GPL v2 license
 * Modified: 5/8/23
 * 
 * Fired during plugin activation
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Activator {

	/**
	 * Short Description.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		CAA_Task_Plugin_Activator::create_variable_table();
	}

	private static function create_variable_table() {
		// Code adapted from: https://codex.wordpress.org/Creating_Tables_with_Plugins


		global $wpdb;

		$table_name = $wpdb->prefix . 'CAA_TASK_PLUGIN_VARIABLES';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		clientID varchar(55) DEFAULT '' NOT NULL,
		clientSecret varchar(55) DEFAULT '' NOT NULL
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}

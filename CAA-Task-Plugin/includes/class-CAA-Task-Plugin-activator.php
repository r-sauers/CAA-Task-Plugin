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
 * Import CAA_Task_Plugin_Task_Definition_Table and CAA_Task_Plugin_Event_Type_Table to intitialize
 * tables.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/task-definitions/class-CAA-Task-Plugin-Task-Definition-Table.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/event-types/class-CAA-Task-Plugin-Event-Type-Table.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/events/class-CAA-Task-Plugin-Event-Table.php';

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
	 * Creates tables for task definitions and event types.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		CAA_Task_Plugin_Task_Definition_Table::create_table();
		CAA_Task_Plugin_Event_Type_Table::create_table();
		CAA_Task_Plugin_Event_Table::create_table();
	}

}

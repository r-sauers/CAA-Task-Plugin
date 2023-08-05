<?php

/**
 * Adapted from https://github.com/DevinVinson/WordPress-Plugin-Boilerplate under the GPL v2 license
 * Modified: 5/8/23
 * 
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @package    CAA_Task_Plugin
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Import CAA_Task_Plugin_Task_Definition_Table and CAA_Task_Plugin_Event_Type_Table to delete
 * tables.
 */
require_once plugin_dir_path( dir_name( __FILE__ ) ) . 'admin/partials/task-definitions/class-CAA-Task-Plugin-Task-Definition-Table.php';
require_once plugin_dir_path( dir_name( __FILE__ ) ) . 'admin/partials/event-types/class-CAA-Task-Plugin-Event-Type-Table.php';
require_once plugin_dir_path( dir_name( __FILE__ ) ) . 'admin/partials/events/class-CAA-Task-Plugin-Event-Table.php';

CAA_Task_Plugin_Task_Definition_Table::delete_table();
CAA_Task_Plugin_Event_Type_Table::delete_table();
CAA_Task_Plugin_Event_Table::delete_table();
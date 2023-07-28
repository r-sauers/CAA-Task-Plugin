<?php

/**
 * Defines the CAA_Task_Plugin_Task_Definition_Table class that manages the SQL table for task definitions.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */

/**
 * Imports the CAA_Task_Plugin_Task_Definition class so objects of that type can be returned.
 */
require_once plugin_dir_path( __FILE__ ) . 'class-CAA-Task-Plugin-Task-Definition.php';


/**
 * Used to manage SQL table for task definitions and retrieve data from it.
 *
 * Task definitions are used to define basecamp tasks. They can be created for an event type (all
 * unique). They can be retrieved by ID using the event type to create a basecamp task. They can be
 * edited when editing an event type.
 * 
 * The table for event types is structured with columns for:
 * - ID
 * - title
 * - start_offset_in_days  (how many days before the start of the event should this be started?)
 * - finish_offset_in_days (how many days before the start of the event is this due?)
 * - description
 * 
 * @todo       Implement assigned role for task description.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Task_Definition_Table {

    /**
	 * The name of the table defining task definitions in the SQL database
	 *
	 * @since    1.0.0
	 * @var      string    TABLE_NAME    The name of the table
	 */
    const TABLE_NAME = "caa_task_plugin_task_definitions";

    /**
	 * Creates the table for task definitions in mysql using wordpress.
     * 
     * Called in @see CAA_Task_Plugin_Activator.
     * Contains columns for ID, title, start_offset_in_days, finish_offset_in_days, and description_file
	 * 
	 * @since	1.0.0
	 */
    public static function create_table() {

        // Code adapted from: https://codex.wordpress.org/Creating_Tables_with_Plugins
		global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
        id int NOT NULL AUTO_INCREMENT,
		title varchar(55) DEFAULT '' NOT NULL,
		start_offset_in_days int DEFAULT 0 NOT NULL,
        finish_offset_in_days int DEFAULT 0 NOT NULL,
        description MEDIUMTEXT DEFAULT NULL,
        PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
    }

    /**
     * Creates a task definition in the table.
     * 
     * Creates the task definition in the table, and assigns the id of the table entry to the task
     * definition passed in.
     * 
     * @since   1.0.0
     * @param   CAA_Task_Plugin_Task_Definition  The Task Definition that needs added to the table.
     * @return  bool    Returns true on success, false on failure.
     */
    public static function add_task_definition( CAA_Task_Plugin_Task_Definition $task_definition ): bool {

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $res = $wpdb->insert( 
            $table_name, 
            [ 
                'title' => $task_definition->get_title(), 
                'start_offset_in_days' => $task_definition->get_start_offset_in_days(),
                'finish_offset_in_days' => $task_definition->get_finish_offset_in_days(),
                'description' => $task_definition->get_description()
            ] 
        );

        if ( false === $res ) {
            return false;
        } else {
            $task_definition->set_id( $wpdb->insert_id );
            return true;
        }
    }

    /**
     * Updates the task definition with the id $ID in the table with given data.
     * 
     * @since   1.0.0
     * @param   CAA_Task_Plugin_Task_Definition  The updated Task Definition that needs updated in the table.
     * The task definition must already exist in the table.
     * @return  bool    Returns true on success and false on failure.
     */
    public static function update_task_definition( CAA_Task_Plugin_Task_Definition $task_definition ): bool {

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $res = $wpdb->update( 
            $table_name, 
            [ 
                'title' => $task_definition->get_title(), 
                'start_offset_in_days' => $task_definition->get_start_offset_in_days(),
                'finish_offset_in_days' => $task_definition->get_finish_offset_in_days(),
                'description' => $task_definition->get_description()
            ], 
            [
                'id' => $task_definition->get_id()
            ]
        );

        if ( false === $res ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Retrieves a Task Definition with the given $id from the table.
     * 
     * @param   int     $id     The ID of the Task Definition in the wordpress table that should 
     * be retrieved.
     * @return CAA_Task_Plugin_Task_Definition  the Task Definition with the given ID.
     */
    public static function get_task_definition( int $id ): CAA_Task_Plugin_Task_Definition {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $res = $wpdb->get_row( "SELECT * FROM $table_name WHERE id=$id" );

        if ( null === $res ) {
            return null;
        }

        $task_definition = new CAA_Task_Plugin_Task_Definition( 
            $res->title,
            $res->start_offset_in_days,
            $res->finish_offset_in_days,
            $res->description
        );
        $task_definition->set_id( $res->id );
        return $task_definition;
    }

    /**
	 * Deletes the task definitions table in mysql using wordpress.
     * 
     * Called in uninstall.php.
	 * 
	 * @since	1.0.0
	 */
    public static function delete_table() {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS" . $wpdb->prefix . self::TABLE_NAME );
    }

}
<?php

/**
 * Defines the CAA_Task_Plugin_Event_Type_Table class that manages the SQL table for event types.
 * 
 * Event types are used in event creation to define the functionality and needs of the event.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */

/**
 * Imports the CAA_Task_Plugin_Event_Type class so objects of that type can be returned.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/class-CAA-Task-Plugin-Event-Type.php';

/**
 * Used to manage SQL table for event types and retrieve data from it.
 *
 * Event types are used in event creation to define the functionality and needs of the event. They
 * contain task definitions and subtypes. Therefore this class is accessed when a client creates/edits/deletes
 * an event type, needs event types displayed, or needs access to task definitions for creating tasks.
 * 
 * The table for event types is structured with columns for:
 * - ID
 * - display_name (client facing name of the event type)
 * - subtypes (a comma separated string of event type IDs)
 * - description (a short description for the client to understand what the event type does)
 * - task_definitions (a comma separated string of task definition IDs).
 * - finished (boolean, true if the event type has finished being created by client)
 * - deleted (boolean, true if the event type is deleted)
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Event_Type_Table {

    /**
	 * The name of the table defining event types in the SQL database
	 *
	 * @since    1.0.0
	 * @var      string    TABLE_NAME    The name of the table
	 */
    const TABLE_NAME = "caa_task_plugin_event_types";

    /**
	 * Creates the table for task definitions in mysql using wordpress.
     * 
     * Called in @see CAA_Task_Plugin_Activator.
     * The table for event types is structured with columns for:
	* - ID
	* - display_name (client facing name of the event type)
	* - subtypes (a comma separated string of event type IDs)
	* - description (a short description for the client to understand what the event type does)
	* - task_definitions (a comma separated string of task definition IDs).
	* - finished (boolean, true if the event type has finished being created by client)
	* - deleted (boolean, true if the event type is deleted)
	 * 
	 * @since	1.0.0
	 * @global	$wpdb	The wordpress database global
	 */
    public static function create_table() {
        
        // Code adapted from: https://codex.wordpress.org/Creating_Tables_with_Plugins
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
        id int NOT NULL AUTO_INCREMENT,
		display_name TEXT DEFAULT '' NOT NULL,
		description MEDIUMTEXT DEFAULT '' NOT NULL,
		subtypes TEXT DEFAULT '' NOT NULL,
        task_definitions TEXT DEFAULT '' NOT NULL,
		finished boolean DEFAULT false NOT NULL,
		deleted boolean DEFAULT false NOT NULL,
        PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
    }

	/**
	 * Adds an event type to the table.
	 * 
	 * The table entries are unfilled, and must be filled using @see update_event_type. Task definitions
	 * must be added using add_task_definition
	 * 
	 * @since 	1.0.0
	 * @global	$wpdb		The wordpress database global
	 * @return	CAA_Task_Plugin_Event_Type|null	The created event type or null if the event type couldn't be created. Note that the event type only has an id filled!
	 */
	public static function create_event_type(): int | null {

		global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $res = $wpdb->insert( 
            $table_name, 
            [ ] // no table entries, everything should be the default defined by MySQL
        );

        if ( false === $res ) {
            return null;
        }

		return new CAA_Task_Plugin_Event_Type( $wpdb->insert_id );

	}

	/**
	 * Updates the event type in the table.
	 * 
	 * @since	1.0.0
	 * @global	$wpdb	The wordpress database global
	 * @param	CAA_Task_Plugin_Event_Type $event_type, the event type object containing the updated information
	 * @return	boolean True if table was successfully updated, false if otherwise.
	 */
	public static function update_event_type( CAA_Task_Plugin_Event_Type $event_type ){

		global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $res = $wpdb->update( 
            $table_name, 
            [ 
				'display_name' => $event_type->get_display_name(),
				'description' => $event_type->get_description(),
                'subtypes' => $event_type->get_subtype_ids_csv(),
				'task_definitions' => $event_type->get_task_definition_ids_csv(),

            ], 
            [
                'id' => $event_type->get_id()
            ]
        );

		if ( false === $res ) {
            return false; // ERROR: SQL Update failed
        } else {
			return true;
		}

	}

	/**
	 * Classifies the event type as 'finished'.
	 * 
	 * This function is used when a user clicks 'submit', which will cause the event type to show
	 * up in displays.
	 * 
	 * @since	1.0.0
	 * @global 	$wpdb	The wordpress database global.
	 * @param  	CAA_Task_Plugin_Event_Type $event_type, the event type object for the event type that needs finished.
	 * @return	boolean True if table was successfully updated, false if otherwise.
	 */
	public static function finish_event_type( CAA_Task_Plugin_Event_Type $event_type ) {

		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$res = $wpdb->update(
			$table_name,
			[ 'finished' => true ],
			[ 'id' => $event_type->get_id() ]
		);

		if ( false === $res ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Deletes the event type.
	 * 
	 * This marks the event type table entry as deleted. It will no longer show up in @see get_event_types.
	 * 
	 * @since	1.0.0
	 * @global	$wpdb	The wordpress database global
	 * @param	CAA_Task_Plugin_Event_Type $event_type, the event type that needs deleted.
	 * @return	boolean	True if event was successfully deleted, false if otherwise.
	 */
	public static function delete_event_type( CAA_Task_Plugin_Event_Type $event_type ) {

		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$res = $wpdb->update(
			$table_name,
			[ 'deleted' => true ],
			[ 'id' => $event_type->get_id() ]
		);

		if ( false === $res ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Converts a table response into an event type object.
	 * 
	 * This is used in get_event_type, and get_event_types
	 * 
	 * @since	1.0.0
	 * @param	array	$table_response	is the response from an sql table.
	 * @return	CAA_Task_Plugin_Event_Type	The event type object.
	 */
	private static function event_type_from_table_response( $table_response ){

		$event_type = new CAA_Task_Plugin_Event_Type( $table_response->id );
		$event_type->set_display_name( $table_response->display_name );
		$event_type->set_subtypes_csv( $table_response->subtypes );
		$event_type->set_todo_definitions_csv( $table_response->todo_definitions );

		return $event_type;
	}

	/**
     * Retrieves an Event Type with the given $ID from the table.
	 * 
	 * Can retrieve deleted and unfinished event types.
     * 
	 * @since	1.0.0
	 * @global	$wpdb	The wordpress database global.
     * @param   int     $ID     The ID of the Event Type in the wordpress table that should 
     * be retrieved.
     * @return CAA_Task_Plugin_Event_Type  the Event Type with the given ID.
     */
	public static function get_event_type( int $ID ) : CAA_Task_Plugin_Event_Type|null {
		global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $res = $wpdb->get_row( "SELECT * FROM $table_name WHERE ID=$ID" );

        if ( null === $res ) {
            return null;
        }

        return self::event_type_from_table_response( $res );
	}

	/**
     * Retrieves all event types from the table that have not been deleted and have been finished.
     * 
	 * @since	1.0.0
	 * @global	$wpdb	The wordpress database global.
     * @return array  An array of event type objects.
     */
	public static function get_event_types() {

		global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
		$res = $wpdb->get_results( "SELECT * FROM $table_name WHERE finished=true AND deleted=false", "ARRAY_A" );

		$event_types = array_map( 'self::event_type_from_table_response', $res );

		return $event_types;
	}

    /**
	 * Deletes the task definitions table in mysql using wordpress.
     * 
     * Called in uninstall.php.
	 * 
	 * @since	1.0.0
	 * @global	$wpdb	The wordpress database global.
	 */
    public static function delete_table() {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS" . $wpdb->prefix . self::TABLE_NAME );
    }

}
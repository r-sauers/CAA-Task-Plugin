<?php

/**
 * Defines the CAA_Task_Plugin_Event_Table class that manages the SQL table for events.
 * 
 * Events are used to generate basecamp tasks for a given event.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */

/**
 * Imports the CAA_Task_Plugin_Event class so objects of that type can be returned.
 */
require_once plugin_dir_path( __FILE__ ) . 'class-CAA-Task-Plugin-Event.php';

/**
 * Used to manage SQL table for events and retrieve data from it.
 *
 * Events are used to generate basecamp tasks for an event. At the moment they can be created and
 * viewed, but not edited. When the event is created, it will create the tasks in basecamp for the event.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Event_Table {

	/**
	 * The name of the table defining events in the SQL database
	 *
	 * @since    1.0.0
	 * @var      string    TABLE_NAME    The name of the table
	 */
	const TABLE_NAME = "caa_task_plugin_events";

	/**
	 * Creates the table for events in mysql using wordpress.
	 * 
	 * Called in @see CAA_Task_Plugin_Activator.
	 * The table for event types is structured with columns for:
	 * - id
	 * - name (client facing name of the event)
	 * - event_types (a comma separated string of event type IDs)
	 * - location (location of event)
	 * - start_time_unix_timestamp (the start time of the event in seconds since jan 1 1970)
	 * - end_time_unix_timestamp (the end time of the event in seconds since jan 1 1970)
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
		name TEXT DEFAULT '' NOT NULL,
		event_types TEXT DEFAULT '' NOT NULL,
        location TEXT DEFAULT '' NOT NULL,
		start_time_unix_timestamp int DEFAULT NULL,
		end_time_unix_timestamp int DEFAULT NULL,
		finished boolean DEFAULT false NOT NULL,
		deleted boolean DEFAULT false NOT NULL,
        PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Creates an empty event in the mySQL table.
	 * @since 1.0.0
	 * @global $wpdb is the wordpress database global
	 * @return CAA_Task_Plugin_Event|WP_Error the empty event that was created, with the id matching the table entry.
	 */
	public static function create_event(): CAA_Task_Plugin_Event|WP_Error {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$res        = $wpdb->insert(
			$table_name,
			[ 'name' => "" ] // no table entries, everything should be the default defined by MySQL
		);

		if ( false === $res ) {
			return new WP_Error( "Failed Database Insert", "Failed to create event in mySQL table $table_name." );
		}

		return new CAA_Task_Plugin_Event( $wpdb->insert_id );
	}

	/**
	 * Updates the corresponding entry in the table with the event passed in.
	 * @since 1.0.0
	 * @global $wpdb is the wordpress database global
	 * @param CAA_Task_Plugin_Event $event
	 * @return WP_Error|bool true on success, WP_Error if it fails
	 */
	public static function update_event( CAA_Task_Plugin_Event $event ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$id         = $event->get_id();
		$res        = $wpdb->update(
			$table_name,
			[ 
				'name' => $event->get_name(),
				'event_types' => $event->get_event_type_ids_csv(),
				'location' => $event->get_location(),
				'start_time_unix_timestamp' => $event->get_start_time_unix_timestamp(),
				'end_time_unix_timestamp' => $event->get_end_time_unix_timestamp(),

			],
			[ 
				'id' => $id
			]
		);

		if ( false === $res ) {
			return new WP_Error( "Failed Database Update", "Failed to update event (id:$id) in mySQL table $table_name." );
		} else {
			return true;
		}
	}

	/**
	 * Classifies the event as 'finished'.
	 * 
	 * This function is used when a user clicks 'submit', which will cause the event to show
	 * up in the table display all events.
	 * 
	 * @since	1.0.0
	 * @global 	$wpdb	The wordpress database global.
	 * @param  	CAA_Task_Plugin_Event $event specifies the id of the event that needs 'finished'
	 * @return	WP_Error|bool True if table was successfully updated, WP_Error if otherwise.
	 */
	public static function finish_event( CAA_Task_Plugin_Event $event ): WP_Error|bool {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$id         = $event->get_id();
		$res        = $wpdb->update(
			$table_name,
			[ 'finished' => true ],
			[ 'id' => $id ]
		);

		if ( false === $res ) {
			return new WP_Error( "Failed Database Update", "Failed to update event (id:$id) to 'finished' in mySQL table $table_name" );
		} else {
			return true;
		}
	}

	/**
	 * Deletes the event type.
	 * 
	 * This marks the event type table entry as deleted. It will no longer show up in get_events
	 * 
	 * @since 1.0.0
	 * @global $wpdb is the wordpress database global
	 * @param CAA_Task_Plugin_Event $event specifies the id of the event that needs deleted.
	 * @return WP_Error|bool True if the table was successfully updated, WP_Error otherwise.
	 */
	public static function delete_event( CAA_Task_Plugin_Event $event ): WP_Error|bool {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$id         = $event->get_id();
		$res        = $wpdb->update(
			$table_name,
			[ 'deleted' => true ],
			[ 'id' => $id ]
		);

		if ( false === $res ) {
			return new WP_Error( "Failed Database Update", "Failed to update event (id:$id) to 'deleted' in mySQL table $table_name." );
		} else {
			return true;
		}
	}

	/**
	 * Converts a table response for an event query into a CAA_Task_Plugin_Event.
	 * @since 1.0.0
	 * @param object $table_response
	 * @return CAA_Task_Plugin_Event
	 */
	private static function event_from_table_response( object $table_response ): CAA_Task_Plugin_Event {

		$event = new CAA_Task_Plugin_Event( $table_response->id );
		$event->set_name( $table_response->name );
		$event->set_location( $table_response->location );
		$event->set_event_type_ids_csv( $table_response->event_types );
		$event->set_start_time_unix_timestamp( $table_response->start_time_unix_timestamp );
		$event->set_end_time_unix_timestamp( $table_response->end_time_unix_timestamp );
		return $event;
	}

	/**
	 * Retrieves an Event with the given $id from the table.
	 * 
	 * Can retrieve deleted and unfinished events.
	 * 
	 * @since	1.0.0
	 * @global	$wpdb	The wordpress database global.
	 * @param   int     $id     The id of the Event in the wordpress table that should 
	 * be retrieved.
	 * @return CAA_Task_Plugin_Event|WP_Error is the Event Type with the given ID.
	 */
	public static function get_event( int $id ): CAA_Task_Plugin_Event|WP_Error {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$query      = "SELECT * FROM $table_name WHERE id=$id";
		$res        = $wpdb->get_row( $query );

		if ( null === $res ) {
			return new WP_Error( "Failed Database Row Retrieval", "Query: '$query'" );
		}

		return self::event_from_table_response( $res );
	}

	/**
	 * Retrieves all events from the table that have not been deleted and have been finished.
	 * 
	 * @since	1.0.0
	 * @global	$wpdb	The wordpress database global.
	 * @return CAA_Task_Plugin_Event[] all finished, and undeleted events in the table.
	 */
	public static function get_events() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$res        = $wpdb->get_results( "SELECT * FROM $table_name WHERE finished=true AND deleted=false", "OBJECT" );
		$events     = array_map( "self::event_from_table_response", $res );
		return $events;
	}

	/**
	 * Deletes the event table in mysql using wordpress.
	 * 
	 * Called in uninstall.php.
	 * 
	 * @since	1.0.0
	 * @global	$wpdb	The wordpress database global.
	 */
	public static function delete_table() {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS" . $wpdb->prefix . self::TABLE_NAME );
	}

}
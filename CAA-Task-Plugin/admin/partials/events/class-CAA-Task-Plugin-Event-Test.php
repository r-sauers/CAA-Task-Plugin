<?php

/**
 * Defines the CAA_Task_Plugin_Event_Test class that runs tests on events and the events table.
 * 
 * Runs tests on CAA_Task_Plugin_Event and CAA_Task_Plugin_Event_Table
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */

 /**
 * Defines the CAA_Task_Plugin_Event_Test class that runs tests on events and the events table.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Event_Test {

    /**
     * Runs tests on CAA_Task_Plugin_Event and CAA_Task_Plugin_Event_Table.
     * @return void
     * @since 1.0.0
     */
    public static function test() {
		// get classes to test
		require_once plugin_dir_path( __FILE__ ) . 'class-CAA-Task-Plugin-Event.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-CAA-Task-Plugin-Event-Table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'event-types/class-CAA-Task-Plugin-Event-Type.php';


		$event = CAA_Task_Plugin_Event_Table::create_event();

		// empty case csv
		$event->set_event_type_ids_csv( "" );
		assert( $event->get_event_type_ids_csv() === "", "get_event_type_ids_csv failed empty case." );

		$event->set_event_type_ids_csv( "43,44" );
		$event->remove_event_type( new CAA_Task_Plugin_Event_Type( 43 ) );
		$event->add_event_type( new CAA_Task_Plugin_Event_Type( 42 ) );

		// duplicate test
		$res = $event->add_event_type( new CAA_Task_Plugin_Event_Type( 44 ) );
		assert( is_wp_error( $res ), "Failed to catch duplicate event type." );

		// assert event types are correct
		$csv = $event->get_event_type_ids_csv();
		assert( $csv === "42,44" || $csv === "44,42", "" );

		assert( true === CAA_Task_Plugin_Event_Table::update_event( $event ), "Failed to update event." );

		$event->set_name( "test event" );
		$event->set_location( "TBD" );
		$event->set_start_time_unix_timestamp( 1691170121 );
		$event->set_end_time_unix_timestamp( 1691180121 );

		assert( true === CAA_Task_Plugin_Event_Table::update_event( $event ), "Failed to update event." );
		assert( true === CAA_Task_Plugin_Event_Table::finish_event( $event ), "Failed to finish event." );

		$events = CAA_Task_Plugin_Event_Table::get_events();

		$table_event = $events[ count( $events ) - 1 ];
		assert( $table_event->get_id() === $event->get_id(), "Event ID not updated in table properly." );
		assert( $table_event->get_name() === $event->get_name(), "Event Name not updated in table properly." );
		assert( $table_event->get_location() === $event->get_location(), "Event Location not updated in table properly." );

		$table_event_ids = $table_event->get_event_type_ids();
		$event_ids       = $event->get_event_type_ids();
		assert( count( $table_event_ids ) === count( $event_ids ), "Event event_type_ids not updated in table properly: counts don't match." );
		for ( $i = 0; $i < count( $table_event_ids ); $i++ ) {
			assert( in_array( $table_event_ids[ $i ], $event_ids ), "Event event_type_ids not updated in table properly: ids don't match." );
		}

		$table_csv = $table_event->get_event_type_ids_csv();
		assert( $table_csv === "42,44" || $table_csv === "44,42", "Event event_type_ids_csv is incorrect. csv:$table_csv" );

		$event_types = $table_event->get_event_types();
		if ( $event_types instanceof WP_Error ) {
			assert( false, "get_event_types failed. Error: " . $event_types->get_error_messages() );
		}

		assert( true === CAA_Task_Plugin_Event_Table::delete_event( $table_event ), "Event failed to delete." );
	}
}
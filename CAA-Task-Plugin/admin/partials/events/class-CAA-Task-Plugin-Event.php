<?php

/**
 * Defines the CAA_Task_Plugin_Events class that stores information about events.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */

/**
 * Imports the CAA_Task_Plugin_Event_Type_Table class so event types can be assigned to events.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'event-types/class-CAA-Task-Plugin-Event-Type-Table.php';

/**
 * Imports the CAA_Task_Plugin_Event_Table class so the table can be accessed to generate events.
 */
require_once plugin_dir_path( __FILE__ ) . 'class-CAA-Task-Plugin-Event-Table.php';

/**
 * Used to store information about events. Events are used to generate task lists in basecamp for the
 * event.
 * 
 * They can be retrieved/updated in the wordpress table, @see CAA_Task_Plugin_Event_Table.
 * 
 * Events contain the following information:
 * - ID: the id of the event in the SQL table
 * - name: the name of the event
 * - start date: the date the event will start
 * - end date: the date the event will end
 * - start time: the time the event will start
 * - end time: the time the event will end
 * - location: the location of the event
 * - event types: an array of event types that define the event's needs
 * 
 * @todo implement basecamp links, task list, etc.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Event {

	/**
	 * The id of the event in the SQL table.
	 * @var int
	 */
	private int $id;

	/**
	 * The name of the event that will be displayed to client.
	 * @var string
	 */
	private string $name;

	/**
	 * The start time of the event in unix timestamp.
	 * @var int
	 */
	private int $start_time_unix_timestamp;

	/**
	 * The end time of the event in unix timestamp.
	 * @var int
	 */
	private int $end_time_unix_timestamp;

	/**
	 * The location of the event.
	 * @var string
	 */
	private string $location;

	/**
	 * A list of the event type ids for the event.
	 * @var int[]
	 */
	private array $event_type_ids;

	/**
	 * The event types that this event is categorized under. These define the tasks that will be made
	 * for the event. Can be null.
	 * @var CAA_Task_Plugin_Event_Type[]
	 */
	private array|null $event_types;

	/**
	 * Constructs an empty event with the given id.
	 * 
	 * @param int $id is the id of the event in the database.
	 * @since 1.0.0
	 */
	function __construct( int $id ) {
		$this->id                        = $id;
		$this->name                      = "";
		$this->start_time_unix_timestamp = 0;
		$this->end_time_unix_timestamp   = 0;
		$this->location                  = "";
		$this->event_type_ids            = [];
	}


	/**
	 * The id of the event in the SQL table.
	 * @since 1.0.0
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * The name of the event that will be displayed to client.
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * The name of the event that will be displayed to client.
	 * @since 1.0.0
	 * @param string $name The name of the event that will be displayed to client.
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->name = $name;
		return $this;
	}

	/**
	 * The start time of the event in unix timestamp.
	 * @since 1.0.0
	 * @return int
	 */
	public function get_start_time_unix_timestamp(): int {
		return $this->start_time_unix_timestamp;
	}

	/**
	 * The start time of the event in unix timestamp.
	 * @since 1.0.0
	 * @param int $start_time_unix_timestamp The start time of the event in unix timestamp.
	 * @return self
	 */
	public function set_start_time_unix_timestamp( int $start_time_unix_timestamp ): self {
		$this->start_time_unix_timestamp = $start_time_unix_timestamp;
		return $this;
	}

	/**
	 * The end time of the event in unix timestamp.
	 * @since 1.0.0
	 * @return int
	 */
	public function get_end_time_unix_timestamp(): int {
		return $this->end_time_unix_timestamp;
	}

	/**
	 * The end time of the event in unix timestamp.
	 * @since 1.0.0
	 * @param int $end_time_unix_timestamp The end time of the event in unix timestamp.
	 * @return self
	 */
	public function set_end_time_unix_timestamp( int $end_time_unix_timestamp ): self {
		$this->end_time_unix_timestamp = $end_time_unix_timestamp;
		return $this;
	}

	/**
	 * The location of the event.
	 * @since 1.0.0
	 * @return string
	 */
	public function get_location(): string {
		return $this->location;
	}

	/**
	 * The location of the event.
	 * @since 1.0.0
	 * @param string $location The location of the event.
	 * @return self
	 */
	public function set_location( string $location ): self {
		$this->location = $location;
		return $this;
	}

	/**
	 * The event types that this event is categorized under. These define the tasks that will be made
	 * for the event.
	 * @since 1.0.0
	 * @return CAA_Task_Plugin_Event_Type[]|WP_Error
	 */
	public function get_event_types(): array|WP_Error {
		if ( ! isset( $this->event_types ) || empty( $this->event_types ) ) {
			$this->event_types = array();
			foreach ( $this->event_type_ids as $event_type_id ) {
				$event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( $event_type_id );
				if ( null === $event_type ) {
					return new WP_Error( "Failed to get event type from table", "ID: $event_type_id" );
				}
				array_push( $this->event_types, $event_type );
			}
		}
		return $this->event_types;
	}

	/**
	 * A list of the event type ids for the event.
	 * @return array
	 */
	public function get_event_type_ids(): array {
		return $this->event_type_ids;
	}

	/**
	 * Adds an event type to the event.
	 * 
	 * Assumes that the event type is a valid, complete event type in the table.
	 * @todo Implement method to check if event type is complete, so this can raise an error if it is not.
	 * @param CAA_Task_Plugin_Event_Type $event_type
	 * @return bool|WP_Error true if successful
	 * @since 1.0.0
	 */
	public function add_event_type( CAA_Task_Plugin_Event_Type $event_type ): bool|WP_Error {

		// check to make sure adding doesn't cause duplicates.
		$id = $event_type->get_id();
		if ( in_array( $id, $this->event_type_ids ) ) {
			return new WP_Error( "Duplicate Event Type", "This event already has event type with id: $id" );
		}

		if ( isset( $this->event_types ) ) {
			array_push( $this->event_types, $event_type );
		}

		array_push( $this->event_type_ids, $id );

		return true;

	}

	/**
	 * Removes an event type from the event. (removes the event type based on the id of $event_type)
	 * @param CAA_Task_Plugin_Event_Type $event_type_to_remove
	 * @return void
	 * @since 1.0.0
	 */
	public function remove_event_type( CAA_Task_Plugin_Event_Type $event_type_to_remove ) {

		// remove from event types
		if ( isset( $this->event_types ) ) {
			$this->event_types = array_filter(
				$this->event_types,
				function (CAA_Task_Plugin_Event_Type $event_type) use ($event_type_to_remove) {
					return ! ( $event_type->get_id() === $event_type_to_remove->get_id() );
				}
			);
		}

		// remove from event_type_ids
		$this->event_type_ids = array_filter(
			$this->event_type_ids,
			function (int $event_type_id) use ($event_type_to_remove) {
				return ! ( $event_type_id === $event_type_to_remove->get_id() );
			}
		);

	}

	/**
	 * Gets a list of event type ids of the event in csv format. This is how they are stored in the database.
	 * @return string
	 * @since 1.0.0
	 */
	public function get_event_type_ids_csv(): string {
		if ( empty( $this->event_type_ids ) ) {
			return "";
		} else {
			return implode( ",", array_map( 'strval', $this->event_type_ids ) );
		}
	}

	/**
	 * Sets the event types of the event using csv format (the format used by the database).
	 * @param string $event_type_ids_csv
	 * @return void
	 * @since 1.0.0
	 */
	public function set_event_type_ids_csv( string $event_type_ids_csv ) {

		if ( "" === $event_type_ids_csv ) {
			// handle empty case

			$this->event_type_ids = [];
			$this->event_types    = null;

		} else {
			// handle nonempty case

			// parse csv into int array
			$event_type_ids = array_map( function (int $event_type_id) {
				if ( is_numeric( $event_type_id ) ) {
					return intval( $event_type_id );
				}

				throw new Exception( "Incorrect format!" );

			}, explode( ',', $event_type_ids_csv ) );

			// check for duplicate values
			if ( count( $event_type_ids ) !== count( array_flip( $event_type_ids ) ) ) {
				throw new Exception( "Duplicate values found!" );
			}

			// set task definitions
			$this->event_type_ids = $event_type_ids;
			$this->event_types    = null;

		}
	}

	/**
	 * Gets event types that can be added to the event without being redundant.
	 * @return array
	 * @since 1.0.0
	 */
	public function get_addable_event_types(): array {
		$event_types         = CAA_Task_Plugin_Event_Type_Table::get_event_types();
		$used_event_types    = $this->get_event_types();
		$addable_event_types = array_filter(
			$event_types,
			function (CAA_Task_Plugin_Event_Type $event_type) use ($used_event_types) {
				foreach ( $used_event_types as $used_event_type ) {
					if ( !$used_event_type->excludes_subtype( $event_type ) || $used_event_type->get_id() === $event_type->get_id() ) {
						return false;
					}
				}
				return true;
			}
		);

		return $addable_event_types;
	}
}
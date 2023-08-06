<?php
/**
 * Adapted from https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/#extending-internal-classes
 * 
 * Registers the REST API endpoint for subtypes
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/api
 */

/**
 * Imports the CAA_Task_Plugin_Event_Table class so the table can be accessed to get events.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/events/class-CAA-Task-Plugin-Event-Table.php';

/**
 * Imports the CAA_Task_Plugin_Event_Type_Table class so the table can be accessed to generate event types and check for cycles.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/event-types/class-CAA-Task-Plugin-Event-Type-Table.php';

/**
 * Imports the CAA_Task_Plugin_API_Validation library for validation functions
 */
require_once plugin_dir_path( __FILE__ ) . 'class-CAA-Task-Plugin-API-Validation.php';

/**
 * Registers the REST API endpoint for event's event types
 *
 * @todo       Fully implement create, delete, update, etc
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/api
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Event_Event_Types_Rest_Controller extends WP_REST_Controller {

	/**
	 * Initialize the namespace and resource name.
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = "caataskplugin/v1";
		$this->rest_base = "events/(?P<event_id>[\d]+)/event-types";
	}

	/**
	 * register routes for subtypes
	 * 
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_SERVER::READABLE,
				'callback' => array( $this, 'get_event_types' ),
				'permissions_callback' => array( $this, 'get_event_types_permissions_check' ),
				'args' => array(
					'event_id' => array(
						'required' => true,
						'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_id_validation'
					),
					'addable' => array(
						'default' => 'false',
						'validate_callback' => 'CAA_Task_Plugin_API_Validation::bool_validation'
					),
					'removable' => array(
						'default' => 'true',
						'validate_callback' => 'CAA_Task_Plugin_API_Validation::bool_validation'
					)
				)
			),
			array(
				'methods' => WP_REST_SERVER::CREATABLE,
				'callback' => array( $this, 'create_event_type' ),
				'permissions_callback' => array( $this, 'create_event_type_permissions_check' ),
				'args' => array(
					'event_type_id' => array(
						'required' => true,
						'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
					),
					'event_id' => array(
						'required' => true,
						'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_id_validation'
					)
				)
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<event_type_id>[\d]+)', array(
			array(
				'methods' => WP_REST_SERVER::DELETABLE,
				'callback' => array( $this, 'delete_event_type' ),
				'permissions_callback' => array( $this, 'delete_event_type_permissions_check' ),
				'args' => array(
					'event_type_id' => array(
						'required' => true,
						'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
					),
					'event_id' => array(
						'required' => true,
						'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_id_validation'
					)
				)
			)
		) );
	}

	/**
	 * Gets all deleted and unfinished subtypes.
	 * 
	 * @param WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 * @since 1.0.0
	 */
	public function get_event_types( WP_REST_Request $request ): WP_Error|WP_REST_Response {

		$event = CAA_Task_Plugin_Event_Table::get_event( $request['event_id'] );
		if ( false === $event ) {
			return new WP_Error( '', "Could not get event " . $request['event_id'] );
		}

		$data = array();

		// 'removable' event types have already been assigned as an event type for the event
		if ( 'true' === $request['removable'] ) {

			$removable_event_types = $event->get_event_types();
			$removable_data        = array();

			foreach ( $removable_event_types as $event_type ) {
				$event_type_data  = $this->prepare_event_type_for_response( $event_type, $request );
				$removable_data[] = $this->prepare_response_for_collection( $event_type_data );
			}


			// if addable subtypes are required, add a key to access removable subtypes separately
			if ( 'true' === $request['addable'] ) {
				$data['removable'] = $removable_data;
			} else {
				$data = $removable_data;
			}
		}

		// 'addable' event types are all event types that aren't already in use by the event
		if ( 'true' === $request['addable'] ) {

			$addable_event_types = $event->get_addable_event_types();
			$addable_data        = array();

			foreach ( $addable_event_types as $event_type ) {
				$event_type_data = $this->prepare_event_type_for_response( $event_type, $request );
				$addable_data[]  = $this->prepare_response_for_collection( $event_type_data );
			}

			// if removable subtypes are required, add a key to access addable subtypes separately
			if ( 'true' === $request['removable'] ) {
				$data['addable'] = $addable_data;
			} else {
				$data = $addable_data;
			}
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Create a subtype for the event type
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_event_type( WP_REST_Request $request ): WP_Error|WP_REST_Response {

		$event_type = $this->prepare_event_type_for_database( $request );
		if ( false === $event_type ) {
			return new WP_Error( 'Could not get event type.', "ID: " . $request['event_type_id'] );
		}

		$event = CAA_Task_Plugin_Event_Table::get_event( $request['event_id'] );
		if ( false === $event_type ) {
			return new WP_Error( 'Could not get event.', "ID: " . $request['event_id'] );
		}

		$res = $event->add_event_type( $event_type );
		if ( false === $res ) {
			return new WP_Error( 'Failed to add event type', "Perhaps it is already being used by event?" );
		}

		$res = CAA_Task_Plugin_Event_Table::update_event( $event );
		if ( false === $res ) {
			return new WP_Error( 'Failed to update event', "" );
		}

		$location = $this->namespace . "\/event-types/" . $event_type->get_id();
		$data     = array(
			"status" => "success",
			"location" => $location,
			"event_type" => $this->prepare_response_for_collection( $this->prepare_event_type_for_response( $event_type, $request ) )
		);
		$headers  = array(
			"Location" => $location
		);
		return new WP_REST_Response( $data, 201, $headers );
	}

	/**
	 * Delete subtype from the subtypes
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_REST_Response
	 */
	public function delete_event_type( WP_REST_Request $request ): WP_REST_Response {

		$event_type_id = $request['event_type_id'];
		$event_id      = $request['event_id'];

		$event_type = $this->prepare_event_type_for_database( $request );
		if ( false === $event_type ) {
			if ( WP_DEBUG ) {
				error_log( "Could not get event type $event_type_id from database" );
			}
			return new WP_REST_Response( "Could not get subtype $event_type_id", 500 );
		}

		$event = CAA_Task_Plugin_Event_Table::get_event( $event_id );
		if ( false === $event ) {
			return new WP_REST_Response( "Could not get event $event_id", 500 );
		}

		$event->remove_event_type( $event_type );
		$res = CAA_Task_Plugin_Event_Table::update_event( $event );
		if ( false === $res ) {
			return new WP_REST_Response( "Failed to update event", 500 );
		}

		return new WP_REST_Response( "Successfully Deleted!", 200 );
	}

	/**
	 * Check if request has permissions to read event types.
	 * 
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_event_types_permissions_check( WP_REST_Request $request ): WP_Error|bool {
		return current_user_can( 'edit_dashboard' );
	}

	/**
	 * Check if a given request has access to create subtypes
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function create_event_type_permissions_check( WP_REST_Request $request ): WP_Error|bool {
		return current_user_can( 'edit_dashboard' );
	}

	/**
	 * Check if a given request has access to delete a specific subtype
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function delete_event_type_permissions_check( WP_REST_Request $request ): WP_Error|bool {
		return $this->create_event_type_permissions_check( $request );
	}

	/**
	 * Prepare the subtype for create or delete operation
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_Error|CAA_Task_Plugin_Event_Type $prepared_item
	 */
	protected function prepare_event_type_for_database( WP_REST_Request $request ): WP_Error|CAA_Task_Plugin_Event_Type {
		$event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( $request['event_type_id'] );
		if ( null === $event_type ) {
			return new WP_Error( 'Could not get Event Type' );
		}
		return $event_type;
	}


	/**
	 * Prepare event type for the REST response
	 * 
	 * @param CAA_Task_Plugin_Event_Type $event_type
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response json representation of event type
	 */
	public function prepare_event_type_for_response( CAA_Task_Plugin_Event_Type $event_type, WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response( array(
			"id" => $event_type->get_id(),
			"display_name" => $event_type->get_display_name(),
			"description" => $event_type->get_description(),
			"subtypes" => $event_type->get_subtype_ids(),
			"task_definitions" => $event_type->get_task_definition_ids()
		), 200 );
	}

}
<?php
/**
 * Adapted from https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/#extending-internal-classes
 * 
 * Registers the REST API endpoint for task definitions
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/api
 */


/**
 * Imports the CAA_Task_Plugin_Event_Type_Table class so the table can be accessed.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/event-types/class-CAA-Task-Plugin-Event-Type-Table.php';

/**
 * Imports the CAA_Task_Plugin_Task_Definition_Table to access the database.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/task-definitions/class-CAA-Task-Plugin-Task-Definition-Table.php';

/**
 * Imports the CAA_Task_Plugin_Task_Definition class
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/task-definitions/class-CAA-Task-Plugin-Task-Definition.php';

/**
 * Imports the CAA_Task_Plugin_API_Validation library for validation functions
 */
require_once plugin_dir_path( __FILE__ ) . 'class-CAA-Task-Plugin-API-Validation.php';

/**
 * Registers the REST API endpoint for subtypes
 *
 * @todo       Fully implement create, delete, update, etc
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/api
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Task_Definitions_Rest_Controller extends WP_REST_Controller {

	/**
	 * Initialize the namespace and resource name.
	 * 
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = "caataskplugin/v1";
		$this->rest_base = "event-types/(?P<event_type_id>[\d]+)/task-definitions";
	}

	/**
	 * register routes for subtypes
	 * 
	 * @todo    Add verification callbacks
	 * @since   1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_SERVER::READABLE,
				'callback' => array( $this, 'get_task_definitions' ),
				'permissions_callback' => array( $this, 'get_task_definitons_permissions_check' ),
				'args' => array(
					'event_type_id' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
					)
				)
			),
			array(
				'methods' => WP_REST_SERVER::CREATABLE,
				'callback' => array( $this, 'create_task_definition' ),
				'permissions_callback' => array( $this, 'create_task_definition_permissions_check' ),
				'args' => array(
					'event_type_id' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
					),
					'title' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::task_definition_title_validation'
					),
					'description' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::task_definition_description_validation'
					),
					'start_offset' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::task_definition_date_offset_validation'
					),
					'finish_offset' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::task_definition_date_offset_validation'
					)
				)
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<task_definition_id>[\d]+)', array(
			array(
				'methods' => WP_REST_SERVER::DELETABLE,
				'callback' => array( $this, 'delete_task_definition' ),
				'permissions_callback' => array( $this, 'delete_task_definition_permissions_check' ),
				'args' => array(
					'event_type_id' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
					),
					'task_definition_id' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::task_definition_id_validation'
					)
				)
			),
			array(
				'methods' => WP_REST_SERVER::EDITABLE,
				'callback' => array( $this, 'edit_task_definition' ),
				'permissions_callback' => array( $this, 'edit_task_permissions_check' ),
				'args' => array(
					'event_type_id' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
					),
					'task_definition_id' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::task_definition_id_validation'
					)
				)
			),
			array(
				'methods' => WP_REST_SERVER::READABLE,
				'callback' => array( $this, 'get_task_definition' ),
				'permissions_callback' => array( $this, 'get_task_permissions_check' ),
				'args' => array(
					'event_type_id' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
					),
					'task_definition_id' => array(
						'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::task_definition_id_validation'
					)
				)
			)
		) );
	}

    /**
	 * Gets all task definitions attached to the event type.
	 * 
	 * @since 1.0.0
	 * 
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_task_definitions( WP_REST_Request $request ): WP_Error|WP_REST_Response {

		$event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( $request['event_type_id'] );
		if ( ! $event_type ) {
			return new WP_Error( '', "Could not get event type " . $request['event_type_id'] );
		}
		$task_definitions = $event_type->get_task_definitions();

		$data = array();
		foreach ( $task_definitions as $task_definition ) {
			$task_definition_data = $this->prepare_task_definition_for_response( $task_definition, $request );
			$data[]               = $this->prepare_response_for_collection( $task_definition_data );
		}
		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Gets a task definition attached to the event type.
	 * 
	 * @since 1.0.0
	 * 
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_task_definition( WP_REST_Request $request ): WP_Error|WP_REST_Response {

		$task_definition = CAA_Task_Plugin_Task_Definition_Table::get_task_definition( $request["task_definition_id"] );
		$data            = $this->prepare_task_definition_for_response( $task_definition, $request );
		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Create and add task definition to event type
	 * 
	 * @since   1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_task_definition( WP_REST_Request $request ): WP_Error|WP_REST_Response {

        $event_type_id      = intval( $request['event_type_id'] );
		$task_definition = new CAA_Task_Plugin_Task_Definition(
			$request["title"],
			intval( $request["start_offset"] ),
			intval( $request["finish_offset"] ),
			$request["description"]
		);

		if ( ! CAA_Task_Plugin_Task_Definition_Table::add_task_definition( $task_definition ) ) {
			return new WP_Error( '', 'Could not create task definition' );
		}
		$task_definition_id = $task_definition->get_id();

		$event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( $event_type_id );
		if ( false === $event_type ) {
			return new WP_Error( '', "Could not get event type $event_type_id" );
		}
		$res = $event_type->add_task_definition( $task_definition );
        if ( false === $res ){
			return new WP_Error( '', 'Could not add task definition ' );
        }

        
		CAA_Task_Plugin_Event_Type_Table::update_event_type( $event_type );
		if ( ! CAA_Task_Plugin_Event_Type_Table::update_event_type( $event_type ) ) {
			return new WP_Error( '', "Could not update event type $event_type_id" );
		}

        $data    = array(
			"status" => "success",
			"location" => $this->namespace . "/event-types/$event_type_id/task-definitions/$task_definition_id",
			"task_definition" => $this->prepare_task_definition_for_response( $task_definition, $request )
		);
		$headers = array(
			"Location" => $this->namespace . "/event-types/$event_type_id/task-definitions/$task_definition_id"
		);
		return new WP_REST_Response( $data, 201, $headers );
	}

	/**
	 * Delete the task definition from the event type
	 * 
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_task_definition( WP_REST_Request $request ): WP_Error|WP_REST_Response {

		$task_definition = CAA_Task_Plugin_Task_Definition_Table::get_task_definition( intval( $request["task_definition_id"] ) );
		if ( ! $task_definition ) {
			return new WP_Error( '', 'Could not get task definition' . $request['task_definition_id'] );
		}

		$event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( $request['event_type_id'] );
		if ( false === $event_type ) {
			return new WP_Error( '', 'Could not get event type ' . $request['event_type_id'] );
		}
		$event_type->remove_task_definition( $task_definition );
		if ( ! CAA_Task_Plugin_Event_Type_Table::update_event_type( $event_type ) ) {
			return new WP_Error( '', 'Could not update event type ' . $request['event_type_id'] );
		}

		return new WP_REST_Response( "Successfully deleted", 200 );
	}

	/**
	 * Edit the task definition
	 * 
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function edit_task_definition( WP_REST_Request $request ): WP_Error|WP_REST_Response {

		$event_type_id      = intval( $request['event_type_id'] );
		$task_definition_id = intval( $request['task_definition_id'] );

		$updated_task_definition = new CAA_Task_Plugin_Task_Definition(
			$request["title"],
			intval( $request["start_offset"] ),
			intval( $request["finish_offset"] ),
			$request["description"]
		);
		$updated_task_definition->set_id( $task_definition_id );
		if ( ! CAA_Task_Plugin_Task_Definition_Table::update_task_definition( $updated_task_definition ) ) {
			return new WP_Error( '', "Cound not edit task definition $task_definition_id" );
		}

		$data    = array(
			"status" => "success",
			"location" => $this->namespace . "/event-types/$event_type_id/task-definitions/$task_definition_id",
			"task_definition" => $this->prepare_task_definition_for_response( $updated_task_definition, $request )
		);
		$headers = array(
			"Location" => $this->namespace . "/event-types/$event_type_id/task-definitions/$task_definition_id"
		);
		return new WP_REST_Response( $data, 201, $headers );
	}

	/**
	 * Check if request has permissions to read task definitions.
	 * 
	 * @since 1.0.0
	 * 
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_task_definitions_permissions_check( WP_REST_Request $request ): WP_Error|bool {
		return $this->create_task_definition_permissions_check( $request );
	}

	/**
	 * Check if request has permissions to read a specific task definition.
	 * 
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_task_definition_permissions_check( WP_REST_Request $request ): WP_Error|bool {
		return $this->create_task_definition_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to create task definitions
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function create_task_definition_permissions_check( WP_Rest_Request $request ): WP_Error|bool {
		return current_user_can( 'edit_dashboard' );
	}

	/**
	 * Check if a given request has access to delete a specific task definition
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function delete_task_definition_permissions_check( WP_Rest_Request $request ): WP_Error|bool {
		return $this->create_task_definition_permissions_check( $request );
	}

	/**
	 * Checks if a given request has access to edit task definition.
	 * @param WP_REST_Request $request
	 * @return WP_Error|bool
	 */
	public function edit_task_definition_permissions_check( WP_REST_Request $request ): WP_Error|bool {
		return $this->create_task_definition_permissions_check( $request );
	}

	/**
	 * Prepare the subtype for create or delete operation
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_Error|array $prepared_item
	 */
	protected function prepare_task_definition_for_database( WP_REST_Request $request ): WP_Error|array {
		return array();
	}


	/**
	 * Prepare event type for the REST response
	 * 
	 * @param CAA_Task_Plugin_Task_Definition $event_type
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error json representation of event type
	 */
	public function prepare_task_definition_for_response( CAA_Task_Plugin_Task_Definition $task_definition, WP_REST_Request $request ): WP_REST_Response|WP_Error {
		return new WP_REST_Response( array(
				"id" => $task_definition->get_id(),
				"title" => $task_definition->get_title(),
				"description" => $task_definition->get_description(),
				"start_offset_in_days" => $task_definition->get_start_offset_in_days(),
				"finish_offset_in_days" => $task_definition->get_finish_offset_in_days()
        ), 200 );
	}

}
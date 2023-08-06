<?php
/**
 * Adapted from https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/#extending-internal-classes
 * 
 * Registers the REST API endpoint for event types
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
 * Imports the CAA_Task_Plugin_API_Validation library for validation functions
 */
require_once plugin_dir_path( __FILE__ ) . 'class-CAA-Task-Plugin-API-Validation.php';

/**
 * Registers the REST API endpoint for event types.
 *
 * @todo       Fully implement create, delete, update, etc
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/api
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Event_Rest_Controller extends WP_REST_Controller {

	/**
	 * Initialize the namespace and resource name.
	 */
	public function __construct() {
		$this->namespace = "caataskplugin/v1";
		$this->rest_base = "events";
	}

	/**
	 * register routes for event types
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods' => WP_REST_SERVER::READABLE,
			'callback' => array( $this, 'get_event_types' ),
			'permissions_callback' => array( $this, 'get_event_types_permissions_check' ),
		) );

	}

	/**
	 * Gets all undeleted and finished events.
	 * 
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_event_types( WP_REST_Request $request ): WP_Error|WP_REST_Response {

		$events = CAA_Task_Plugin_Event_Table::get_events();
		$data   = array();
		if ( false === $events ) {
			return new WP_Error( '', "Could not get event type " );
		}

		foreach ( $events as $event ) {
			$event_data = $this->prepare_event_type_for_response( $event, $request );
			$data[]     = $this->prepare_response_for_collection( $event_data );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Check if request has permissions to read events.
	 * 
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool
	 */
	public function get_event_types_permissions_check( WP_REST_Request $request ): bool {
		return current_user_can( 'edit_dashboard' );
	}

	/**
	 * Prepare event for the REST response
	 * 
	 * @param CAA_Task_Plugin_Event $event
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response json representation of event type
	 */
	public function prepare_event_type_for_response( CAA_Task_Plugin_Event $event, WP_REST_Request $request ): WP_REST_Response {
		$data = array(
			"id" => $event->get_id(),
			"name" => $event->get_name(),
			"location" => $event->get_location(),
			"start_time" => date( DATE_ISO8601, $event->get_start_time_unix_timestamp() ),
			"end_time" => date( DATE_ISO8601, $event->get_end_time_unix_timestamp() ),
			"event_types" => $event->get_event_type_ids()
		);
		return new WP_REST_Response( $data, 200 );
	}
}
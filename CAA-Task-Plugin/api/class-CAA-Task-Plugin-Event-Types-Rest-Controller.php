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
 * Imports the CAA_Task_Plugin_Event_Type_Table class so the table can be accessed to generate event types and check for cycles.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/event-types/class-CAA-Task-Plugin-Event-Type-Table.php';

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
class CAA_Task_Plugin_Event_Types_Rest_Controller extends WP_REST_Controller {

    /**
     * Initialize the namespace and resource name.
     */
    public function __construct(){
        $this->namespace = "caataskplugin/v1";
        $this->rest_base = "event-types";
    }

    /**
     * register routes for event types
     */
    public function register_routes(){
        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => array( $this, 'get_event_types' ),
            'permissions_callback' => array( $this, 'get_event_types_permissions_check' ),
        ) );
    }

    /**
     * Gets all undeleted and finished event types.
     * 
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_event_types( WP_REST_Request $request ): WP_Error|WP_REST_Response{

        $event_types = CAA_Task_Plugin_Event_Type_Table::get_event_types();
        $data = array();
        if ( false === $event_types ) {
            return new WP_Error( '', "Could not get event type " );
        }

        foreach( $event_types as $event_type ){
            $event_type_data = $this->prepare_event_type_for_response( $event_type, $request );
            $data[] = $this->prepare_response_for_collection( $event_type_data );
        }

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Check if request has permissions to read event types.
     * 
     * @param WP_REST_Request $request Full data about the request.
     * @return bool
     */
    public function get_event_types_permissions_check( WP_REST_Request $request ): bool {
        return current_user_can( 'edit_dashboard' );
    }

    /**
     * Prepare event type for the REST response
     * 
     * @param CAA_Task_Plugin_Event_Type $event_type
     * @param WP_REST_Request $request
     * @return WP_REST_Response json representation of event type
     */
    public function prepare_event_type_for_response( CAA_Task_Plugin_Event_Type $event_type, WP_REST_Request $request ): WP_REST_Response {
        $data =  array(
            "id" => $event_type->get_id(),
            "display_name" => $event_type->get_display_name(),
            "description" => $event_type->get_description(),
            "subtypes" => $event_type->get_subtype_ids(),
            "task_definitions" => $event_type->get_task_definition_ids()
        );
		return new WP_REST_Response( $data, 200 );
    }
}
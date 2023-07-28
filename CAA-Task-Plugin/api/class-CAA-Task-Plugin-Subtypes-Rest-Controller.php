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
 * Imports the CAA_Task_Plugin_Event_Type_Table class so the table can be accessed to generate event types and check for cycles.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/class-CAA-Task-Plugin-Event-Type-Table.php';

/**
 * Imports the CAA_Task_Plugin_API_Validation library for validation functions
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-CAA-Task-Plugin-API-Validation.php';

 /**
 * Registers the REST API endpoint for subtypes
 *
 * @todo       Fully implement create, delete, update, etc
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/api
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Subtypes_Rest_Controller extends WP_REST_Controller {

    /**
     * Initialize the namespace and resource name.
     * 
     * @since 1.0.0
     */
    public function __construct(){
        $this->namespace = "caataskplugin/v1";
        $this->rest_base = "event-types/(?P<event_type_id>[\d]+)/subtypes";
    }

    /**
     * register routes for subtypes
     * 
     * @since 1.0.0
     */
    public function register_routes(){
        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_SERVER::READABLE,
                'callback' => array( $this, 'get_subtypes' ),
                'permissions_callback' => array( $this, 'get_subtypes_permissions_check' ),
                'args' => array(
                    'event_type_id' => array(
                        'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
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
                'callback' => array( $this, 'create_subtype' ),
                'permissions_callback' => array( $this, 'create_subtype_permissions_check' ),
                'args' => array(
                    'event_type_id' => array(
                        'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
                    ),
                    'subtype_id' => array(
                        'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
                    )
                )
            ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<subtype_id>[\d]+)', array(
            array(
                'methods' => WP_REST_SERVER::DELETABLE,
                'callback' => array( $this, 'delete_subtype' ),
                'permissions_callback' => array( $this, 'delete_subtype_permissions_check' ),
                'args' => array(
                    'event_type_id' => array(
                        'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
                    ),
                    'subtype_id' => array(
                        'required' => true,
                        'validate_callback' => 'CAA_Task_Plugin_API_Validation::event_type_id_validation'
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
    public function get_subtypes( WP_REST_Request $request ): WP_Error|WP_REST_Response {

        $main_event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( $request['event_type_id'] );
        if ( false === $main_event_type ) {
            return new WP_Error( '', "Could not get event type " . $request['event_type_id'] );
        }

        $data = array();

        // 'removable' subtypes are subtypes that already exist as a subtype of the event type
        if ( 'true' === $request['removable'] ){

            $removable_event_types = $main_event_type->get_subtypes();
            $removable_data = array();

            foreach( $removable_event_types as $event_type ){
                $event_type_data = $this->prepare_subtype_for_response( $event_type, $request );
                $removable_data[] = $this->prepare_response_for_collection( $event_type_data );
            }


            // if addable subtypes are required, add a key to access removable subtypes separately
            if ( 'true' === $request['addable'] ){
                $data['removable'] = $removable_data;
            } else {
                $data = $removable_data;
            }
        }

        // 'addable' subtypes are subtypes that can be added as a subtype to the event type
        if ( 'true' === $request['addable'] ){

            $addable_event_types = $main_event_type->get_addable_event_types();
            $addable_data = array();

            foreach( $addable_event_types as $event_type ){
                $event_type_data = $this->prepare_subtype_for_response( $event_type, $request );
                $addable_data[] = $this->prepare_response_for_collection( $event_type_data );
            }

            // if removable subtypes are required, add a key to access addable subtypes separately
            if ( 'true' === $request['removable'] ){
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
    public function create_subtype( WP_REST_Request $request ): WP_Error|WP_REST_Response {

        $subtype = $this->prepare_subtype_for_database( $request );
        if ( false === $subtype ) {
            return new WP_Error( '', "Could not get subtype " . $request['subtype_id'] );
        }

        $event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( $request['event_type_id'] );
        if ( false === $event_type ) {
            return new WP_Error( '', "Could not get event type " . $request['event_type_id'] );
        }

        $res = $event_type->add_subtype( $subtype );
        if ( false === $res ) {
            return new WP_Error( '', "Failed to add subtype, perhaps it caused a cycle or made a duplicate?");
        }

        $res = CAA_Task_Plugin_Event_Type_Table::update_event_type( $event_type );
        if ( false === $res ) {
            return new WP_Error( '', "Failed to update event type");
        }

        $location = $this->namespace . "\/event-types/" . $subtype->get_id();
        $data    = array(
			"status" => "success",
			"location" => $location,
			"task_definition" => $this->prepare_response_for_collection( $this->prepare_subtype_for_response( $event_type, $request ) )
		);
		$headers = array(
			"Location" => $location
		);
        return new WP_REST_Response( $data, 201, $headers);
    }

    /**
     * Delete subtype from the subtypes
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function delete_subtype( WP_REST_Request $request ): WP_REST_Response {

        $subtype_id = $request['subtype_id'];
        $event_type_id = $request['event_type_id'];

        $subtype = $this->prepare_subtype_for_database( $request );
        if ( false === $subtype ) {
            if ( WP_DEBUG ){
                error_log("Could not get subtype $subtype from database");
            }
			return new WP_REST_Response( "Could not get subtype $subtype_id", 500 );
        }

        $event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( $event_type_id );
        if ( false === $event_type ) {
            return new WP_REST_Response( "Could not get event type $event_type_id", 500 );
        }

        $event_type->remove_subtype( $subtype );
        $res = CAA_Task_Plugin_Event_Type_Table::update_event_type( $event_type );
        if ( false === $res ) {
            return new WP_REST_Response( "Failed to update event type", 500 );
        }

        return new WP_REST_Response( "Successfully Deleted!", 200);
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
    public function create_subtype_permissions_check( WP_REST_Request $request ): WP_Error|bool {
        return current_user_can( 'edit_dashboard' );
    }

    /**
     * Check if a given request has access to delete a specific subtype
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function delete_subtype_permissions_check( WP_REST_Request $request ): WP_Error|bool {
        return $this->create_subtype_permissions_check( $request );
    }

    /**
     * Prepare the subtype for create or delete operation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_Error|CAA_Task_Plugin_Event_Type $prepared_item
     */
    protected function prepare_subtype_for_database( WP_REST_Request $request ): WP_Error|CAA_Task_Plugin_Event_Type {
        $event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( $request['subtype_id'] );
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
    public function prepare_subtype_for_response( CAA_Task_Plugin_Event_Type $sub_type, WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response( array(
			"id" => $sub_type->get_id(),
			"display_name" => $sub_type->get_display_name(),
			"description" => $sub_type->get_description(),
			"subtypes" => $sub_type->get_subtype_ids(),
			"task_definitions" => $sub_type->get_task_definition_ids()
        ), 200 );
    }

}
<?php
/**
 * Registers the REST API routes
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/api
 */

 /**
 * Imports the CAA_Task_Plugin_Event_Types_Rest_Controller class
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-CAA-Task-Plugin-Event-Types-Rest-Controller.php';

 /**
 * Imports the CAA_Task_Plugin_Subtypes_Rest_Controller class
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-CAA-Task-Plugin-Subtypes-Rest-Controller.php';

 /**
 * Imports the CAA_Task_Plugin_Task_Definitions_Rest_Controller class
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-CAA-Task-Plugin-Task-Definitions-Rest-Controller.php';

 /**
 * Registers the REST API routes
 *
 * @todo       Fully implement create, delete, update, etc
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/api
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Rest_API {

    /**
     * Registers all routes for the API
     */
    public function register_routes(){

        $event_types_controller = new CAA_Task_Plugin_Event_Types_Rest_Controller();
        $event_types_controller->register_routes();

        $subtypes_controller = new CAA_Task_Plugin_Subtypes_Rest_Controller();
        $subtypes_controller->register_routes();

        $task_definitions_controller = new CAA_Task_Plugin_Task_Definitions_Rest_Controller();
        $task_definitions_controller->register_routes();
    }
    
} 
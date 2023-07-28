<?php

 /**
 * The class responsible for displaying event types
 */
require_once plugin_dir_path( __FILE__ ) . 'class-CAA-Task-Plugin-Event-Type-Display.php';

/**
 * The class responsible for managing event types in the wordpress table.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'event-types/class-CAA-Task-Plugin-Event-Type-Table.php';

/**
 * The class responsible for representing event types.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'event-types/class-CAA-Task-Plugin-Event-Type.php';

class CAA_Task_Plugin_Event_Type_Controller {


    private static function generate_new_query_url( $new_query_string ){
        $old_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $old_query_string = $_SERVER['QUERY_STRING'];
        if ( $new_query_string === $old_query_string ) {
            return null;
        } else {
            return str_replace( $old_query_string, $new_query_string, $old_url );
        }
        
    }
    /**
	 * Display the html for the page linked to the 'Create Event Tasks' admin submenu.
	 * 
	 * @since 1.0.0
	 */
	public static function on_load() {

        if ( 'GET' === $_SERVER['REQUEST_METHOD'] ) {

            if ( isset( $_GET['new-event-type'] ) ) {

                $event_type = CAA_Task_Plugin_Event_Type_Table::create_event_type();
                $id = $event_type->get_id();
                $new_query_string = 'page=' . $_GET['page'] . "&action=edit" . "&event-type-id={$id}";
    
                wp_redirect( self::generate_new_query_url( $new_query_string ) );

            }
        }
	}

	/**
	 * Display the html for the page linked to the 'Create Event Tasks' admin submenu.
	 * 
	 * @since 1.0.0
	 */
	public static function draw_page() {
        if ( 'GET' === $_SERVER['REQUEST_METHOD' ]) {

            if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) {
                CAA_Task_Plugin_Event_Type_Display::display_edit_page( intval( $_GET['event-type-id'] ) );
            } else {
                CAA_Task_Plugin_Event_Type_Display::display_main_page();
            }

        } else if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
            if ( 'save' === $_POST['action'] ) {

                // save event type
                $success = true;
                $updated_event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( intval( $_POST['event-type-id'] ) );
                $updated_event_type->set_display_name( strval( $_POST['display_name'] ) );
                $updated_event_type->set_description( strval( $_POST['description'] ) );
                if ( ! CAA_Task_Plugin_Event_Type_Table::update_event_type( $updated_event_type ) ) {
                    $success = false;
                }
                if ( ! CAA_Task_Plugin_Event_Type_Table::finish_event_type( $updated_event_type ) ) {
                    $success = false;
                }

                CAA_Task_Plugin_Event_Type_Display::display_main_page( );

            } else if ( 'delete' === $_POST['action'] ) {

                // delete event type
                $deleted_event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( intval( $_POST['event-type-id'] ) );
                CAA_Task_Plugin_Event_Type_Table::delete_event_type( $deleted_event_type );
                

                $notification = "";
                CAA_Task_Plugin_Event_Type_Display::display_main_page( $notification );

            } else {
                CAA_Task_Plugin_Event_Type_Display::display_main_page();
            }
        }
	}
}
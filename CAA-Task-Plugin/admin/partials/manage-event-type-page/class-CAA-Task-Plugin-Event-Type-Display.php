<?php

/**
 * Responsible for displaying event types to the client.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin/includes
 */

/**
 * The class responsible for retrieving event types from the wordpress table
 */
require_once plugin_dir_path(  dirname( __FILE__ ) ) . 'event-types/class-CAA-Task-Plugin-Event-Type-Table.php';


/**
 * Responsible for displaying event types to the client.
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Event_Type_Display {

    /**
     * Draws a button that will open up the page to create new event types.
     * 
     * @since   1.0.0
     * @param   string  $redirect   is the uri the button should redirect to.
     */
    public static function draw_new_event_type_button( string $redirect ) {
        ?>
        <button onclick="<?php echo "window.location.href='" . esc_url( $redirect ) . "'" ?>">Add Event Type</button>
        <?php
    }

    public static function display_edit_page( int $id ) {
        $event_type = CAA_Task_Plugin_Event_Type_Table::get_event_type( $id );
        $REMOVABLE_CONTAINER_ID = "removable-subtypes-container";
		$ADDABLE_CONTAINER_ID   = "addable-subtypes-container";
		$TASK_DEFINITION_CONTAINER_ID = "task-definition-container";

        if ( isset( $event_type ) ) {
        ?>
        <div class="wrap">
            <h2>Edit Event Type</h2>
            <form method="post" action=<?php echo esc_attr( "?page=" . $_GET['page'] ) ?>>

                <input type="number" name="event-type-id" value="<?php echo $event_type->get_id() ?>" hidden>

                <div style="display:flex;">

                    <div style="flex-grow:1;">
                        <h4>General Settings</h4>
                        <label>Display Name:</label><br>
                        <input type="text" name="display_name" value="<?php echo esc_attr( $event_type->get_display_name() ); ?>"><br>

                        <label>Description:</label><br>
                        <textarea name="description" maxLength="21845" cols="70" rows="10"><?php echo esc_html( $event_type->get_description() ); ?></textarea><br>
                    </div>

                    <div id="subtype-editor" style="flex-grow:1;">
                        <h4>Event Types</h4>
                        <div id="<?php echo $REMOVABLE_CONTAINER_ID?>"></div>
                        <div id="<?php echo $ADDABLE_CONTAINER_ID?>" style="height:30%;"></div>
                    </div>

                    <div id="task-definition-editor" style="flex-grow:1;">
                        <h4>Task Definitions</h4>
                        <div id="<?php echo $TASK_DEFINITION_CONTAINER_ID?>" style="height:30%;"></div>
                        <button type="button" onclick="Task_Definition_Editor.draw()">Create Task Definition</button>
                    </div>

                    <script>
                        jQuery(document).ready( async function(){
                            Event_Type_Editor_Page.set_container_ids("<?php echo $ADDABLE_CONTAINER_ID?>", "<?php echo $REMOVABLE_CONTAINER_ID?>", "<?php echo $TASK_DEFINITION_CONTAINER_ID?>");
                            Event_Type_Editor_Page.draw();
                        } );
                    </script>
                </div>
                
                <button type="submit" name="action" value="delete">Delete</button>
                <button type="submit" name="action" value="save">Save</button>
            </form>
        </div>
        <?php
        } else {
            ?>
            <div class="wrap">
                <p>Failed to retrieve event type!</p>
            </div>
            <?php
        }
    }

    public static function display_main_page( $notification=null ) {
		$EVENT_TYPE_CONTAINER_ID = "event-type-container";
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <?php self::draw_new_event_type_button( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '&new-event-type' ); ?>
            <div id="<?php echo $EVENT_TYPE_CONTAINER_ID ?>"></div>
        </div>
        <script>
            window.onload = async function(){
                Event_Type_Manager_Page.set_event_type_container_id("<?php echo $EVENT_TYPE_CONTAINER_ID ?>");
                Event_Type_Manager_Page.draw_event_type_table();
            };
        </script>
        <?php
    }

}
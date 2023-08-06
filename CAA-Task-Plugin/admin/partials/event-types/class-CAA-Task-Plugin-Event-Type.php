<?php

/**
 * Defines the CAA_Task_Plugin_Event_Type class that stores information about event types.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */

/**
 * Imports the CAA_Task_Plugin_Task_Definition_Table class so task definitions can be retrieved from the table.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'task-definitions/class-CAA-Task-Plugin-Task-Definition-Table.php';

/**
 * Imports the CAA_Task_Plugin_Event_Type_Table class so the table can be accessed to generate event types and check for cycles.
 */
require_once plugin_dir_path( __FILE__ ) . 'class-CAA-Task-Plugin-Event-Type-Table.php';

/**
 * Used to store information about event types. Event types are used to categorize events and define 
 * the tasks necessary for the event.
 * 
 * They can be retrieved/updated in the wordpress table, @see CAA_Task_Plugin_Event_Type_Table.
 * 
 * To create an event type, it must be constructed with a table entry ID. Then it can be given
 * a display name, task definitions, and subtypes.
 *
 * Information includes:
 * - ID (sql table ID)
 * - display_name
 * - subtypes
 * - task definitions
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Event_Type {

    /**
     * The ID of the task definition.
     * 
     * @since 1.0.0
     * @var   int     The ID of the Task Definition in the wordpress table.
     */
    private int $id;

    /**
     * The name the event type is displayed as.
     * 
     * @since 1.0.0
     * @var   string  The display name of the event type.
     */
    private string $display_name;

    /**
     * The description for the event type visible to the client.
     * 
     * @since   1.0.0
     * @var     string  The description of the event type.
     */
    private string $description;

    /**
     * The subtype ids of the event type.
     * 
     * Subtypes are event types that are inherited. 
     * This should never contain a cycle where a subtype points to any parent or self. It should also contain
     * the same IDs as are found in $this->subtypes at all time.
     * 
     * @since 1.0.0
     * @var   array an int array of the subtype ids
     */
    private array $subtype_ids;

    /**
     * The subtypes of the event type.
     * 
     * Subtypes are event types that are inherited. This variable is used to cache retrieval of subtypes.
     * This should never contain a cycle where a subtype points to any parent or self. It should also contain
     * the same IDs as are found in $this->subtype_ids at all time.
     * 
     * @since 1.0.0
     * @var     array A CAA_Task_Plugin_Event_Type array of subtypes or null if it hasn't been generated yet.
     */
    private array|null $subtypes;

    /**
     * The task definition ids of the event type.
     * 
     * @since 1.0.0
     * @var   array  An int array of the task definition ids
     */
    private array $task_definition_ids;

    /**
     * The subtypes of the event type.
     * 
     * Subtypes are event types that are inherited. This should never contain a cycle where a
     * subtype points to any parent or self.
     * This variable is used to cache retrieval of subtypes.
     * 
     * @since 1.0.0
     * @var     array A CAA_Task_Plugin_Event_Type array of subtypes or null if it hasn't been generated yet.
     */
    private array|null $task_definitions;

    /**
     * Constructs an instance of CAA_Task_Plugin_Task_Definition with id defined, and all other values
     * default.
     * 
     * @see the following for how to set the rest of the values: set_display_name, set_subtypes_csv, set_task_definitions_csv
     * 
     * @since   1.0.0
     * @param   int     $id     The ID of the Task Definition in the wordpress table.
     */
    function __construct( 
        int $id
    ) {
        $this->id = $id;
        $this->display_name = '';
        $this->subtype_ids = [];
        $this->subtypes = null;
        $this->task_definition_ids = [];
        $this->task_definitions = null;
        $this->description = '';
    }

    /**
     * Gets the ID of the event type.
     * 
     * @since 1.0.0
     * @return   int     The ID of the Event Type in the wordpress table.
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Gets the display name of the event type.
     * 
     * @since 1.0.0
     * @return   string  The display naem of the event type.
     */
    public function get_display_name() {
        return $this->display_name;
    }

    /**
     * Sets the display name of the event type.
     * 
     * @since   1.0.0
     * @param   string  $display_name is the name of the event type displayed to the client.
     */
    public function set_display_name( string $display_name ) {
        $this->display_name = $display_name;
    }

    /**
     * Gets the client-visible description for the event type.
     * 
     * @since 1.0.0
     * @return   string  The description of the event type.
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Sets the client-visible description of the event type.
     * 
     * @since   1.0.0
     * @param   string  $description is the description of the event type.
     */
    public function set_description( string $description ) {
        $this->description = $description;
    }

    /**
     * Gets an array of the subtype ids.
     * 
     * @since   1.0.0
     * @return  array   An array of integers, representing the event type ids.
     */
    public function get_subtype_ids(): array {
        return $this->subtype_ids;
    }

    /**
     * Gets a comma separated string of subtype ids.
     * 
     * @since   1.0.0
     * @return  string  A comma separated string of event type ids. e.g. "1,2,4,5" or "" if empty.
     */
    public function get_subtype_ids_csv(): string {
        if ( empty( $this->subtype_ids ) ) {
            return "";
        } else {
            return implode( ",", array_map( 'strval', $this->subtype_ids ) );
        }
    }

    public function excludes_subtype( $event_type ){
        $rec_subtype_ids = $this->get_subtype_ids_recursive();
        foreach ( $rec_subtype_ids as $subtype_id ){
            if( $event_type->get_id() === $subtype_id ){
                return false;
            }
        }
        return true;
    }

    public function append_to_causes_no_cycle( $subtype ){
        return ! $this->append_causes_cycle( $subtype );
    }

    public function get_addable_event_types(){
        $addable_subtypes = CAA_Task_Plugin_Event_Type_Table::get_event_types();

        // filter out subtypes that are already included
        $addable_subtypes = array_filter( $addable_subtypes, array($this, 'excludes_subtype' ) );
        
        $parent_event_type = $this;
        // filter out subtypes that would cause a cycle
        $addable_subtypes = array_filter( $addable_subtypes, array( $this, 'append_to_causes_no_cycle' ) );

        return $addable_subtypes;
    }

    /**
     * Gets an array of subtypes.
     * 
     * @since   1.0.0
     * @return  array   An array of CAA_Task_Plugin_Event_Type objects.
     */
    public function get_subtypes(): array {

        if ( ! isset( $this->subtypes ) || empty( $this->subtypes ) ) {

            $this->subtypes = array_map( 'CAA_Task_Plugin_Event_Type_Table::get_event_type', $this->subtype_ids );
        }
        return $this->subtypes;
    }

    /**
     * Sets the subtypes using a comma separated string of event type ids.
     * 
     * This method ensures no cycles are created in the subtypes.
     * Raises exceptions for incorrect format, duplicate values, and cycles.
     * 
     * @since   1.0.0
     * @param   string  $subtype_ids_csv is a comma separated string of event type ids. e.g. "1,2,4,5" or "" if none.
     * @todo validate that ids exist in table.
     */
    public function set_subtypes_csv( $subtype_ids_csv ) {

        if ( "" === $subtype_ids_csv ) {
            // handle empty case
            
            $this->subtype_ids = [];
            $this->subtypes = null;

        } else {
            // handle nonempty case

            $subtypes = null;

            // parse csv to int array
            $subtype_ids = array_map( function( $subtype_id ) {
                if ( is_numeric( $subtype_id ) ) {
                    return intval( $subtype_id );
                }

                throw new Exception( "Incorrect format!" );

            }, explode( ',', $subtype_ids_csv ) );

            // check for duplicate values at top level.
            if ( count( $subtype_ids ) !== count( array_flip( $subtype_ids ) ) ) {
                throw new Exception( "Duplicate values found!" );
            }

            // check for cycles
            foreach( $this->get_subtypes() as $subtype ) {
                if ( $this->append_causes_cycle( $subtype ) ) {
                    throw new Exception( "Cycle found!" );
                }
            }

            // set the subtypes ids
            $this->subtype_ids = $subtype_ids;
        }
    }

    /**
     * Adds a subtype.
     * 
     * This method ensures no cycles. It also ensures no duplicates at top level.
     * 
     * Subtype should be completely filled.
     * 
     * @since   1.0.0
     * @param   CAA_Task_Plugin_Event_Type $subtype is the event type that should be added as a subtype
     * @return  boolean True if adding the subtype was successful, false if otherwise.
     */
    public function add_subtype( $subtype ) {

        // check if subtype already exists (no duplicates allowed!)
        if ( in_array( $subtype->get_id(), $this->subtype_ids ) ) {
            return false; // A task definition with this ID already exists?
        }

        // check if adding subtype will create a cycle
        if ( $this->append_causes_cycle( $subtype ) ) {
            return false;
        }

        // adds task definition to task_definitions
        if ( isset( $this->subtypes ) ) {
            array_push( $this->subtypes, $subtype );
        }

        // adds task definition to task_definition_ids
        array_push( $this->subtype_ids, $subtype->get_id() );

        return true;
    }

    /**
     * Removes the subtype passed in.
     * 
     * NOTE: the subtype is identified by ID, a subtype passed in may have different values, but
     * as long as it has the same ID, it will be deleted.
     * 
     * @since   1.0.0
     * @param   CAA_Task_Plugin_Event_Type $subtype_to_remove is the event type that should be removed as a subtype.
     */
    public function remove_subtype( $subtype_to_remove ) {

        // remove from subtypes
        if ( isset( $this->subtypes ) && ! empty( $this->subtypes ) ) {
            $this->subtypes = array_filter( 
                $this->subtypes, 
                function ( CAA_Task_Plugin_Event_Type $subtype ) use ( $subtype_to_remove ) {
                    return ! ( $subtype->get_id() === $subtype_to_remove->get_id() );
                } 
            );
        }

        // remove from subtype_ids
        $this->subtype_ids = array_filter( 
            $this->subtype_ids, 
            function( int $subtype_id ) use ( $subtype_to_remove ) {
                return ! ( $subtype_id === $subtype_to_remove->get_id() );
            } 
        );
    }

    /**
     * Gets an array of the task definition ids used for the event type.
     * 
     * @since   1.0.0
     * @return  array   An array of integers, representing the task defintion ids.
     */
    public function get_task_definition_ids(): array {
        return $this->task_definition_ids;
    }

    /**
     * Gets a comma separated string of task definition ids used for the event type.
     * 
     * @since   1.0.0
     * @return  string  A comma separated string of ids. e.g. "1,2,4,5" or "" if empty.
     */
    public function get_task_definition_ids_csv(): string {
        if ( empty( $this->task_definition_ids ) ) {
            return "";
        } else {
            return implode( ",", array_map( 'strval', $this->task_definition_ids ) );
        }
    }

    /**
     * Gets an array of task definitions used for the event type.
     * 
     * @since   1.0.0
     * @return  array   An array of CAA_Task_Plugin_Task_Definition objects.
     */
    public function get_task_definitions(): array {

        if ( ! isset( $this->task_definitions ) ) {
            $this->task_definitions = [];
            foreach ( $this->task_definition_ids as $task_definition_id ) {
                $task_definition = CAA_Task_Plugin_Task_Definition_Table::get_task_definition( $task_definition_id );
                array_push( $this->task_definitions, $task_definition );
            }
        }
        
        return $this->task_definitions;
    }

    /**
     * Sets the task definitions used by the event type using a comma separated string of task definiton ids.
     * 
     * Raises exceptions for incorrect format, and duplicate values
     * 
     * @since   1.0.0
     * @param   string  $task_definition_ids_csv is a comma separated string of event type ids. e.g. "1,2,4,5" or "" if none.
     * @todo    validate that ids exist in table.
     */
    public function set_task_definitions_csv( string $task_definition_ids_csv ) {

        
        if ( "" === $task_definition_ids_csv ) {
            // handle empty case

            $this->task_definition_ids = [];
            $this->task_definitions = null;

        } else {
            // handle nonempty case

            // parse csv into int array
            $task_definition_ids = array_map( function( int $task_definition_id ) {
                if ( is_numeric( $task_definition_id ) ) {
                    return intval( $task_definition_id );
                }

                throw new Exception( "Incorrect format!" );

            }, explode( ',', $task_definition_ids_csv ) );

            // check for duplicate values
            if ( count( $task_definition_ids ) !== count( array_flip( $task_definition_ids ) ) ) {
                throw new Exception( "Duplicate values found!" );
            }

            // set task definitions
            $this->task_definition_ids = $task_definition_ids;
            $this->task_definitions = null;

        }
    }

    /**
     * Adds a task definition.
     * 
     * This method ensures no duplicates.
     * 
     * @since   1.0.0
     * @param   CAA_Task_Plugin_Task_Definition $task_definition is the task_definition that should be added.
     * @return  bool True if adding the task definition was successful, false if otherwise.
     */
    public function add_task_definition( CAA_Task_Plugin_Task_Definition $task_definition ): bool {

        // check if task definition already exists (no duplicates allowed!)
        if ( array_search( $task_definition->get_id(), $this->task_definition_ids ) ) {
            return false; // A task definition with this ID already exists?
        }

        // adds task definition to task_definitions
        if ( isset( $this->task_definitions ) ) {
            array_push( $this->task_definitions, $task_definition );
        }

        // adds task definition to task_definition_ids
        array_push( $this->task_definition_ids, $task_definition->get_id() );

        return true;

    }

    /**
     * Removes a task definition.
     * 
     * NOTE: the task definition is identified by ID, a task definition passed in may have different values, but
     * as long as it has the same ID, it will be deleted.
     * 
     * @since   1.0.0
     * @param   CAA_Task_Plugin_Task_Definition $task_definition_to_remove is the task definition that should be removed.
     */
    public function remove_task_definition( CAA_Task_Plugin_Task_Definition $task_definition_to_remove ) {

        // remove from task_definitions
        if ( isset( $this->task_definitions ) ) {
            $this->task_definitions = array_filter( 
                $this->task_definitions, 
                function ( CAA_Task_Plugin_Task_Definition $task_definition ) use ( $task_definition_to_remove ) {
                    return ! ( $task_definition->get_id() === $task_definition_to_remove->get_id() );
                } 
            );
        }

        // remove from task_definition_ids
        $this->task_definition_ids = array_filter( 
            $this->task_definition_ids, 
            function( int $task_definition_id ) use ( $task_definition_to_remove ) {
                return ! ( $task_definition_id === $task_definition_to_remove->get_id() );
            } 
        );
    }

    /**
     * Checks if this event type has a subtype with the same ID as $event_type. Checks recursively.
     * 
     * This method is used to check if adding this event type to $event_type will create a cycle.
     * It relies on the invariant that $this->subtypes and $this->subtype_ids contain exactly the same ids.
     * 
     * @since   1.0.0
     * @param   CAA_Task_Plugin_Event_Type $event_type is the event type that is being looked for in the subtypes.
     * @return  bool true if this event type has $event_type as a subtype (recursively), false otherwise
     */
    private function has_subtype_recursive( CAA_Task_Plugin_Event_Type $event_type ): bool {

        if ( in_array( $event_type->get_id(), $this->subtype_ids ) ) {
            return true;
        }

        foreach ( $this->get_subtypes() as $subtype ){
            if ( $subtype->has_subtype_recursive( $event_type ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if appending $event_type_to_append would cause a cycle, false otherwise
     * @param CAA_Task_Plugin_Event_Type $event_type_to_append
     * @return bool
     * @since 1.0.0
     */
    public function append_causes_cycle( CAA_Task_Plugin_Event_Type $event_type_to_append ): bool {
        return $event_type_to_append->get_id() === $this->get_id() || $event_type_to_append->has_subtype_recursive( $this );
    }

    /**
     * Gets an array of all of the subtype ids that are children of this event type. This is not a shallow
     * operation, it is a deep operation that operates recursively on children.
     * @return array an array of all the subtype ids that are children (or grandchildrent, etc)
     * @since 1.0.0
     */
    private function get_subtype_ids_recursive(): array {
        
        $subtype_ids_rec = $this->subtype_ids;
        foreach ( $this->get_subtypes() as $subtype ) {
            $subtype_ids_rec = array_merge( $subtype_ids_rec, $subtype->get_subtype_ids_recursive() );
        }
        return array_unique( $subtype_ids_rec );
    }

}


/**
 * Need class for drawing event types to select.
 * - Need tree to draw the tree structure. (1 call to getting whole tree structure per page visit)
 * - get_subtypes: makes tree structure, could be called relatively often.
 * - Need function call to get all toplevel event types, and all hidden event types.
 * 
 * Need class for creating/editing event types.
 * - create event type (needs to be created before todos are added)
 *  * 1 write: ID, status: incomplete, deleted
 * Redirect to Edit:
 * - add a task
 *  * 1 write to task definitions
 *  * 1 write to event types
 * - save event type
 *  * 1 write: display name, description, event types, status: complete
 * - delete event type
 *  * 1 write: deleted
 * In Event Type Editing:
 * - tasks: button to add a task will open up modal to create task -> submit task to server
 * - subtypes: searchable list? Or tree thing?
 * 
 * Need class for creating event/tasks based on event types.
 * - needs to get all tasks (and remove duplicates)
 * 
 * 
 * Tasks
 * 
 * Events
 * - contains tasks
 * - contains event types??
 */
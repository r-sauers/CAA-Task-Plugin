<?php

/**
 * Defines the CAA_Task_Plugin_Task_Definition class that stores information about task definitions.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */

/**
 * Used to store information about task definitions.
 * 
 * Task definitions are used to define basecamp tasks. They are stored in a wordpress table,
 * @see CAA_Task_Plugin_Task_Definition_Table. This class provides a convenient way to interact with
 * data from the table.
 * 
 * This class is passed into CAA_Task_Plugin_Task_Definition_Table to create an entry in the table,
 * and it can be generated using CAA_Task_Plugin_Task_Definition_Table to get information to create
 * a basecamp task.
 * 
 * Information includes:
 * - ID (sql table ID)
 * - title
 * - start_offset_in_days  (how many days before the start of the event should this be started?)
 * - finish_offset_in_days (how many days before the start of the event is this due?)
 * - description
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Task_Definition {

    /**
     * @since 1.0.0
     * @var   int|null     The ID of the Task Definition in the wordpress table or null if it isn't in the table yet.
     * The user of this module is responsible for ensuring the task definition has a valid id.
     */
    private int|null $id;

    /**
     * @since 1.0.0
     * @var   string  The title that displays to the user and client on basecamp.
     */
    private string $title;

    /**
     * @since 1.0.0
     * @var   int   The number of days before the start of the event that the task should be 
     * started on.
     */
    private int $start_offset_in_days;

    /**
     * @since 1.0.0
     * @var   int  The number of days before the start of the event that the task should be 
     * finished by.
     */
    private int $finish_offset_in_days;

    /**
     * The description of the task definition.
     * 
     * The description is in rich text format, is a maximum of 16,777,215 characters.
     * @link https://github.com/basecamp/bc3-api/blob/master/sections/rich_text.md for more about the rich text format
     * 
     * @since 1.0.0
     * @var   string   The description.
     */
    private string $description;

    /**
     * Constructs an instance of CAA_Task_Plugin_Task_Definition with all data filled except id.
     * 
     * NOTE: Please call @see set_id to set the table entry id.
     * 
     * @since   1.0.0
     * @param   string  $title  The title that displays to the user and client on basecamp.
     * @param   int     $start_offset_in_days   The number of days before the start of the event 
     * that the task should be started on.
     * @param   int     $finish_offset_in_days  The number of days before the start of the event 
     * that the task should be finished by.
     * @param   string  $description   The client-visible description for the task definition. This is
     * in rich text format. Please see @link https://github.com/basecamp/bc3-api/blob/master/sections/rich_text.md.
     */
    function __construct( 
        string $title, 
        int $start_offset_in_days, 
        int $finish_offset_in_days, 
        string $description
    ) {
        $this->id = null;
        $this->title = $title;
        $this->start_offset_in_days = $start_offset_in_days;
        $this->finsih_offset_in_days = $finish_offset_in_days;
        $this->description = $description;
    }

    /**
     * Gets the ID of the task definition table entry.
     * 
     * The ID is not guaranteed to be a valid entry in the table.
     * 
     * @since 1.0.0
     * @return   int|null     The ID of the Task Definition in the wordpress table or null if it isn't in a table.
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * Sets the ID of the task definition.
     * 
     * @since   1.0.0
     * @param   int     $id     The ID of the task definition in the wordpress table. Should be valid
     * entry in table.
     */
    public function set_id( int $id ) {
        $this->id = $id;
    }

    /**
     * Checks if the task definition has an id.
     * 
     * @since   1.0.0
     * @return  bool    true if there is an id, false if there isn't
     */
    public function has_id(): bool {
        return ! ( null === $this->id );
    }

    /**
     * Gets the title of the task definition.
     * 
     * @since 1.0.0
     * @return   string  The title that displays to the user and client on basecamp.
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Gets the task start offset of the task definition.
     * 
     * @since 1.0.0
     * @return   int   The number of days before the start of the event that the task should be 
     * started on.
     */
    public function get_start_offset_in_days() {
        return $this->start_offset_in_days;
    }

    /**
     * Gets the task finish offset of the task definition.
     * 
     * @since 1.0.0
     * @return   int  The number of days before the start of the event that the task should be 
     * finished by.
     */
    public function get_finish_offset_in_days() {
        return $this->finish_offset_in_days;
    }

    /**
     * Gets the description of the task definition.
     * 
     * @since 1.0.0
     * @return   string|null   The description of the task definition.
     */
    public function get_description() {
        return $this->description;
    }
}
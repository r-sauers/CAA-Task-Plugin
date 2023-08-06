<?php
/**
 * 
 * Library of validation functions used across routes
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/api
 */

/**
 * Library of validation functions used across routes.
 * 
 * @package CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/SUBPACKAGE
 * @author Ryan Sauers <sauer319@umn.edu>
 */
abstract class CAA_Task_Plugin_API_Validation {

    /**
     * Returns true if $str is a valid bool e.g. 'true'
     * @param string $str
     * @return bool
     * @since 1.0.0
     */
    public static function bool_validation( string $str ): bool {
        return 'false' === $str || 'true' === $str;
    }

    /**
     * Validates whether $str is a valid event type id
     * @param string $str
     * @return bool
     * @since 1.0.0
     */
    public static function event_type_id_validation( string $str ): bool {
		return is_numeric( $str );
    }

    /**
     * Validates whether $str is a valid event id
     * @param string $str
     * @return bool
     * @since 1.0.0
     */
    public static function event_id_validation( string $str ): bool {
    return is_numeric( $str );
    }

    /**
     * Validates whether $str is a valid task definition id
     * @param string $str
     * @return bool
     * @since 1.0.0
     */
    public static function task_definition_id_validation( string $str ): bool {
		return is_numeric( $str );
    }

    /**
     * Validates whether $str is a valid task definition date offset
     * @param string $str
     * @return bool
     * @since 1.0.0
     */
    public static function task_definition_date_offset_validation( string $str ): bool {
		return is_numeric( $str );
    }

    /**
     * Validates whether $str is a valid task definition description
     * @param string $str
     * @return bool
     * @since 1.0.0
     */
    public static function task_definition_description_validation( string $str ): bool {
		return true;
    }

    /**
     * Validates whether $str is a valid task definition title
     * @param string $str
     * @return bool
     * @since 1.0.0
     */
    public static function task_definition_title_validation( string $str ): bool {
		return true;
    }
}
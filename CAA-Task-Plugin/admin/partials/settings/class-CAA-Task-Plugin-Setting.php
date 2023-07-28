<?php

/**
 * Used to create and retrieve settings for the plugin.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin/partials/settings
 */

/**
 * Used to create and retrieve settings for the plugin.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin/partials/settings
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
abstract class CAA_Task_Plugin_Setting {

    /**
	 * The option name for the setting.
	 *
     * The option name is used by wordpress to refer to the option. The option name should be 
     * lowercase and snakecase.
     * 
	 * @since    1.0.0
	 * @var      string    $option_name    The option name of the setting
	 */
    abstract public function get_option_name();

    /**
	 * The option title for the setting.
	 *
     * The option title will appear in the field users fill in.
     * 
	 * @since    1.0.0
	 * @var      string    $option_title    The option title of the setting
	 */
    abstract public function get_option_title();

    /**
	 * Initializes the setting in wordpress.
	 * 
	 * Registers the setting, creates a field for it, and creates a sanitize hook.
	 * 
	 * @since   1.0.0
	 */
    public function init (
        $settings_page_slug, 
        $settings_section_slug 
    ) {
        register_setting( 'options', $this->get_option_name() );
        add_settings_field( 
            $this->get_option_name(), 
            $this->get_option_title(), 
            array( $this, 'output_field' ), 
            $settings_page_slug, 
            $settings_section_slug
        );
    }

    /**
	 * Retrieves the raw value of the setting.
	 * 
	 * Retrieves the value from the wordpress option, if no option exists, returns ''.
	 * 
	 * @since   1.0.0
     * @return  string  The value of the setting or '' if no value has been set
	 */
    public function get_value() {
        $value = get_option( $this->get_option_name() );
        if ( $value ) {
            return $value;
        } else {
            return '';
        }
    }

    /**
	 * Outputs the field for the user to edit the setting.
	 * 
	 * @since 1.0.0
	 */
    public abstract function output_field();

    /**
	 * Updates the value of the setting.
	 * 
     * Takes the new value submitted by the user in a POST request, sanitizes it, and updates the
     * wordpress option.
     * 
	 * @since 1.0.0
	 */
    public function update_value() {
        $new_value = $_POST[ $this->get_option_name() ];
        $sanitized_value = sanitize_option( "sanitize_option_". $this->get_option_name(), $new_value );
        update_option( $this->get_option_name(), $sanitized_value );
    }
}
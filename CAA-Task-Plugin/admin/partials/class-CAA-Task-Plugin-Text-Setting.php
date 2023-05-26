<?php

/**
 * Used to create and retrieve text settings for the plugin.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin/partials/settings
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/class-CAA-Task-Plugin-Setting.php';
/**
 * Used to create and retrieve text settings for the plugin.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin/partials/settings
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Text_Setting extends CAA_Task_Plugin_Setting {

    /**
	 * The option name for the setting.
	 *
     * The option name is used by wordpress to refer to the option. The option name should be 
     * lowercase and snakecase.
     * 
	 * @since    1.0.0
	 * @var      string    $option_name    The option name of the setting
	 */
    private $option_name;

    /**
	 * The option title for the setting.
	 *
     * The option title will appear in the field users fill in.
     * 
	 * @since    1.0.0
	 * @var      string    $option_title    The option title of the setting
	 */
    private $option_title;

    /**
	 * Initializes the setting in wordpress, and initializes option_name and option_title.
	 * 
	 * @since   1.0.0
     * @param   string  $option_name    the snakecase name for the option, should be unique
     * @param   string  $option_title   the title that describes the option to the user
     * @param   string  $settings_page_slug  the slug for the page the setting will show on
     * @param   string  $settings_section_slug  the slug for the section the setting is in
	 */
    function __construct(
        $option_name,
        $option_title,
        $settings_page_slug, 
        $settings_section_slug 
    ) {
        $this->option_name = $option_name;
        $this->option_title = $option_title;
        $this->init( $settings_page_slug, $settings_section_slug );
    }

    /**
	 * Gets the option name for the setting.
	 *
     * The option name is used by wordpress to refer to the option. The option name should be 
     * lowercase and snakecase.
     * 
	 * @since    1.0.0
	 * @return   string    The option name of the setting
	 */
    public function get_option_name() {
        return $this->option_name;
    }

    /**
	 * Gets the option title for the setting.
	 *
     * The option title will appear in the field users fill in.
     * 
	 * @since    1.0.0
	 * @return   string    The option title of the setting
	 */
    public function get_option_title() {
        return $this->option_title;
    }

    /**
	 * Outputs the field for the user to edit the setting.
     * 
     * Uses a text field.
	 * 
	 * @since 1.0.0
	 */
    public function output_field() {
        // get the value of the setting we've registered with register_setting()
        $value = $this->get_value();
        // output the field
        ?>
        <input type="text" name="<?php echo esc_attr( $this->get_option_name() ) ?>" value="<?php echo isset( $value ) ? esc_attr( $value ) : ''; ?>">
        <?php
    }
}
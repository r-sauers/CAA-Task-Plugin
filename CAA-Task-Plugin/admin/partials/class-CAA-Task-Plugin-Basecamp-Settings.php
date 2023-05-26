<?php

/**
 * Used to create and retrieve settings for Basecamp.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/class-CAA-Task-Plugin-Text-Setting.php';
/**
 * Used to create and retrieve settings for Basecamp.
 *
 * This class defines the following settings:
 * - client id: the id of the integration registered with basecamp, used on client side of OAuth2
 * - client secret: the secret of the integration registered with basecamp, used on server side of
 * OAuth2
 * 
 * It also provides methods to retrieve these settings.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Basecamp_Settings {

    private static $client_id;
    private static $client_secret;
    private static $redirect_uri;

    /**
	 * Initialize settings needed for BasecampAuth.
	 * 
	 * BasecampAuth requires settings for client id, client secret, and the project we need
     * to check permissions for.
	 * 
	 * @since 1.0.0
	 */
	public static function init( $settings_page_slug ) {

        // add settings section
        $settings_section_slug = 'caa_task_plugin_basecamp_settings';
        add_settings_section( 
			$settings_section_slug, 
			'CAA Basecamp Settings',
			array( get_called_class(), 'basecamp_settings_section_callback' ),
			$settings_page_slug
		);

        // add client id setting
        self::$client_id = new CAA_Task_Plugin_Text_Setting(
            'caa_task_plugin_basecamp_client_id',
            'Basecamp Client ID',
            $settings_page_slug,
            $settings_section_slug
        );
        add_filter( 
            'sanitize_option_' . self::$client_id->get_option_name(),
            'sanitize_text_field'
        );

        // add client secret setting
        self::$client_secret = new CAA_Task_Plugin_Text_Setting(
            'caa_task_plugin_basecamp_client_secret',
            'Basecamp Client Secret',
            $settings_page_slug,
            $settings_section_slug
        );
        add_filter( 
            'sanitize_option_' . self::$client_secret->get_option_name(),
            'sanitize_text_field'
        );

        // add redirect_uri setting
        self::$redirect_uri = new CAA_Task_Plugin_Text_Setting(
            'caa_task_plugin_basecamp_redirect_uri',
            'Basecamp Redirect URI',
            $settings_page_slug,
            $settings_section_slug
        );
        add_filter( 
            'sanitize_option_' . self::$redirect_uri->get_option_name(),
            'sanitize_text_field'
        );
        add_filter( 
            'sanitize_option_' . self::$redirect_uri->get_option_name(),
            'sanitize_url'
        );

	}

    /**
	 * Outputs description of basecamp settings section.
     * 
     * This echos html for the description under the title of the basecamp settings section.
	 * 
	 * @since 1.0.0
	 */
    public static function basecamp_settings_section_callback() {
        echo "<p>Basecamp Settings Intro</p>";
    }

    /**
	 * Outputs the field for redirect uri in the settings page.
     * 
     * @todo updated values should be validated
	 * @since 1.0.0
	 */
    public static function update_settings() {
        self::$client_id->update_value();
        self::$client_secret->update_value();
        self::$redirect_uri->update_value();
    }
}
<?php

/**
 * Used to authenticate users with third party integrations in the admin menu
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/class-CAA-Task-Plugin-BasecampAuth.php';

/**
 * Used to authenticate users with third party integrations in the admin menu
 *
 * This class defines the high-level code used to authenticate users with all third-party integrations.
 * This class can check if a user still needs to be authenticated, and it can manage the process of
 * authenticating them.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Authenticator {

    /**
	 * Logs user in if they are not already.
     * 
     * Services include: Basecamp
	 *
	 * @since    1.0.0
     * @return   bool   True if user is logged in to all third party services, false if not
	 */
    public static function ensure_login() {
        if ( !self::is_user_logged_in() ) {
			self::login_user(); // send them to main page with a 'not logged in flag'
        }
    }

    /**
	 * Checks if user logged in to all third party services.
     * 
     * Services include: Basecamp
	 *
	 * @since    1.0.0
     * @return   bool   True if user is logged in to all third party services, false if not
	 */
    public static function is_user_logged_in() {
        return CAA_Task_Plugin_BasecampAuth::is_user_logged_in();
    }

    /**
	 * Logs user into all third party services it is not already logged into
	 *
	 * Services include: Basecamp
	 *
	 * @since    1.0.0
	 */
    public static function login_user() {
        if ( !CAA_Task_Plugin_BasecampAuth::is_user_logged_in() ) {
            CAA_Task_Plugin_BasecampAuth::login_user();
        }
    }
}
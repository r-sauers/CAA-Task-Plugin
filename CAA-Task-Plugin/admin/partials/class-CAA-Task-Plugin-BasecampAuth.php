<?php

/**
 * Used to authenticate users with Basecamp using OAuth2
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/class-CAA-Task-Plugin-Basecamp-Settings.php';

/**
 * Used to authenticate users with Basecamp
 *
 * This class defines how to authenticate users with Basecamp using OAuth2. It also defines how
 * the authentication information is stored in the wordpress site.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_BasecampAuth {

    /**
	 * Checks if the user's authorization token exists and hasn't expired.
	 *
	 * @since    1.0.0
     * @return   bool   The value is true if user has an unexpired auth token, and false otherwise.
	 */
    public static function is_user_logged_in() {
        $user_id = get_current_user_id();
        $auth_expire_ISO_8601 = get_user_meta($user_id, "caa_task_plugin_basecamp_auth_expire", true);
        
        if ( empty( $auth_expire_ISO_8601 ) ) {
            return false;
        }

        $auth_expire_timestamp = self::convert_ISO_8601_to_timestamp( $auth_expire_ISO_8601 );
        if ( $auth_expire_timestamp < time() ) {
            return false;
        }

        return true;
    }

    /**
	 * Log user into basecamp using OAuth2 authentication.
	 *
	 * Redirects user to 37signals (basecamp) to get a verification token, then makes a backchannel 
     * request using the code to get the authorization token. Finally, makes api request to check
     * if user has sufficient permissions to edit the basecamp project.
     * Does this as described here: 
     * @link https://github.com/basecamp/api/blob/master/sections/authentication.md
	 *
	 * @since    1.0.0
	 */
    public static function login_user() {

        $client_id = CAA_Task_Plugin_Basecamp_Settings::$client_id->get_value();
        $redirect_uri = CAA_Task_Plugin_Basecamp_Settings::$redirect_uri->get_value();

        $verification_code = self::get_verification_code();
        if ( empty( $verification_code ) ) {
            self::request_verification_code( $client_id, $redirect_uri ); // call exits process
        }

        $client_secret = CAA_Task_Plugin_Basecamp_Settings::$client_secret->get_value();
        $auth_token = self::request_auth_token( $client_id, $redirect_uri, $client_secret, $verification_code );
        $auth_details = self::request_auth_details( $auth_token );
        if ( self::has_project_permissions($auth_token) ) {
            $auth_expire_ISO_8601 = self::get_auth_expire_ISO_8601( $auth_token );
            self::save_auth_token( $auth_token, $auth_expire_ISO_8601 );
        }
    }

    /**
	 * Redirects user to 37signals (basecamp) to log in and be redirected back with verification code.
     * 
     * url is not escaped.
	 *
     * @ignore
	 * @since    1.0.0
     * @param    string     `$client_id` is an alphanumeric id for the basecamp integration 
     * @param    string     `$redirect_uri` is the uri that basecamp sends the verification code to
	 */
    private static function request_verification_code( $client_id, $redirect_uri ) {
        // ISSUE: wp_safe_redirect should be used.
        //        wp_safe_redirect was causing errors, so it was reverted to wp_redirect.
        wp_redirect( 'https://launchpad.37signals.com/authorization/new?type=web_server&client_id=' . $client_id . '&redirect_uri=' . $redirect_uri );
        exit;
    }

    /**
	 * Gets verification code from redirect back to login page.
	 *
	 * 37 signals will redirect the user back to the login page (GET request) with the code as a
     * url parameter `?code=<code>` This retrieves the code from the url if it exists. Note that
     * this also checks for the `basecamp-auth` parameter too so future integrations won't cause a 
     * conflict.
     * Verification code is not validated or sanitized.
	 * 
     * @ignore
	 * @since    1.0.0
     * @return   string     The verification code or '' if there is no verification code.
	 */
    private static function get_verification_code() {
        if ( 'GET' === $_SERVER['REQUEST_METHOD']) {
            if ( $_GET['basecamp-auth'] && isset( $_GET['code'] ) ) {
                return $_GET['code'];
            }
        }
        return '';
    }

    /**
	 * Makes backchannel request to trade the verification code for an authorization token.
	 *
	 * url is not escaped, authorization token is not validated or sanitized.
	 *
     * @ignore
	 * @since    1.0.0
     * @param    string     `client_id` is an alphanumeric id for the basecamp integration 
     * @param    string     `redirect_uri` is the uri that basecamp sends the verification code to
     * @param    string     `client_secret` is an alphanumeric secret for basecamp integration
     * @param    string     `verification_code` is an alphanumeric code given to trade for an authorization token
     * @return   string     the authorization token or '' if there is none.
	 */
    private static function request_auth_token( $client_id, $redirect_uri, $client_secret, $verification_code ) {
        $response = wp_remote_post( 
            'https://launchpad.37signals.com/authorization/token?type=web_server&client_id=' . 
            $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . 
            '&code=' . $verification_code );
        $status = wp_remote_retrieve_response_code( $response );
        if ( $status === 200 ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            return $body["access_token"];
        }
        return "";
    }

    /**
	 * Saves authorization token to user meta data.
     * 
     * Saves the token to the 'CAA_Task_Plugin_Basecamp_Auth_Token' meta key for current user.
     * 
     * @ignore
	 * @since    1.0.0
     * @param    string     `$auth_token` is alphanumeric token used to access 37Signals API
	 */
    private static function request_auth_details( $auth_token ) {
        $args = [];
        $args['headers'] = [ 'Authorization' => 'BEARER ' . $auth_token ];
        $response = wp_remote_get('https://launchpad.37signals.com/authorization.json', $args);
        $auth_details = json_decode( wp_remote_retrieve_body( $response ), true );
        return $auth_details;
        
        return $body['expires_at'];
    }

    /**
     * Checks if the user has permissions to edit the specific basecamp project
     * 
     * @ignore
     * @since    1.0.0
     * @param    array      `$auth_details` is the json response from the authorization endpoint
     * @return   bool       `true` if the user has all the permissions, `false` if not
     */
    private static function user_has_project_permissions( $auth_details ) {
        return true;
    }

    /**
     * Gets the authentication token expiration date in ISO 8601
     * 
     * @ignore
     * @since    1.0.0
     * @param    array      `$auth_details` is the json response from the authorization endpoint
     * @return   string     The date in ISO 8601 format e.g. '2023-05-13T01:49:17.000Z'
     */
    private static function get_auth_expire_ISO_8601( $auth_details ) {
        return $auth_details['expires_at'];
    }

    /**
	 * Saves authorization token to user meta data.
     * 
     * Saves the token to the 'CAA_Task_Plugin_Basecamp_Auth_Token' meta key for current user.
     * 
     * @ignore
	 * @since    1.0.0
     * @param    string     `$auth_token` is alphanumeric token used to access 37Signals API
     * @param    string     `$auth_expire` is the date the token will expire in ISO 8601 format
	 */
    private static function save_auth_token( $auth_token, $auth_expire ) {
        $user_id = get_current_user_id();
        update_user_meta( $user_id, 'caa_task_plugin_basecamp_auth_token', $auth_token);
        update_user_meta( $user_id, 'caa_task_plugin_basecamp_auth_expire', $auth_expire);
    }

    /**
	 * Gets auth token from user meta data.
	 *
	 * Retrieves data from current user's CAA_Task_Plugin_Basecamp_Auth_Token' meta key.
	 *
     * @ignore
	 * @since    1.0.0
     * @return   string     the authorization token or '' if no auth token exists
	 */
    private static function get_auth_token() {
        $user_id = get_current_user_id();
        $auth_token = get_user_meta($user_id, 'caa_task_plugin_basecamp_auth_token', true);
        if ( $auth_token ) {
            return $auth_token;
        } else {
            return '';
        }
    }

    /**
	 * Converts ISO 8601 date format to a Unix timestamp.
	 * 
	 * @ignore
	 * @since    1.0.0
     * @param    string     `$ISO_8601` is an ISO 8601 date format e.g. '2023-05-13T01:49:17.000Z'
     * @return   int        The Unix Timestamp
	 */
    private static function convert_ISO_8601_to_timestamp( $ISO_8601 ) {
        sscanf($ISO_8601, "%d-%d-%dT%d:%d:%dZ", $year, $month, $day, $hour, $minute, $second);
        $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
        return $timestamp;
    }
}
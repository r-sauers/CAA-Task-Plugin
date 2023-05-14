<?php

/**
 * Adapted from https://github.com/DevinVinson/WordPress-Plugin-Boilerplate under the GPL v2 license
 * Modified: 5/8/23
 * 
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    			The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/CAA-Task-Plugin-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/CAA-Task-Plugin-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Create the admin menu for the plugin. The admin menu includes submenus.
	 * 
	 * @since	1.0.0
	 */
	public function create_admin_menu() {

		$toplevel_slug = 'caa-task-app';
		$hookname = add_menu_page(
			'CAA Task App',
			'CAA Task App',
			'edit_pages',
			$toplevel_slug,
			array( get_called_class(), 'generate_admin_menu_html' ),
			plugin_dir_url(__FILE__) . 'images/menu_icon.png',
			20
		);
		add_action( 'load-' . $hookname, array( get_called_class(), 'ensure_login' ) ); 

		add_submenu_page(
			$toplevel_slug,
			'Create Event Tasks',
			'Create Event Tasks',
			'edit_pages',
			'create-event-tasks',
			array( get_called_class(), 'generate_create_event_tasks_submenu_html' )
		);
	}

	/**
	 * Wrapper for CAA_Task_Plugin_Authenticator::ensure_login
	 * 
	 * @since	1.0.0
	 */
	public static function ensure_login() {
		CAA_Task_Plugin_Authenticator::ensure_login();
	}

	/**
	 * Generate the html for the page linked to the top level admin menu for the plugin.
	 * 
	 * @since	1.0.0
	 */
	public static function generate_admin_menu_html() {

		if ( !CAA_Task_Plugin_Authenticator::is_user_logged_in() ) {
			// draw login page
			// CAA_Task_Plugin_Authenticator::login_user(); // send them to main page with a 'not logged in flag'
		} else {
			// draw main page
			require plugin_dir_path(__FILE__) . 'partials/CAA-Task-Plugin-admin-menu-logged-in.php';
		}

	}

	/**
	 * Display the html for the page linked to the 'Create Event Tasks' admin submenu.
	 * 
	 * @since 1.0.0
	 */
	public static function generate_create_event_tasks_submenu_html() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		</div>
		<?php
	}
}

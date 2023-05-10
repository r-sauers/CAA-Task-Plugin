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
	 * Create the admin menu for the plugin. The admin menu includes submenus.
	 * 
	 * @since	1.0.0
	 */
	public function create_admin_menu() {

		$toplevel_slug = 'caa-task-app';
		add_menu_page(
			'CAA Task App',
			'CAA Task App',
			'edit_pages',
			$toplevel_slug,
			array( get_called_class(), 'generate_admin_menu_html' ),
			plugin_dir_url(__FILE__) . 'images/menu_icon.png',
			20
		);

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
	 * Generate the html for the page linked to the top level admin menu for the plugin.
	 * 
	 * @since	1.0.0
	 */
	public static function generate_admin_menu_html() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		</div>
		<?php
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

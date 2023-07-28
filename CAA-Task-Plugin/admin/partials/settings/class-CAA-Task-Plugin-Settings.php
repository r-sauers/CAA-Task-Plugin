<?php

/**
 * Used to create and retrieve settings for the plugin.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'basecamp/class-CAA-Task-Plugin-Basecamp-Settings.php';
/**
 * Used to create and retrieve settings for the plugin.
 *
 * This class initializes settings for third party integrations such as Basecamp.
 *
 * @since      1.0.0
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/includes
 * @author     Ryan Sauers <sauer319@umn.edu>
 */
class CAA_Task_Plugin_Settings {

	/**
	 * The slug used to identify the settings page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $settings_page_slug		the settings page slug
	 */
	private $settings_page_slug;

	/**
	 * The title of the settings page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $settings_page_title		the settings page title
	 */
	private $settings_page_title;

	/**
	 * Initialize settings needed for plugin.
	 * 
	 * Currently settins are only necessary for Basecamp.
	 * 
	 * @since 1.0.0
	 */
	public function __construct( $settings_page_slug, $settings_page_title ) {
		$this->settings_page_slug = $settings_page_slug;
		$this->settings_page_title = $settings_page_title;
	}

    /**
	 * Initialize settings needed for plugin.
	 * 
	 * Currently settins are only necessary for Basecamp.
	 * 
	 * @since 1.0.0
	 */
	public function init() {

		CAA_Task_Plugin_Basecamp_Settings::init( $this->settings_page_slug );

	}

	/**
	 * Register the options page, so it shows in the navbar.
	 * 
	 * @since 1.0.0
	 */
	public function create_options_page() {

		$hookname = add_options_page( 
			$this->settings_page_title,
			$this->settings_page_title,
			'edit_pages',
			$this->settings_page_slug,
			array( $this, 'create_options_page_html' )
			
		);

		add_action( 'load-' . $hookname, array( $this, 'options_page_load' ) ); 
	}

	/**
	 * Display the html for the options page.
	 * 
	 * @since 1.0.0
	 */
	public function create_options_page_html() {
		?>
		<div class="wrap">
                <h1><?php echo esc_html( get_admin_page_title() ); ?><h1>
                <form action=<?php echo "admin.php?page=" . $this->settings_page_slug ?> method="post">
                    <?php
                    // output security fields for the registered setting "wporg_options"
                    settings_fields( 'options' );
                    // output setting sections and their fields
                    do_settings_sections( $this->settings_page_slug );
                    // output save settings button
                    submit_button( __('Save Settings', 'textdomain' ) );
                    ?>
                </form>
            </div>
		<?php
	}

	/**
	 * Called before the settings page is loaded.
	 * 
	 * Used to process settings changes submitted through POST requests.
	 * 
	 * @since 1.0.0
	 */
	public function options_page_load() {
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ){
			$this->options_page_submit();
		}
	}

	/**
	 * Called when user submits the options form.
	 * 
	 * Updates the settings based on user input.
	 * 
	 * @since 1.0.0
	 */
	private function options_page_submit() {
		CAA_Task_Plugin_Basecamp_Settings::update_settings();
	}
}
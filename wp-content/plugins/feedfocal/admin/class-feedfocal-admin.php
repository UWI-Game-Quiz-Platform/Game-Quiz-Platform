<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       feedfocal.com
 * @since      1.0.0
 *
 * @package    FeedFocal
 * @subpackage FeedFocal/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    FeedFocal
 * @subpackage FeedFocal/admin
 * @author     FeedFocal <wordpress@feedfocal.com>
 */
class FeedFocal_Admin {

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
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register admin init.
	 *
	 * @since    1.0.0
	 */
	public function admin_init() {

		// Redirect on initirla activation
		if( get_option( 'feedfocal_activation_redirect', false ) ) {
			delete_option('feedfocal_activation_redirect');

			// Do not redirect in bulk activation
			if( !isset( $_GET['activate-multi'] ) ) {
				wp_redirect(FEEDFOCAL_PLUGIN_SETTINGS_URL);
			}
	    }
	}

	/**
	 * Register admin pages.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {

		add_menu_page(
			__( 'JustFeedback', 'feedfocal' ),
			__( 'JustFeedback', 'feedfocal' ),
			'manage_options',
			'feedfocal_projects',
			'feedfocal_template_projects',
			'dashicons-cloud'
		);

	}

	/**
	 * Register custom API endpoints.
	 *
	 * @since    1.0.0
	 */
	public function rest_api_init() {

		// Register custom API endpoints
		register_rest_route( 'feedfocal/v1', '/setup', array(
			'methods'             => 'POST',
			'callback'            => 'feedfocal_api_setup',
			'permission_callback' => function( $request ) {
				return current_user_can( 'manage_options' );
			}
		) );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in FeedFocal_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The FeedFocal_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


		if( is_admin('feedfocal_projects') ) {

			// Add plugin stylesheets
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/feedfocal-admin.css', array(), $this->version, 'all' );

			// Add 3rd party stylesheets
			wp_enqueue_style( 'uikit-css', plugin_dir_url( __FILE__ ) . 'css/uikit.css', array(), '3.13.7', 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in FeedFocal_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The FeedFocal_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// Add plugin scripts
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/feedfocal-admin.js', array( 'jquery' ), $this->version, false );

		// Add 3rd party scripts
		wp_enqueue_script( 'uikit-js', plugin_dir_url( __FILE__ ) . 'js/uikit.js', array( 'jquery' ), '3.13.7', false );

		wp_localize_script( $this->plugin_name, 'WPURLS',
			array(
				'api_root'  => esc_url_raw( rest_url() ),
				'api_nonce' => wp_create_nonce( 'wp_rest' )
			)
		);

	}

	/**
	 * Add links to plugin page.
	 *
	 * @since    1.0.0
	 */
	public function plugin_action_links( $links ) {

		// Build and escape the URL.
		$url = esc_url( add_query_arg(
			'page',
			'feedfocal_projects',
			get_admin_url() . 'admin.php'
		));

		// Create the link.
		$settings_link = "<a href='$url'>" . __( 'Settings', 'feedfocal' ) . '</a>';

		// Adds the link to the end of the array.
		array_push(
			$links,
			$settings_link
		);

		return $links;

	}

	/**
	 * Add admin body class.
	 *
	 * @since    1.0.0
	 */
	public function admin_body_class( $classes ) {

		// Get page info
		$screen = get_current_screen();

		// Add body class to feedfocal pages
		if(strpos($screen->id, 'feedfocal') !== false) {
			$classes .= ' page-feedfocal';
		}

		return $classes;

	}
}

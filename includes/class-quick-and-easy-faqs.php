<?php

/**
 * The file that defines the core plugin class
 *
 * It includes attributes and functions used across both the
 * public-facing side of the site and the admin area. Also maintains the unique identifier
 * of this plugin as well as the current version of the plugin.
 */
class Quick_And_Easy_Faqs {

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 */
	protected $version;

	/**
	 * The Self Instance of the plugin.
	 */
	protected static $qae_instance;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {

		$this->version = QUICK_AND_EASY_FAQS_VERSION;

		$this->plugin_name = 'quick-and-easy-faqs';

		$this->load_admin_dependencies();
		$this->load_public_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		add_action( 'plugins_loaded', [ $this, 'set_locale' ] );

	}

	/**
	 * Define the instance functionality of the plugin.
	 */
	public static function instance() {
		if ( is_null( self::$qae_instance ) ) {
			self::$qae_instance = new self();
		}
		return self::$qae_instance;
	}

	/**
	 * Load the admin dependencies for this plugin.
	 */
	private function load_admin_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-faqs-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-faqs-post-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-faqs-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-faqs-classic-editor.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-faqs-gutenberg-editor.php';

	}

	/**
	 * Load the admin dependencies for this plugin.
	 */
	private function load_public_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-faqs-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-faqs-shortcode.php';

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 */
	public function set_locale() {

		load_plugin_textdomain(
			'quick-and-easy-faqs',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 */
	private function define_admin_hooks() {

		$post_type = new FAQs_Post_Type_And_Taxonomy();
		add_action( 'init', [ $post_type, 'register_faqs_post_type' ] );
		add_action( 'init', [ $post_type, 'register_faqs_group_taxonomy' ] );

		$plugin_admin = new Quick_And_Easy_FAQs_Admin( $this->plugin_name, $this->version );
		add_action( 'admin_enqueue_scripts', [ $plugin_admin, 'enqueue_admin_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $plugin_admin, 'enqueue_admin_scripts' ] );

		$classic_editor = new FAQs_Add_Classic_Editor_Button();
		add_filter( 'mce_external_plugins', [ $classic_editor, 'enqueue_plugin_scripts' ] );
		add_filter( 'mce_buttons', [ $classic_editor, 'register_buttons_editor' ] );

		$gutenberg_button = new FAQs_Add_Gutenberg_Blocks();
		if ( Quick_And_Easy_FAQs_Admin::is_gutenberg_active() ) {
			add_filter( 'block_categories', [ $gutenberg_button, 'add_faqs_block_category' ] );
			add_action( 'init', [ $gutenberg_button, 'add_all_faqs_block' ] );
		}

		$faq_settings = new Add_FAQs_Settings();
		add_action( 'admin_menu', [ $faq_settings, 'add_faqs_options_page' ] );
		add_action( 'admin_init', [ $faq_settings, 'initialize_faqs_options' ] );
		add_filter( 'plugin_action_links_' . $this->plugin_name, [ $faq_settings, 'faqs_action_links' ] );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 */
	private function define_public_hooks() {

		$plugin_public = new Quick_And_Easy_Faqs_Public( $this->plugin_name, $this->version );
		add_action( 'wp_enqueue_scripts', [ $plugin_public, 'enqueue_public_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $plugin_public, 'enqueue_public_scripts' ] );
		add_action( 'wp_head', [ $plugin_public, 'add_public_custom_styles' ] );


		$faqs_shortcodes = new FAQs_Shortcode( $this->plugin_name, $this->version );
		add_action( 'init', [ $faqs_shortcodes, 'register_faqs_shortcodes' ] );

		if ( class_exists( 'Vc_Manager' ) ) {
			add_action( 'vc_before_init', [ $faqs_shortcodes, 'integrate_shortcode_with_vc' ] );
		}

	}
}

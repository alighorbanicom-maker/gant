<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/includes
 * @author     Jules
 */
class Wp_Project_Manager {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->plugin_name = 'wp-project-manager';
        $this->version = '1.0.0';
        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wpm-date-functions.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpm-cpm.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpm-project-analyzer.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpm-admin.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new WPM_Admin( $this->get_plugin_name(), $this->get_version() );

        // Add menu
        add_action( 'admin_menu', array( $plugin_admin, 'add_menu' ) );

        // Enqueue scripts and styles
        add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ) );

        // Handle form submission
        add_action( 'admin_post_wpm_save_project', array( $plugin_admin, 'save_project' ) );

        // Handle print page request
        add_action( 'admin_init', array( $plugin_admin, 'handle_print_page' ) );

        // Handle delete action
        add_action( 'admin_init', array( $plugin_admin, 'handle_delete_action' ) );
    }

    /**
     * Retrieve the name of the plugin.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * The main plugin execution function.
     *
     * @since    1.0.0
     */
    public function run() {
        // At this point, we don't have public-facing functionality to run.
    }
}
?>
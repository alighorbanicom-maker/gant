<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    AG_Moshtashar_Booking
 * @subpackage AG_Moshtashar_Booking/admin
 */

class AGMB_Admin {

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
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        add_menu_page(
            'AG Booking Settings',
            'AG Booking',
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_settings_page' ),
            'dashicons-calendar-alt',
            58
        );
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        include_once 'partials/agmb-admin-display.php';
    }
}

<?php
/**
 * Plugin Name:       AG Moshtashar Booking Pro
 * Plugin URI:        https://dralighorbani.com/
 * Description:       A comprehensive booking and consultation system for Dr. Ali Ghorbani, featuring a Jalali calendar, online payments via Zarinpal, and SMS notifications.
 * Version:           1.0.0
 * Author:            Dr. Ali Ghorbani
 * Author URI:        https://dralighorbani.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ag-moshtashar-booking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'AGMB_PLUGIN_VERSION', '1.0.0' );
define( 'AGMB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AGMB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The core plugin class.
 */
class AG_Moshtashar_Booking_Pro {

    protected $loader;
    protected $plugin_name;
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->plugin_name = 'ag-moshtashar-booking';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        require_once AGMB_PLUGIN_DIR . 'admin/class-agmb-admin.php';
        require_once AGMB_PLUGIN_DIR . 'public/class-agmb-public.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $admin_hooks = new AGMB_Admin( $this->get_plugin_name(), $this->get_version() );
        add_action( 'admin_menu', array( $admin_hooks, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $public_hooks = new AGMB_Public( $this->get_plugin_name(), $this->get_version() );
        add_action( 'wp_enqueue_scripts', array( $public_hooks, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $public_hooks, 'enqueue_scripts' ) );
        add_shortcode( 'ag_booking', array( $public_hooks, 'render_booking_form' ) );
    }

    /**
     * Register all of the settings for the plugin.
     */
    public function register_settings() {
        // Settings Group
        register_setting( 'agmb_settings_group', 'agmb_settings' );

        // General Settings Section
        add_settings_section( 'agmb_general_settings_section', 'General Settings', null, $this->plugin_name );

        add_settings_field( 'agmb_price_per_15_min', 'Price per 15 minutes', array( $this, 'render_price_field' ), $this->plugin_name, 'agmb_general_settings_section' );
        add_settings_field( 'agmb_logo_url', 'Logo URL', array( $this, 'render_logo_field' ), $this->plugin_name, 'agmb_general_settings_section' );
        add_settings_field( 'agmb_welcome_text', 'Welcome Text', array( $this, 'render_welcome_text_field' ), $this->plugin_name, 'agmb_general_settings_section' );

        // Schedule Settings Section
        add_settings_section( 'agmb_schedule_settings_section', 'Schedule Settings', null, $this->plugin_name );
        add_settings_field( 'agmb_working_hours', 'Working Hours', array( $this, 'render_working_hours_field' ), $this->plugin_name, 'agmb_schedule_settings_section' );
        add_settings_field( 'agmb_weekly_holidays', 'Weekly Holidays', array( $this, 'render_weekly_holidays_field' ), $this->plugin_name, 'agmb_schedule_settings_section' );
        add_settings_field( 'agmb_blocked_dates', 'Blocked Dates', array( $this, 'render_blocked_dates_field' ), $this->plugin_name, 'agmb_schedule_settings_section' );

        // API Settings Section
        add_settings_section( 'agmb_api_settings_section', 'API Settings', null, $this->plugin_name );
        add_settings_field( 'agmb_zarinpal_api', 'Zarinpal API Key', array( $this, 'render_zarinpal_api_field' ), $this->plugin_name, 'agmb_api_settings_section' );
        add_settings_field( 'agmb_mediana_api', 'Mediana API Key', array( $this, 'render_mediana_api_field' ), $this->plugin_name, 'agmb_api_settings_section' );
        add_settings_field( 'agmb_mediana_sender', 'Mediana Sender Line', array( $this, 'render_mediana_sender_field' ), $this->plugin_name, 'agmb_api_settings_section' );
        add_settings_field( 'agmb_admin_mobile', 'Admin Mobile', array( $this, 'render_admin_mobile_field' ), $this->plugin_name, 'agmb_api_settings_section' );
        add_settings_field( 'agmb_meeting_link', 'Meeting Link', array( $this, 'render_meeting_link_field' ), $this->plugin_name, 'agmb_general_settings_section' );
        add_settings_field( 'agmb_capacity', 'Capacity per Time Slot', array( $this, 'render_capacity_field' ), $this->plugin_name, 'agmb_schedule_settings_section' );

        // Mediana Endpoint
        add_settings_field( 'agmb_mediana_endpoint', 'Mediana API Endpoint', array( $this, 'render_mediana_endpoint_field' ), $this->plugin_name, 'agmb_api_settings_section' );

        // Templates Section
        add_settings_section( 'agmb_templates_section', 'Message Templates', null, $this->plugin_name );
        add_settings_field( 'agmb_user_sms_template', 'User SMS Template', array( $this, 'render_user_sms_template_field' ), $this->plugin_name, 'agmb_templates_section' );
        add_settings_field( 'agmb_admin_sms_template', 'Admin SMS Template', array( $this, 'render_admin_sms_template_field' ), $this->plugin_name, 'agmb_templates_section' );
        add_settings_field( 'agmb_email_template', 'Email Template', array( $this, 'render_email_template_field' ), $this->plugin_name, 'agmb_templates_section' );
        add_settings_field( 'agmb_receipt_template', 'On-site Receipt Template', array( $this, 'render_receipt_template_field' ), $this->plugin_name, 'agmb_templates_section' );
    }

    public function render_capacity_field() {
        $options = get_option('agmb_settings');
        echo '<input type="number" name="agmb_settings[capacity]" value="' . esc_attr($options['capacity'] ?? '1') . '" min="1">';
    }

    public function render_mediana_endpoint_field() {
        $options = get_option('agmb_settings');
        echo '<input type="text" name="agmb_settings[mediana_endpoint]" value="' . esc_attr($options['mediana_endpoint'] ?? 'http://api.mediana.ir/v1/messages') . '" size="50">';
    }

    public function render_user_sms_template_field() {
        $options = get_option('agmb_settings');
        echo '<textarea name="agmb_settings[user_sms_template]" rows="5" cols="50">' . esc_textarea($options['user_sms_template'] ?? '') . '</textarea>';
    }

    public function render_admin_sms_template_field() {
        $options = get_option('agmb_settings');
        echo '<textarea name="agmb_settings[admin_sms_template]" rows="5" cols="50">' . esc_textarea($options['admin_sms_template'] ?? '') . '</textarea>';
    }

    public function render_email_template_field() {
        $options = get_option('agmb_settings');
        echo '<textarea name="agmb_settings[email_template]" rows="8" cols="50">' . esc_textarea($options['email_template'] ?? '') . '</textarea>';
    }

    public function render_receipt_template_field() {
        $options = get_option('agmb_settings');
        echo '<textarea name="agmb_settings[receipt_template]" rows="5" cols="50">' . esc_textarea($options['receipt_template'] ?? '') . '</textarea>';
    }

    public function render_meeting_link_field() {
        $options = get_option('agmb_settings');
        echo '<input type="text" name="agmb_settings[meeting_link]" value="' . esc_attr($options['meeting_link'] ?? '') . '" size="50">';
    }

    public function render_admin_mobile_field() {
        $options = get_option('agmb_settings');
        echo '<input type="text" name="agmb_settings[admin_mobile]" value="' . esc_attr($options['admin_mobile'] ?? '') . '" size="50">';
    }

    public function render_price_field() {
        $options = get_option('agmb_settings');
        echo '<input type="number" name="agmb_settings[price_per_15_min]" value="' . esc_attr($options['price_per_15_min'] ?? '') . '">';
    }

    public function render_logo_field() {
        $options = get_option('agmb_settings');
        echo '<input type="text" name="agmb_settings[logo_url]" value="' . esc_attr($options['logo_url'] ?? '') . '" size="50">';
    }

    public function render_welcome_text_field() {
        $options = get_option('agmb_settings');
        echo '<textarea name="agmb_settings[welcome_text]" rows="5" cols="50">' . esc_attr($options['welcome_text'] ?? '') . '</textarea>';
    }

    public function render_working_hours_field() {
        $options = get_option('agmb_settings');
        $start = $options['working_hours']['start'] ?? '08:00';
        $end = $options['working_hours']['end'] ?? '24:00';

        $html = 'Start: <select name="agmb_settings[working_hours][start]">';
        $current = new DateTime('00:00');
        $end_time = new DateTime('24:00');
        while ($current <= $end_time) {
            $time_val = $current->format('H:i');
            $selected = ($time_val == $start) ? 'selected' : '';
            $html .= "<option value='{$time_val}' {$selected}>{$time_val}</option>";
            $current->modify('+15 minutes');
        }
        $html .= '</select>';

        $html .= ' End: <select name="agmb_settings[working_hours][end]">';
        $current = new DateTime('00:00');
        while ($current <= $end_time) {
            $time_val = $current->format('H:i');
            $selected = ($time_val == $end) ? 'selected' : '';
            $html .= "<option value='{$time_val}' {$selected}>{$time_val}</option>";
            $current->modify('+15 minutes');
        }
        $html .= '</select>';

        echo $html;
    }

    public function render_weekly_holidays_field() {
        $options = get_option('agmb_settings');
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            $checked = isset($options['weekly_holidays'][$day]) ? 'checked' : '';
            echo "<label><input type='checkbox' name='agmb_settings[weekly_holidays][{$day}]' {$checked}> " . ucfirst($day) . "</label><br>";
        }
    }

    public function render_blocked_dates_field() {
        $options = get_option('agmb_settings');
        echo '<textarea name="agmb_settings[blocked_dates]" rows="5" cols="50" placeholder="Enter dates, one per line (YYYY-MM-DD)">' . esc_attr($options['blocked_dates'] ?? '') . '</textarea>';
    }

    public function render_zarinpal_api_field() {
        $options = get_option('agmb_settings');
        echo '<input type="text" name="agmb_settings[zarinpal_api]" value="' . esc_attr($options['zarinpal_api'] ?? '') . '" size="50">';
    }

    public function render_mediana_api_field() {
        $options = get_option('agmb_settings');
        echo '<input type="text" name="agmb_settings[mediana_api]" value="' . esc_attr($options['mediana_api'] ?? '') . '" size="50">';
    }

    public function render_mediana_sender_field() {
        $options = get_option('agmb_settings');
        echo '<input type="text" name="agmb_settings[mediana_sender]" value="' . esc_attr($options['mediana_sender'] ?? '') . '" size="50">';
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    /**
     * Run the plugin.
     */
    public function run() {
        // Actions and filters will be registered here.
    }

    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'agmb_bookings';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            first_name tinytext NOT NULL,
            last_name tinytext NOT NULL,
            mobile tinytext NOT NULL,
            email tinytext NOT NULL,
            job tinytext,
            education tinytext,
            service tinytext NOT NULL,
            booking_date date NOT NULL,
            booking_time time NOT NULL,
            duration int(11) NOT NULL,
            description text,
            attachment_url varchar(255) DEFAULT '',
            price decimal(10, 2) NOT NULL,
            payment_ref tinytext,
            payment_token tinytext,
            status tinytext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

/**
 * Begins execution of the plugin.
 */
function run_ag_moshtashar_booking_pro() {
	$plugin = new AG_Moshtashar_Booking_Pro();
	$plugin->run();
}

register_activation_hook( __FILE__, array( 'AG_Moshtashar_Booking_Pro', 'activate' ) );

run_ag_moshtashar_booking_pro();

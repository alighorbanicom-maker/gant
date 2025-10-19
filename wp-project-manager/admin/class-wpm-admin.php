<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/admin
 * @author     Jules
 */
class WPM_Admin {

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
     * Handles deleting a project and its tasks.
     *
     * @since    1.0.0
     */
    public function handle_delete_action() {
        if ( isset( $_GET['page'] ) && $_GET['page'] === $this->plugin_name && isset( $_GET['action'] ) && $_GET['action'] === 'delete' ) {

            $project_id = isset( $_GET['project_id'] ) ? absint( $_GET['project_id'] ) : 0;
            // Add a nonce check for security
            // if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'wpm_delete_project_' . $project_id ) ) {
            //     wp_die( 'Security check failed!' );
            // }

            if ( $project_id > 0 ) {
                global $wpdb;
                $project_table = $wpdb->prefix . 'wpm_projects';
                $tasks_table = $wpdb->prefix . 'wpm_tasks';

                // Delete tasks first
                $wpdb->delete( $tasks_table, array( 'project_id' => $project_id ), array( '%d' ) );
                // Delete the project
                $wpdb->delete( $project_table, array( 'id' => $project_id ), array( '%d' ) );
            }

            wp_redirect( admin_url( 'admin.php?page=' . $this->plugin_name . '&status=deleted' ) );
            exit;
        }
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles($hook) {
        if ( 'toplevel_page_' . $this->plugin_name !== $hook ) {
            return;
        }
        wp_enqueue_style( $this->plugin_name . '-kama-datepicker', 'https://unpkg.com/kamadatepicker/dist/kamadatepicker.min.css', array(), '1.5.2' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($hook) {
        // Only load on our plugin's admin page
        if ( 'toplevel_page_' . $this->plugin_name !== $hook ) {
            return;
        }

        $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';

        // Load form script only on add/edit pages
        if ( 'add_new' === $action || 'edit' === $action ) {
            wp_enqueue_script( 'kama-datepicker-js', 'https://unpkg.com/kamadatepicker', array(), '1.5.2', true );
            wp_enqueue_script(
                $this->plugin_name . '-project-form',
                plugin_dir_url( __FILE__ ) . '../../assets/js/wpm-project-form.js',
                array('kama-datepicker-js'),
                $this->version,
                true
            );
            // Initialize the datepicker
            wp_add_inline_script('kama-datepicker-js', "document.addEventListener('DOMContentLoaded', function() { kamaDatepicker('start_date', { nextButtonIcon: 'dashicons-arrow-right-alt2', previousButtonIcon: 'dashicons-arrow-left-alt2', buttonsColor: 'blue', forceFarsiDigits: true }); });");
        }

        // Load Gantt chart script and data only when viewing a project
        if ( 'view' === $action && isset($_GET['project_id']) ) {
            $project_id = absint($_GET['project_id']);

            global $wpdb;
            $tasks_table = $wpdb->prefix . 'wpm_tasks';
            $project_table = $wpdb->prefix . 'wpm_projects';

            $tasks_from_db = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $tasks_table WHERE project_id = %d ORDER BY id ASC", $project_id ), ARRAY_A );
            $project = $wpdb->get_row( $wpdb->prepare( "SELECT creation_date FROM $project_table WHERE id = %d", $project_id ) );

            if ($project && !empty($tasks_from_db)) {
                wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array(), null, true );
                wp_enqueue_script(
                    $this->plugin_name . '-gantt',
                    plugin_dir_url( __FILE__ ) . '../../assets/js/wpm-gantt-chart.js',
                    array( 'google-charts' ),
                    $this->version,
                    true
                );

                $cpm = new WPM_CPM($tasks_from_db);
                $calculated_tasks = $cpm->calculate();

                $gantt_data = [
                    'tasks' => array_values($calculated_tasks),
                    'critical_path' => $cpm->get_critical_path(),
                    'project_start_date' => $project->start_date,
                ];

                wp_localize_script( $this->plugin_name . '-gantt', 'wpm_gantt_data', $gantt_data );
            }
        }
    }

    /**
     * Register the menu for the plugin in the admin area.
     *
     * @since    1.0.0
     */
    public function add_menu() {
        add_menu_page(
            'مدیریت پروژه', // Page Title
            'مدیریت پروژه', // Menu Title
            'manage_options', // Capability
            $this->plugin_name, // Menu Slug
            array( $this, 'display_projects_page' ), // Callback function
            'dashicons-chart-line', // Icon
            25 // Position
        );

        add_submenu_page(
            $this->plugin_name, // Parent Slug
            'پرسش و پاسخ با هوش مصنوعی', // Page Title
            'پرسش و پاسخ با هوش مصنوعی', // Menu Title
            'manage_options', // Capability
            'wpm-ai-qa', // Menu Slug
            array( $this, 'display_ai_qa_page' ) // Callback function
        );
    }

    /**
     * Callback function to display the AI Q&A page.
     *
     * @since    1.0.0
     */
    public function display_ai_qa_page() {
        require_once plugin_dir_path( __FILE__ ) . 'templates/ai-qa-page.php';
    }

    /**
     * Callback function to display the main projects page.
     *
     * @since    1.0.0
     */
    public function display_projects_page() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
        $project_id = isset( $_GET['project_id'] ) ? absint( $_GET['project_id'] ) : 0;

        switch ($action) {
            case 'add_new':
                $page_title = 'افزودن پروژه جدید';
                require_once plugin_dir_path( __FILE__ ) . 'templates/add-new-project-page.php';
                break;
            case 'edit':
                global $wpdb;
                $project_table = $wpdb->prefix . 'wpm_projects';
                $tasks_table = $wpdb->prefix . 'wpm_tasks';

                $project = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $project_table WHERE id = %d", $project_id ) );
                $tasks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $tasks_table WHERE project_id = %d", $project_id ), ARRAY_A );

                $page_title = 'ویرایش پروژه';
                require_once plugin_dir_path( __FILE__ ) . 'templates/add-new-project-page.php';
                break;
            case 'view':
                global $wpdb;
                $project_table = $wpdb->prefix . 'wpm_projects';
                $project = $wpdb->get_row( $wpdb->prepare( "SELECT project_name FROM $project_table WHERE id = %d", $project_id ) );
                $page_title = 'نمایش پروژه: ' . ($project ? esc_html($project->project_name) : '');

                echo '<div class="wrap">';
                echo '<h1>' . $page_title . '</h1>';
                echo '<a href="' . esc_url(admin_url('admin.php?page=' . $this->plugin_name)) . '">&larr; بازگشت به لیست پروژه‌ها</a>';
                echo '<hr>';
                echo '<h2>نمودار گانت</h2>';
                echo '<div id="gantt_chart_div"></div>';
                echo '</div>';
                break;
            default:
                // In a future step, this will be a proper list table.
                require_once plugin_dir_path( __FILE__ ) . 'templates/projects-page.php';
                break;
        }
    }

    /**
     * Handles saving and updating the project data from the form.
     *
     * @since    1.0.0
     */
    public function save_project() {
        // Verify nonce
        if ( ! isset( $_POST['wpm_nonce'] ) || ! wp_verify_nonce( $_POST['wpm_nonce'], 'wpm_save_project_nonce' ) ) {
            wp_die( 'Security check failed!' );
        }

        global $wpdb;
        $project_table = $wpdb->prefix . 'wpm_projects';
        $tasks_table = $wpdb->prefix . 'wpm_tasks';

        // Sanitize and prepare project data
        $project_id = isset( $_POST['project_id'] ) ? absint( $_POST['project_id'] ) : 0;
        $project_name = sanitize_text_field( $_POST['project_name'] );
        $start_date_jalali = sanitize_text_field($_POST['start_date']);
        $start_date_gregorian = wpm_jalali_to_gregorian($start_date_jalali);

        $current_user_id = get_current_user_id();
        $current_time = current_time( 'mysql' );

        $project_data = [
            'project_name'  => $project_name,
            'start_date'    => $start_date_gregorian,
            'last_modified' => $current_time,
        ];

        if ($project_id) { // Update existing project
            $wpdb->update($project_table, $project_data, ['id' => $project_id]);
        } else { // Insert new project
            $project_data['created_by'] = $current_user_id;
            $project_data['creation_date'] = $current_time;
            $wpdb->insert($project_table, $project_data);
            $project_id = $wpdb->insert_id;
        }

        // Handle tasks
        if ( $project_id > 0 && isset( $_POST['tasks'] ) && is_array( $_POST['tasks'] ) ) {
            $existing_task_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $tasks_table WHERE project_id = %d", $project_id ) );
            $submitted_task_ids = [];

            foreach ( $_POST['tasks'] as $task_key => $task_data ) {
                $task_id = (strpos($task_key, 'new_') === 0) ? 0 : absint($task_key);

                $data = [
                    'project_id'   => $project_id,
                    'task_name'    => sanitize_text_field( $task_data['name'] ),
                    'duration'     => absint( $task_data['duration'] ),
                    'dependencies' => sanitize_text_field( $task_data['dependencies'] ),
                    'progress'     => absint( $task_data['progress'] ),
                ];

                if ($task_id && in_array($task_id, $existing_task_ids)) { // Update existing task
                    $wpdb->update($tasks_table, $data, ['id' => $task_id]);
                    $submitted_task_ids[] = $task_id;
                } else { // Insert new task
                    $wpdb->insert($tasks_table, $data);
                    $submitted_task_ids[] = $wpdb->insert_id;
                }
            }

            // Delete tasks that were removed from the form
            $tasks_to_delete = array_diff($existing_task_ids, $submitted_task_ids);
            if (!empty($tasks_to_delete)) {
                $wpdb->query("DELETE FROM $tasks_table WHERE id IN (" . implode(',', $tasks_to_delete) . ")");
            }
        }

        // Redirect back to the projects list page
        wp_redirect( admin_url( 'admin.php?page=' . $this->plugin_name . '&status=saved' ) );
        exit;
    }

    /**
     * Handles the request for the printable version of a project.
     *
     * @since    1.0.0
     */
    public function handle_print_page() {
        if ( isset( $_GET['page'] ) && $_GET['page'] === $this->plugin_name && isset( $_GET['action'] ) && $_GET['action'] === 'print' ) {

            $project_id = isset( $_GET['project_id'] ) ? absint( $_GET['project_id'] ) : 0;
            if ( ! $project_id ) {
                wp_die( 'شناسه پروژه نامعتبر است.' );
            }

            global $wpdb;
            $project_table = $wpdb->prefix . 'wpm_projects';
            $project = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $project_table WHERE id = %d", $project_id ) );

            if ( ! $project ) {
                wp_die( 'پروژه یافت نشد.' );
            }

            $user = get_userdata($project->created_by);

            $tasks_table = $wpdb->prefix . 'wpm_tasks';
            $tasks_from_db = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $tasks_table WHERE project_id = %d ORDER BY id ASC", $project_id ), ARRAY_A );

            // Calculate CPM
            $cpm = new WPM_CPM($tasks_from_db);
            $calculated_tasks = $cpm->calculate();
            $critical_path_ids = $cpm->get_critical_path();

            // Generate Analysis
            $analyzer = new WPM_Project_Analyzer($calculated_tasks, $critical_path_ids);
            $project_analysis = $analyzer->generate_analysis();

            // Prepare data for Gantt chart JS
            $gantt_data = [
                'tasks'              => array_values($calculated_tasks),
                'critical_path'      => $critical_path_ids,
                'project_start_date' => $project->start_date,
            ];

            include_once( plugin_dir_path( __FILE__ ) . 'templates/print-project-page.php' );
            exit; // Stop WordPress from loading the rest of the admin page.
        }
    }

    /**
     * Handles the AI Q&A form submission.
     *
     * @since    1.0.0
     */
    public function handle_ai_qa_submit() {
        // Verify nonce
        if ( ! isset( $_POST['wpm_ai_qa_nonce_field'] ) || ! wp_verify_nonce( $_POST['wpm_ai_qa_nonce_field'], 'wpm_ai_qa_nonce' ) ) {
            wp_die( 'Security check failed!' );
        }

        // Sanitize and retrieve form data
        $project_duration = isset( $_POST['project_duration'] ) ? absint( $_POST['project_duration'] ) : '';
        $project_amount = isset( $_POST['project_amount'] ) ? sanitize_text_field( $_POST['project_amount'] ) : '';
        $project_subject = isset( $_POST['project_subject'] ) ? sanitize_text_field( $_POST['project_subject'] ) : '';
        $contract_type = isset( $_POST['contract_type'] ) ? sanitize_text_field( $_POST['contract_type'] ) : '';
        $project_location = isset( $_POST['project_location'] ) ? sanitize_text_field( $_POST['project_location'] ) : '';
        $project_progress = isset( $_POST['project_progress'] ) ? absint( $_POST['project_progress'] ) : '';
        $questioner_position = isset( $_POST['questioner_position'] ) ? sanitize_text_field( $_POST['questioner_position'] ) : '';
        $question_description = isset( $_POST['question_description'] ) ? sanitize_textarea_field( $_POST['question_description'] ) : '';

        // Generate the response using the keyword-based system
        $ai_response = $this->generate_keyword_based_response($question_description);

        // We will store the response in a transient to display it on the page.
        set_transient( 'wpm_ai_qa_response_' . get_current_user_id(), $ai_response, 60 * 5 ); // Expires in 5 minutes

        // Redirect back to the AI Q&A page
        wp_redirect( admin_url( 'admin.php?page=wpm-ai-qa&status=success' ) );
        exit;
    }

    /**
     * Generates a response based on keywords found in the user's question.
     *
     * @since    1.0.0
     * @param    string    $question    The user's question.
     * @return   string    The generated response.
     */
    private function generate_keyword_based_response($question) {
        $responses = [
            'تاخیر' => 'در صورت تاخیر در انجام پروژه، طبق شرایط عمومی پیمان، کارفرما می‌تواند جریمه‌های مقرر را اعمال کند. برای اطلاعات دقیق‌تر، به ماده ۵۰ شرایط عمومی پیمان مراجعه کنید.',
            'جریمه' => 'جریمه‌های تاخیر در پروژه، بر اساس درصدی از مبلغ پیمان و مدت زمان تاخیر محاسبه می‌شود. جزئیات کامل در قرارداد و شرایط عمومی پیمان ذکر شده است.',
            'فسخ' => 'فسخ قرارداد، تحت شرایط خاصی مانند تاخیر بیش از حد مجاز یا عدم انجام تعهدات توسط یکی از طرفین، امکان‌پذیر است. برای اطلاع از شرایط دقیق، به ماده ۴۶ شرایط عمومی پیمان مراجعه کنید.',
            'پیش پرداخت' => 'پیش پرداخت، معمولاً درصدی از مبلغ کل قرارداد است که در ابتدای کار به پیمانکار پرداخت می‌شود تا برای تجهیز کارگاه و شروع عملیات اجرایی، نقدینگی لازم را داشته باشد.',
        ];

        foreach ($responses as $keyword => $response) {
            if (strpos($question, $keyword) !== false) {
                return $response;
            }
        }

        return 'متاسفانه پاسخ دقیقی برای سوال شما یافت نشد. لطفاً سوال خود را با جزئیات بیشتری مطرح کنید یا با دکتر علی قربانی برای مشاوره تخصصی تماس بگیرید.';
    }
}
?>
<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    AG_Moshtashar_Booking
 * @subpackage AG_Moshtashar_Booking/public
 */

class AGMB_Public {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action( 'wp_ajax_agmb_get_available_times', array( $this, 'get_available_times' ) );
        add_action( 'wp_ajax_nopriv_agmb_get_available_times', array( $this, 'get_available_times' ) );
        add_action( 'wp_ajax_agmb_submit_booking', array( $this, 'submit_booking' ) );
        add_action( 'wp_ajax_nopriv_agmb_submit_booking', array( $this, 'submit_booking' ) );

        add_action( 'wp_ajax_agmb_verify_payment', array( $this, 'verify_payment' ) );
        add_action( 'wp_ajax_nopriv_agmb_verify_payment', array( $this, 'verify_payment' ) );
    }

    public function verify_payment() {
        $token = sanitize_text_field($_GET['token']);
        $authority = sanitize_text_field($_GET['Authority']);
        $status = sanitize_text_field($_GET['Status']);

        if ($status !== 'OK') {
            wp_die('پرداخت ناموفق بود.');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'agmb_bookings';
        $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE payment_token = %s", $token));

        if (!$booking) {
            wp_die('اطلاعات رزرو یافت نشد.');
        }

        $options = get_option('agmb_settings');
        $zarinpal_api_key = $options['zarinpal_api'] ?? '';

        $response = wp_remote_post('https://api.zarinpal.com/pg/v4/payment/verify.json', array(
            'method' => 'POST',
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                "merchant_id" => $zarinpal_api_key,
                "amount" => $booking->price,
                "authority" => $authority
            ))
        ));

        if (is_wp_error($response)) {
            wp_die('خطا در تایید پرداخت.');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['data']['code']) && ($body['data']['code'] == 100 || $body['data']['code'] == 101)) {
            $ref_id = $body['data']['ref_id'];
            $wpdb->update($table_name,
                array('status' => 'confirmed', 'payment_ref' => $ref_id),
                array('id' => $booking->id)
            );

            $booking_data = (array) $booking;
            $this->send_notifications($booking_data, $ref_id, $booking->price);

            $receipt_template = $options['receipt_template'] ?? 'رزرو شما با کد رهگیری {ref} با موفقیت ثبت شد.';
            $receipt_message = str_replace('{ref}', $ref_id, $receipt_template);

            // Redirect back to the booking page with a success message
            $redirect_url = remove_query_arg(array('Authority', 'Status', 'token'));
            wp_redirect(add_query_arg(array('booking_status' => 'success', 'message' => urlencode($receipt_message)), $redirect_url));
            exit;
        } else {
            wp_die('تایید پرداخت ناموفق بود. خطا: ' . ($body['errors']['message'] ?? 'خطای نامشخص'));
        }
    }

    private function send_notifications($booking_data, $ref_id, $price) {
        $options = get_option('agmb_settings');

        $jdate = $this->format_jalali_date($booking_data['date']);
        $meeting_link = $options['meeting_link'] ?? '';

        $replacements = array(
            '{first_name}' => $booking_data['first_name'],
            '{last_name}' => $booking_data['last_name'],
            '{service}' => $booking_data['service'],
            '{dow}' => explode(' - ', $jdate)[0],
            '{jdate}' => explode(' - ', $jdate)[1],
            '{time}' => $booking_data['time'],
            '{duration}' => $booking_data['duration'],
            '{ref}' => $ref_id,
            '{price}' => number_format($price),
            '{meeting_link}' => $meeting_link,
            '{mobile}' => $booking_data['mobile'],
        );

        // --- Send Email ---
        $to = $booking_data['email'];
        $subject = 'تایید رزرو شما – سامانه مستشار دکتر علی قربانی';
        $email_template = $options['email_template'] ?? '';
        $body = str_replace(array_keys($replacements), array_values($replacements), $email_template);
        wp_mail($to, $subject, $body);

        // --- Send SMS ---
        $mediana_api = $options['mediana_api'] ?? '';
        $mediana_sender = $options['mediana_sender'] ?? '';
        $mediana_endpoint = $options['mediana_endpoint'] ?? 'http://api.mediana.ir/v1/messages';

        // SMS to User
        $user_sms_template = $options['user_sms_template'] ?? '';
        $user_message = str_replace(array_keys($replacements), array_values($replacements), $user_sms_template);
        wp_remote_post($mediana_endpoint, array(
            'method' => 'POST',
            'headers' => array('Authorization' => 'Bearer ' . $mediana_api),
            'body' => array(
                'originator' => $mediana_sender,
                'recipients' => array($booking_data['mobile']),
                'message' => $user_message
            )
        ));

        // SMS to Admin
        $admin_mobile = $options['admin_mobile'] ?? '';
        if (!empty($admin_mobile)) {
            $admin_sms_template = $options['admin_sms_template'] ?? '';
            $admin_message = str_replace(array_keys($replacements), array_values($replacements), $admin_sms_template);
            wp_remote_post($mediana_endpoint, array(
                'method' => 'POST',
                'headers' => array('Authorization' => 'Bearer ' . $mediana_api),
                'body' => array(
                    'originator' => $mediana_sender,
                    'recipients' => array($admin_mobile),
                    'message' => $admin_message
                )
            ));
        }
    }

    private function format_jalali_date($gregorian_date_str, $format = 'Y/m/d') {
        require_once AGMB_PLUGIN_DIR . 'includes/gregorian_jalali.php';
        list($y, $m, $d) = explode('-', $gregorian_date_str);
        $jalali_date = gregorian_to_jalali($y, $m, $d);

        $days_of_week = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه'];
        $day_of_week_en = date('w', strtotime($gregorian_date_str));
        $day_of_week_fa = $days_of_week[$day_of_week_en];

        return $day_of_week_fa . ' - ' . implode('/', $jalali_date);
    }

    public function submit_booking() {
        check_ajax_referer( 'agmb_ajax_nonce', 'nonce' );

        // Sanitize and validate all form fields
        $form_data = array();
        $required_fields = ['first_name', 'last_name', 'mobile', 'email', 'service', 'date', 'time', 'duration'];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error( array( 'message' => 'لطفاً تمام فیلدهای ستاره‌دار را تکمیل کنید.' ) );
            }
            $form_data[$field] = sanitize_text_field($_POST[$field]);
        }

        $options = get_option('agmb_settings');

        // Handle free service
        if ($form_data['service'] === 'thesis') {
            global $wpdb;
            $table_name = $wpdb->prefix . 'agmb_bookings';
            $wpdb->insert($table_name, array(
                'first_name' => $form_data['first_name'],
                'last_name' => $form_data['last_name'],
                'mobile' => $form_data['mobile'],
                'email' => $form_data['email'],
                'job' => $form_data['job'] ?? '',
                'education' => $form_data['education'] ?? '',
                'service' => $form_data['service'],
                'booking_date' => $form_data['date'],
                'booking_time' => $form_data['time'],
                'duration' => $form_data['duration'],
                'description' => $form_data['description'] ?? '',
                'price' => 0,
                'status' => 'confirmed',
            ));
            $this->send_notifications($form_data, 'N/A', 0);
            wp_send_json_success( array( 'message' => 'رزرو شما با موفقیت ثبت شد.' ) );
            return;
        }

        // Price calculation
        $price_per_15_min = intval($options['price_per_15_min'] ?? 0);
        $duration = intval($form_data['duration']);
        $total_price = ($duration / 15) * $price_per_15_min;

        // Save pending booking
        global $wpdb;
        $table_name = $wpdb->prefix . 'agmb_bookings';
        $token = wp_generate_password( 32, false );

        $wpdb->insert($table_name, array(
            'first_name' => $form_data['first_name'],
            'last_name' => $form_data['last_name'],
            'mobile' => $form_data['mobile'],
            'email' => $form_data['email'],
            'job' => $form_data['job'] ?? '',
            'education' => $form_data['education'] ?? '',
            'service' => $form_data['service'],
            'booking_date' => $form_data['date'],
            'booking_time' => $form_data['time'],
            'duration' => $form_data['duration'],
            'description' => $form_data['description'] ?? '',
            'price' => $total_price,
            'status' => 'pending',
            'payment_token' => $token
        ));
        $booking_id = $wpdb->insert_id;

        $zarinpal_api_key = $options['zarinpal_api'] ?? '';

        $callback_url = add_query_arg( array(
            'action' => 'agmb_verify_payment',
            'token' => $token
        ), admin_url( 'admin-ajax.php' ) );

        $request_body = json_encode(array(
            "merchant_id" => $zarinpal_api_key,
            "amount" => $total_price,
            "currency" => "IRT",
            "callback_url" => $callback_url,
            "description" => "Booking for " . $form_data['service'],
            "metadata" => [
                "mobile" => $form_data['mobile'],
                "email" => $form_data['email']
            ]
        ));

        $response = wp_remote_post( 'https://api.zarinpal.com/pg/v4/payment/request.json', array(
            'method'    => 'POST',
            'headers'   => array('Content-Type' => 'application/json'),
            'body'      => $request_body,
        ));

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => 'خطا در اتصال به درگاه پرداخت.' ) );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( !empty($body['data']['authority']) ) {
            $payment_url = 'https://www.zarinpal.com/pg/StartPay/' . $body['data']['authority'];
            wp_send_json_success( array( 'payment_url' => $payment_url ) );
        } else {
            wp_send_json_error( array( 'message' => 'خطا از درگاه پرداخت: ' . ($body['errors']['message'] ?? 'خطای نامشخص') ) );
        }
    }

    public function get_available_times() {
        check_ajax_referer( 'agmb_ajax_nonce', 'nonce' );

        require_once AGMB_PLUGIN_DIR . 'includes/gregorian_jalali.php';
        $jalali_date_str = sanitize_text_field( $_POST['date'] );
        // The datepicker returns YYYY/MM/DD, which is what the library expects.
        // Let's ensure it's correctly formatted before exploding.
        $jalali_parts = explode('/', $jalali_date_str);
        if (count($jalali_parts) !== 3) {
            wp_send_json_error(['message' => 'تاریخ نامعتبر است.']);
            return;
        }
        list($jy, $jm, $jd) = $jalali_parts;
        $gregorian_date_arr = jalali_to_gregorian((int)$jy, (int)$jm, (int)$jd);
        $date_str = $gregorian_date_arr[0] . '-' . str_pad($gregorian_date_arr[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($gregorian_date_arr[2], 2, '0', STR_PAD_LEFT);

        $options = get_option( 'agmb_settings' );
        $start_time_str = $options['working_hours']['start'] ?? '09:00';
        $end_time_str = $options['working_hours']['end'] ?? '17:00';
        $weekly_holidays = $options['weekly_holidays'] ?? [];
        $blocked_dates_str = $options['blocked_dates'] ?? '';
        $blocked_dates = !empty($blocked_dates_str) ? explode("\n", $blocked_dates_str) : [];

        $day_of_week = strtolower( date( 'l', strtotime( $date_str ) ) );

        if ( in_array( $day_of_week, array_keys( $weekly_holidays ) ) ) {
            wp_send_json_success( [] ); // It's a holiday
        }

        if ( in_array( $date_str, $blocked_dates ) ) {
            wp_send_json_success( [] ); // It's a blocked date
        }

        $available_times = [];
        $current_time = new DateTime( $start_time_str );
        $end_time = new DateTime( $end_time_str );

        while ( $current_time < $end_time ) {
            $available_times[] = $current_time->format( 'H:i' );
            $current_time->modify( '+15 minutes' );
        }

        // Get count of bookings for each time slot
        global $wpdb;
        $table_name = $wpdb->prefix . 'agmb_bookings';
        $bookings_count = $wpdb->get_results( $wpdb->prepare(
            "SELECT booking_time, COUNT(id) as count FROM $table_name WHERE booking_date = %s AND status = 'confirmed' GROUP BY booking_time",
            $date_str
        ), OBJECT_K );

        $capacity = $options['capacity'] ?? 1;

        // Filter out booked times
        $available_times = array_filter($available_times, function($time) use ($bookings_count, $capacity) {
            $time_key = $time . ':00';
            $count = $bookings_count[$time_key]->count ?? 0;
            return $count < $capacity;
        });

        wp_send_json_success( array_values($available_times) );
    }

    public function enqueue_styles() {
        wp_enqueue_style( 'jalalidatepicker-css', 'https://cdn.jsdelivr.net/npm/jalalidatepicker@0.6.0/dist/jalalidatepicker.min.css', array(), '0.6.0', 'all' );
        wp_enqueue_style( $this->plugin_name, AGMB_PLUGIN_URL . 'public/css/agmb-public.css', array('jalalidatepicker-css'), $this->version, 'all' );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'jalalidatepicker-js', 'https://cdn.jsdelivr.net/npm/jalalidatepicker@0.6.0/dist/jalalidatepicker.min.js', array(), '0.6.0', true );
        wp_enqueue_script( $this->plugin_name, AGMB_PLUGIN_URL . 'public.js', array( 'jquery', 'jalalidatepicker-js' ), $this->version, true );

        wp_localize_script( $this->plugin_name, 'agmb_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'agmb_ajax_nonce' )
        ));
    }

    public function render_booking_form() {
        ob_start();
        include_once 'partials/agmb-public-display.php';
        return ob_get_clean();
    }
}

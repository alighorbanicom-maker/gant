<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    AG_Moshtashar_Booking
 * @subpackage AG_Moshtashar_Booking/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'agmb_settings_group' );
        do_settings_sections( 'ag-moshtashar-booking' );
        submit_button( 'Save Settings' );
        ?>
    </form>
</div>

<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    AG_Moshtashar_Booking
 * @subpackage AG_Moshtashar_Booking/public/partials
 */
?>

<div id="ag-booking-form-wrapper">
    <div class="ag-brand-header">
        <?php
            $options = get_option('agmb_settings');
            $logo_url = esc_url($options['logo_url'] ?? '');
            $welcome_text = esc_html($options['welcome_text'] ?? '📜 سامانه مستشار دکتر علی قربانی');
            if ($logo_url) {
                echo '<img src="' . $logo_url . '" alt="Logo" style="max-width: 150px; margin-bottom: 10px;">';
            }
            echo '<h2>' . $welcome_text . '</h2>';
        ?>
    </div>

    <form id="ag-booking-form" action="" method="post" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-field">
                <label for="agmb_first_name">نام</label>
                <input type="text" id="agmb_first_name" name="first_name" required>
            </div>
            <div class="form-field">
                <label for="agmb_last_name">نام خانوادگی</label>
                <input type="text" id="agmb_last_name" name="last_name" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label for="agmb_mobile">موبایل</label>
                <input type="tel" id="agmb_mobile" name="mobile" required>
            </div>
            <div class="form-field">
                <label for="agmb_email">ایمیل</label>
                <input type="email" id="agmb_email" name="email" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label for="agmb_job">شغل</label>
                <input type="text" id="agmb_job" name="job">
            </div>
            <div class="form-field">
                <label for="agmb_education">تحصیلات</label>
                <input type="text" id="agmb_education" name="education">
            </div>
        </div>

        <div class="form-field">
            <label for="agmb_service">خدمت</label>
            <select id="agmb_service" name="service" required>
                <option value="">انتخاب کنید...</option>
                <option value="consultation">مشاوره</option>
                <option value="thesis">امور دانشجویی و پایان‌نامه‌ای</option>
                <!-- More services can be added here -->
            </select>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label for="agmb_date">تاریخ</label>
                <input type="text" id="agmb_date" name="date" required readonly>
            </div>
            <div class="form-field">
                <label for="agmb_time">ساعت</label>
                <select id="agmb_time" name="time" required disabled>
                    <option value="">ابتدا تاریخ را انتخاب کنید</option>
                </select>
            </div>
            <div class="form-field">
                <label for="agmb_duration">مدت (دقیقه)</label>
                <select id="agmb_duration" name="duration" required>
                    <option value="15">15</option>
                    <option value="30">30</option>
                    <option value="45">45</option>
                    <option value="60">60</option>
                </select>
            </div>
        </div>

        <div class="form-field">
            <label for="agmb_description">توضیح</label>
            <textarea id="agmb_description" name="description" rows="4"></textarea>
        </div>

        <div class="form-field">
            <label for="agmb_attachment">فایل پیوست</label>
            <input type="file" id="agmb_attachment" name="attachment">
        </div>

        <div class="form-field">
            <button type="submit" class="ag-submit-button">پرداخت و رزرو</button>
        </div>
    </form>
    <div id="ag-popup-message" class="ag-popup" style="display:none;">
        <div class="ag-popup-content">
            <span class="ag-popup-close">&times;</span>
            <p id="ag-popup-text"></p>
        </div>
    </div>
</div>

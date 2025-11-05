=== AG Moshtashar Booking Pro ===
Contributors: Dr. Ali Ghorbani
Tags: booking, appointment, zarinpal, mediana, jalali calendar
Requires at least: 5.0
Tested up to: 6.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive booking and consultation system for Dr. Ali Ghorbani, featuring a Jalali calendar, online payments via Zarinpal, and SMS notifications.

== Description ==

This plugin provides a complete booking solution for professional consultations. It includes:
*   A user-friendly booking form with a Jalali (Persian) date picker.
*   Seamless integration with the Zarinpal payment gateway.
*   Automated email and SMS confirmations via the Mediana SMS service.
*   A comprehensive admin panel to manage all settings, including pricing, schedules, holidays, and API keys.

== Installation ==

1.  Upload the `ag-moshtashar-booking` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to the "AG Booking" settings page in the admin dashboard to configure the plugin.

== Configuration ==

After activation, a new menu item named "AG Booking" will appear in your WordPress dashboard.

1.  **General Settings**:
    *   **Price per 15 minutes**: Set the base price for a 15-minute time slot.
    *   **Logo URL**: Enter the URL for the logo to be displayed at the top of the form.
    *   **Welcome Text**: Enter the introductory text to be displayed below the logo.
    *   **Meeting Link**: Enter the default URL for the online meeting.

2.  **Schedule Settings**:
    *   **Working Hours**: Set the start and end times for your availability.
    *   **Weekly Holidays**: Check the boxes for the days of the week you are unavailable.
    *   **Blocked Dates**: Enter specific dates you are unavailable, one per line, in the format YYYY-MM-DD.

3.  **API Settings**:
    *   **Zarinpal API Key**: Enter your Merchant ID from Zarinpal.
    *   **Mediana API Key**: Enter your API key from Mediana.
    *   **Mediana Sender Line**: Enter the sender number provided by Mediana.
    *   **Admin Mobile**: Enter the mobile number to receive admin notifications for new bookings.

== Usage ==

To display the booking form on any page or post, simply use the following shortcode:
`[ag_booking]`

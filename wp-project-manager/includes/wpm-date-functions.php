<?php
/**
 * Date and time helper functions for the plugin.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/includes
 */

if ( ! function_exists( 'wpm_gregorian_to_jalali' ) ) {
    function wpm_gregorian_to_jalali( $gregorian_date, $format = 'Y/m/d' ) {
        if ( ! $gregorian_date || $gregorian_date === '0000-00-00 00:00:00' || $gregorian_date === '0000-00-00' ) {
            return '';
        }

        $timestamp = strtotime( $gregorian_date );
        $g_y = date( 'Y', $timestamp );
        $g_m = date( 'm', $timestamp );
        $g_d = date( 'd', $timestamp );

        $g_days_in_month = array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
        $j_days_in_month = array( 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29 );

        $gy = $g_y - 1600;
        $gm = $g_m - 1;
        $gd = $g_d - 1;

        $g_day_no = 365 * $gy + floor( ( $gy + 3 ) / 4 ) - floor( ( $gy + 99 ) / 100 ) + floor( ( $gy + 399 ) / 400 );

        for ( $i = 0; $i < $gm; ++$i ) {
            $g_day_no += $g_days_in_month[ $i ];
        }
        if ( $gm > 1 && ( ( $gy % 4 == 0 && $gy % 100 != 0 ) || ( $gy % 400 == 0 ) ) ) {
            $g_day_no++;
        }
        $g_day_no += $gd;

        $j_day_no = $g_day_no - 79;
        $j_np = floor( $j_day_no / 12053 );
        $j_day_no = $j_day_no % 12053;
        $jy = 979 + 33 * $j_np + 4 * floor( $j_day_no / 1461 );
        $j_day_no %= 1461;

        if ( $j_day_no >= 366 ) {
            $jy += floor( ( $j_day_no - 1 ) / 365 );
            $j_day_no = ( $j_day_no - 1 ) % 365;
        }

        for ( $i = 0; $i < 11 && $j_day_no >= $j_days_in_month[ $i ]; ++$i ) {
            $j_day_no -= $j_days_in_month[ $i ];
        }
        $jm = $i + 1;
        $jd = $j_day_no + 1;

        return str_replace(['Y', 'm', 'd'], [$jy, str_pad($jm, 2, '0', STR_PAD_LEFT), str_pad($jd, 2, '0', STR_PAD_LEFT)], $format);
    }
}


if ( ! function_exists( 'wpm_jalali_to_gregorian' ) ) {
    function wpm_jalali_to_gregorian($jalali_date, $format = 'Y-m-d') {
        if(empty($jalali_date)) return '';

        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $english = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $jalali_date = str_replace($persian, $english, $jalali_date);

        $parts = preg_split('/[^0-9]/', $jalali_date);
        if(count($parts) !== 3) return '';

        list($jy, $jm, $jd) = array_map('intval', $parts);

        $j_days_in_month = array(0, 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

        $jy += 1595;
        $days = -355668 + (365 * $jy) + floor($jy / 33) * 8 + floor((($jy % 33) + 3) / 4) + $jd;
        if ($jm > 6) {
            $days += ($jm - 1) * 31;
            $days += ($jm - 7) * (-1);
        } else {
            $days += ($jm - 1) * 31;
        }

        $gy = 400 * floor($days / 146097);
        $days %= 146097;
        if ($days > 36524) {
            $gy += 100 * floor(--$days / 36524);
            $days %= 36524;
            if ($days >= 365) $days++;
        }
        $gy += 4 * floor($days / 1461);
        $days %= 1461;
        if ($days > 365) {
            $gy += floor(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        $gd = $days + 1;
        $sal_a = array(0, 31, ($gy % 4 == 0 && $gy % 100 != 0 || $gy % 400 == 0) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        for ($gm = 1; $gm <= 12 && $gd > $sal_a[$gm]; $gm++) {
            $gd -= $sal_a[$gm];
        }

        return str_replace(['Y', 'm', 'd'], [$gy, str_pad($gm, 2, '0', STR_PAD_LEFT), str_pad($gd, 2, '0', STR_PAD_LEFT)], $format);
    }
}
?>
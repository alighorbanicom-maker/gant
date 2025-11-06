jQuery(document).ready(function($) {
    // Initialize Jalali Date Picker
    jalaliDatepicker.startWatch({
        selector: '#agmb_date',
        minDate: 'today',
        showEmptyBtn: false,
        autoHide: true,
    });

    // Handle date selection to fetch available times
    $('#agmb_date').on('change', function() {
        const selectedDate = $(this).val();
        const timeSelect = $('#agmb_time');

        if (!selectedDate) {
            timeSelect.html('<option value="">ابتدا تاریخ را انتخاب کنید</option>').prop('disabled', true);
            return;
        }

        $.ajax({
            url: agmb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'agmb_get_available_times',
                date: selectedDate,
                nonce: agmb_ajax.nonce
            },
            beforeSend: function() {
                timeSelect.html('<option value="">در حال بارگذاری...</option>').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">انتخاب کنید...</option>';
                    if (response.data.length > 0) {
                        response.data.forEach(function(time) {
                            options += `<option value="${time}">${time}</option>`;
                        });
                        timeSelect.prop('disabled', false);
                    } else {
                        options = '<option value="">زمانی برای این روز وجود ندارد</option>';
                    }
                    timeSelect.html(options);
                } else {
                    showPopup(response.data.message || 'خطا در دریافت زمان‌ها');
                    timeSelect.html('<option value="">خطا</option>').prop('disabled', true);
                }
            },
            error: function() {
                showPopup('خطای سرور. لطفاً دوباره تلاش کنید.');
                timeSelect.html('<option value="">خطا</option>').prop('disabled', true);
            }
        });
    });

    // Handle form submission
    $('#ag-booking-form').on('submit', function(e) {
        e.preventDefault();

        // Basic client-side validation
        let isValid = true;
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '#ccc');
            }
        });

        if (!isValid) {
            showPopup('لطفاً تمام فیلدهای ستاره‌دار را تکمیل کنید.');
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'agmb_submit_booking');
        formData.append('nonce', agmb_ajax.nonce);

        $.ajax({
            url: agmb_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success && response.data.payment_url) {
                    window.location.href = response.data.payment_url;
                } else {
                    showPopup(response.data.message || 'خطایی در ایجاد تراکنش رخ داده است.');
                }
            },
            error: function() {
                showPopup('خطای سرور. لطفاً دوباره تلاش کنید.');
            }
        });
    });

    // Popup functionality
    function showPopup(message) {
        $('#ag-popup-text').text(message);
        $('#ag-popup-message').fadeIn();
    }

    $('.ag-popup-close').on('click', function() {
        $('#ag-popup-message').fadeOut();
    });

    $(window).on('click', function(event) {
        if ($(event.target).is('#ag-popup-message')) {
            $('#ag-popup-message').fadeOut();
        }
    });

    // Check for success message in URL on page load
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('booking_status') === 'success') {
        const message = urlParams.get('message');
        if (message) {
            showPopup(decodeURIComponent(message));
        }
    }
});

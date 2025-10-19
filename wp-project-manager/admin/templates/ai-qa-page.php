<?php
// Get current Jalali date for "تاریخ پرسش"
$question_date = wpm_gregorian_to_jalali(current_time('mysql'), 'Y/m/d');
?>
<div class="wrap" id="ai-qa-page">
    <div id="printable-area">
        <div class="header-print">
            <p><strong>کاری از دکتر علی قربانی نویسنده کتاب ادعای پیمانکاران</strong></p>
            <p><a href="http://www.alighorbani.com" target="_blank">www.alighorbani.com</a></p>
        </div>
        <hr>
        <h1>پرسش و پاسخ با هوش مصنوعی</h1>

        <div class="qa-section">
            <p><strong>تاریخ پرسش:</strong> <?php echo esc_html($question_date); ?></p>
            <p><strong>تاریخ پاسخ:</strong> <span id="response-date">--/--/----</span></p>
        </div>

        <form id="ai-qa-form" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <input type="hidden" name="action" value="wpm_ai_qa_submit">
            <?php wp_nonce_field( 'wpm_ai_qa_nonce', 'wpm_ai_qa_nonce_field' ); ?>
            <table class="form-table">
                <tbody>
                    <!-- Project Information -->
                    <tr>
                        <th scope="row"><label for="project_duration">مدت پروژه (روز)</label></th>
                        <td><input name="project_duration" type="number" id="project_duration" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="project_amount">مبلغ پروژه (ریال)</label></th>
                        <td><input name="project_amount" type="number" id="project_amount" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="project_subject">موضوع قرارداد</label></th>
                        <td><input name="project_subject" type="text" id="project_subject" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="contract_type">نوع قرارداد</label></th>
                        <td><input name="contract_type" type="text" id="contract_type" class="regular-text" placeholder="مثال: فهرست بهایی، EPC, ..."></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="project_location">محل پروژه</label></th>
                        <td><input name="project_location" type="text" id="project_location" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="project_progress">درصد پیشرفت پروژه</label></th>
                        <td><input name="project_progress" type="number" id="project_progress" min="0" max="100" class="small-text"> %</td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="questioner_position">جایگاه قراردادی پرسش کننده</label></th>
                        <td>
                            <select name="questioner_position" id="questioner_position">
                                <option value="">انتخاب کنید</option>
                                <option value="کارفرما">کارفرما</option>
                                <option value="مشاور">مشاور</option>
                                <option value="پیمانکار">پیمانکار</option>
                                <option value="وکیل">وکیل</option>
                                <option value="سایر">سایر</option>
                            </select>
                        </td>
                    </tr>

                    <!-- Question -->
                    <tr>
                        <th scope="row"><label for="question_description">شرح سوال و ابهام</label></th>
                        <td><textarea name="question_description" id="question_description" rows="10" cols="50" class="large-text" required></textarea></td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button('ارسال سوال'); ?>
        </form>

        <hr>

        <div id="ai-response-section">
            <h2>پاسخ هوش مصنوعی:</h2>
            <div id="ai-response-content" style="background-color: #f9f9f9; border: 1px solid #ccc; padding: 15px; border-radius: 4px; min-height: 100px;">
                <?php
                $ai_response = get_transient( 'wpm_ai_qa_response_' . get_current_user_id() );
                if ( $ai_response ) {
                    echo nl2br( esc_textarea( $ai_response ) );
                    // Delete the transient so it's only shown once.
                    delete_transient( 'wpm_ai_qa_response_' . get_current_user_id() );
                } else {
                    echo '<p>پاسخ پس از ارسال سوال در اینجا نمایش داده خواهد شد.</p>';
                }
                ?>
            </div>
            <p style="font-size: 0.9em; color: #777; margin-top: 15px;">
                <em><strong>سلب مسئولیت:</strong> این پاسخ تا زمان اخذ مشاوره حضوری یا مجازی از دکتر علی قربانی، سندیت ندارد و صرفا جنبه استرشادی دارد.</em>
            </p>
        </div>
    </div>
    <button class="button button-secondary" onclick="printPage()">چاپ / ذخیره PDF</button>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-area, #printable-area * {
                visibility: visible;
            }
            #printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .header-print {
                text-align: center;
                margin-bottom: 20px;
            }
            .header-print p {
                margin: 5px 0;
                font-size: 1.2em;
            }
        }
    </style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</div>

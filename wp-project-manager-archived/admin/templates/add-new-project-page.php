<?php
/**
 * The template for displaying the add/edit project form.
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/admin/templates
 * @author     Jules
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( $page_title ); ?></h1>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

        <input type="hidden" name="action" value="wpm_save_project">
        <?php if (isset($project) && $project->id) : ?>
            <input type="hidden" name="project_id" value="<?php echo $project->id; ?>">
        <?php endif; ?>
        <?php wp_nonce_field( 'wpm_save_project_nonce', 'wpm_nonce' ); ?>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="project_name">نام پروژه</label>
                    </th>
                    <td>
                        <input type="text" id="project_name" name="project_name" class="regular-text" value="<?php echo isset($project) ? esc_attr($project->project_name) : ''; ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="start_date">تاریخ شروع پروژه</label>
                    </th>
                    <td>
                        <input type="text" id="start_date" name="start_date" class="regular-text" value="<?php echo isset($project) ? esc_attr(wpm_gregorian_to_jalali($project->start_date, 'Y/m/d')) : ''; ?>" required>
                        <p class="description">تاریخ را با فرمت YYYY/MM/DD وارد کنید یا از تقویم انتخاب نمایید.</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h2>فعالیت‌ها</h2>
        <table id="tasks-table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th>نام فعالیت</th>
                    <th style="width: 10%;">مدت زمان (روز)</th>
                    <th style="width: 15%;">وابستگی‌ها (ID)</th>
                    <th style="width: 10%;">پیشرفت (%)</th>
                    <th style="width: 10%;"></th>
                </tr>
            </thead>
            <tbody id="tasks-container">
                <?php if (isset($tasks) && !empty($tasks)) : ?>
                    <?php foreach ($tasks as $task) : ?>
                        <tr>
                            <td><span class="task-id"><?php echo $task['id']; ?></span></td>
                            <td><input type="text" name="tasks[<?php echo $task['id']; ?>][name]" class="large-text" value="<?php echo esc_attr($task['task_name']); ?>" required /></td>
                            <td><input type="number" name="tasks[<?php echo $task['id']; ?>][duration]" min="1" value="<?php echo esc_attr($task['duration']); ?>" class="small-text" required /></td>
                            <td><input type="text" name="tasks[<?php echo $task['id']; ?>][dependencies]" value="<?php echo esc_attr($task['dependencies']); ?>" placeholder="مثال: 1,2" /></td>
                            <td><input type="number" name="tasks[<?php echo $task['id']; ?>][progress]" min="0" max="100" value="<?php echo esc_attr($task['progress']); ?>" class="small-text" /></td>
                            <td><button type="button" class="button button-link-delete remove-task-btn">حذف</button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <button type="button" id="add-task-btn" class="button button-secondary" style="margin-top: 10px;">افزودن فعالیت</button>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="ذخیره پروژه">
        </p>

    </form>
</div>
<?php
/**
 * The template for displaying the main projects page in the admin area.
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/admin/templates
 * @author     Jules
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <!-- This button will be used to open the "Add New Project" form -->
    <a href="?page=wp-project-manager&action=add_new" class="page-title-action">افزودن پروژه جدید</a>

    <?php
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    if ($status === 'saved') {
        echo '<div id="message" class="updated notice is-dismissible"><p>پروژه با موفقیت ذخیره شد.</p></div>';
    } elseif ($status === 'deleted') {
        echo '<div id="message" class="updated notice is-dismissible"><p>پروژه با موفقیت حذف شد.</p></div>';
    }
    ?>

    <form method="post">
        <?php
        require_once plugin_dir_path( __FILE__ ) . '../class-wpm-projects-list-table.php';
        $list_table = new WPM_Projects_List_Table();
        $list_table->prepare_items();
        $list_table->display();
        ?>
    </form>
</div>
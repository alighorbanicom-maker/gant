<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPM_Projects_List_Table extends WP_List_Table {

    function __construct() {
        parent::__construct( [
            'singular' => 'پروژه',
            'plural'   => 'پروژه‌ها',
            'ajax'     => false
        ] );
    }

    function get_columns() {
        return [
            'cb'            => '<input type="checkbox" />',
            'project_name'  => 'نام پروژه',
            'created_by'    => 'ثبت کننده',
            'creation_date' => 'تاریخ ثبت',
            'task_count'    => 'تعداد فعالیت'
        ];
    }

    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'creation_date':
                return wpm_gregorian_to_jalali($item[ $column_name ]);
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    function column_project_name($item) {
        $actions = array(
            'view'   => sprintf('<a href="?page=%s&action=view&project_id=%s">نمایش گانت</a>', $_REQUEST['page'], $item['id']),
            'edit'   => sprintf('<a href="?page=%s&action=edit&project_id=%s">ویرایش</a>', $_REQUEST['page'], $item['id']),
            'delete' => sprintf('<a href="?page=%s&action=delete&project_id=%s" onclick="return confirm(\'آیا از حذف این پروژه مطمئن هستید؟\')">حذف</a>', $_REQUEST['page'], $item['id']),
            'print'  => sprintf('<a href="?page=%s&action=print&project_id=%s" target="_blank">چاپ/PDF</a>', $_REQUEST['page'], $item['id']),
        );
        return sprintf('%1$s %2$s', $item['project_name'], $this->row_actions($actions));
    }

    function column_created_by($item) {
        $user = get_userdata($item['created_by']);
        return $user ? $user->display_name : 'کاربر حذف شده';
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="project[]" value="%s" />', $item['id']);
    }

    public function prepare_items() {
        global $wpdb;
        $table_projects = $wpdb->prefix . 'wpm_projects';
        $table_tasks = $wpdb->prefix . 'wpm_tasks';

        $query = "SELECT p.id, p.project_name, p.created_by, p.creation_date, COUNT(t.id) as task_count
                  FROM {$table_projects} p
                  LEFT JOIN {$table_tasks} t ON p.id = t.project_id
                  GROUP BY p.id";

        $this->items = $wpdb->get_results($query, ARRAY_A);

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
    }
}
?>
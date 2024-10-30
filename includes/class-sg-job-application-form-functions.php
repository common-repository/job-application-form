<?php


/*
* register js and css
*/

function sg_enqueue_admin_custom_script() {
    wp_enqueue_script( 'sg_custom_script', plugin_dir_url( __FILE__ ) . '../public/js/myscript.js', array('jquery'), '1.0' );
    wp_localize_script( 'sg_custom_script', 'sg_custom_script_object',
        array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        )
    );
    wp_enqueue_style('custom_css', plugin_dir_url( __FILE__ ) . '../public/css/custom-style.css');
}
add_action( 'admin_enqueue_scripts', 'sg_enqueue_admin_custom_script' );
add_action('wp_enqueue_scripts', 'sg_enqueue_admin_custom_script' );

add_action('admin_menu', 'add_job_pages');

/*
* adding page to menu
*/
function add_job_pages() {
     add_menu_page(
        __( 'Job Application', 'job-plugin' ),
        __( 'Job Application','job-plugin' ),
        'manage_options',
        'job-page',
        'job_page_callback',
        'dashicons-groups'
    );
     add_submenu_page( 'job-page', 'Add designation', 'Add designation', 'manage_options', 'job-desgn-page', 'job_desgn_page_callback');
}

function job_desgn_page_callback(){
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
    * Custom_Table_Example_List_Table class that will display our custom table
    * records in nice table
    */
class Custom_Table_Example_List_Table extends WP_List_Table
{
    /**
        * [REQUIRED] You must declare constructor and give some basic params
        */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'application',
            'plural' => 'applications',
        ));
    }

    /**
        * [REQUIRED] this is a default column renderer
        *
        * @param $item - row (key, value array)
        * @param $column_name - string (key)
        * @return HTML
        */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
        * [OPTIONAL] this is example, how to render specific column
        *
        * method name must be like this: "column_[column_name]"
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_age($item)
    {
        return '<em>' . $item['age'] . '</em>';
    }

    /**
        * [OPTIONAL] this is example, how to render column with actions,
        * when you hover row "Edit | Delete" links showed
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_name($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=persons_form&id=%s">%s</a>', $item['id'], __('Edit', 'custom_table_example')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }

    /**
        * [REQUIRED] this is how checkbox column renders
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['jdid']
        );
    }

    /**
        * [REQUIRED] This method return columns to display in table
        * you can skip columns that you do not want to show
        * like content, or description
        *
        * @return array
        */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'jdid' => __('Designation Id', 'job-plugin'),
            'jdname' => __('Name', 'job-plugin'),
        );
        return $columns;
    }

    /**
        * [OPTIONAL] This method return columns that may be used to sort table
        * all strings in array - is column names
        * notice that true on name column means that its default sort
        *
        * @return array
        */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'jid' => array('jid', true),
            'jdname' => array('fname', false),
        );
        return $sortable_columns;
    }

    /**
        * [OPTIONAL] Return array of bult actions if has any
        *
        * @return array
        */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
        * [OPTIONAL] This method processes bulk actions
        * it can be outside of class
        * it can not use wp_redirect coz there is output already
        * in this example we are processing delete action
        * message about successful deletion will be shown on page in next part
        */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_designation'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? sanitize_text_field($_REQUEST['id']) : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE jdid IN($ids)");
            }
        }
    }

    /**
        * [REQUIRED] This is the most important method
        *
        * It will get rows from database and prepare them to be showed in table
        */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_designation'; // do not forget about tables prefix
        
        $per_page = 20; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(jdid) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'jdid';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
        
        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
        
    }
}


    global $wpdb;

    $table = new Custom_Table_Example_List_Table();
    $table->prepare_items();
    // $table->display();

    ?>

    <div class="wrap">

        <h2><?php _e('Add Designation', 'job_plugin')?>
        <form class="job-desgination-form" id="formjob" method="POST">
            <?php echo wp_nonce_field( 'designation_file', 'designation_nonce', true, false ); ?>
            <input type="text" name="newdesgn" required="">
            <input type="submit" name="subnewdesgn" value="Add Designation">
        </form>
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2><?php _e('Designation List', 'job_plugin')?> 
        </h2>
        <?php echo esc_html($message); ?>

        <form id="job-desgination-table" method="GET">
            <input type="hidden" name="page" value="<?php echo esc_html($_REQUEST['page']) ?>"/>
            <?php $table->display() ?>
        </form>

    </div>

    <?php
}

if ( ! function_exists( 'handle_new_desgn' ) ) {

    /**
     * Handles the file upload request.
     */
    function handle_new_desgn() {
        // Stop immidiately if form is not submitted
        if ( ! isset( $_POST['subnewdesgn'] ) ) {
            return;
        }

        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['designation_nonce'], 'designation_file' ) ) {
            wp_die( esc_html__( 'Nonce mismatched', 'job-plugin' ) );
        }
 
        $data = array(
            'jdname'  => sanitize_text_field($_POST['newdesgn']),
            
        );
        global $wpdb;
        $table = $wpdb->prefix.'job_designation';
        // $data = array('column1' => 'data one', 'column2' => 123);
        // $format = array('%s','%d');
        $wpdb->insert($table,$data);


    }
}

add_action( 'init', 'handle_new_desgn' );

//shotcode
add_shortcode( 'job-shortcode', 'job_code_func' );
function job_code_func( $atts ) {
    $html='';

    $html .= '<form class="job-form" id="formcv" method="POST" enctype="multipart/form-data">';

        $html .= '<table class="form-field">';
        $html .= '<tbody>';
            $html .= '<tr><td><label>First Name</label></td><td><input type="text" id="fname" name="fname" placeholder="Enter First Name" required></td></tr>';
            $html .= '<tr><td><label>Last Name</label></td><td><input type="text" id="lname" name="lname" placeholder="Enter Last Name" required></td></tr>';
            $html .= '<tr><td><label>Email</label></td><td><input type="email" id="uemail" name="uemail" placeholder="Enter Email" required></td></tr>';
            $html .= '<tr><td><label>Phone</label></td><td><input type="number" id="phno" name="phno" placeholder="Enter Mobile Number" minlength=10 maxlength=10 required></td></tr>';
            global $wpdb;
            $table = $wpdb->prefix.'job_designation';
            $designations = $wpdb->get_results( 
                "SELECT * FROM $table"
            );
             
            if ( $designations ) {
                $html .= '<tr><td><label>Designation</label></td><td><select id="jtype" name="jtype">';
                $html .= '<option value="" disabled selected>Select your Designation</option>';
                foreach ( $designations as $designation ) {
                    $html .= '<option value="'.$designation->jdname.'">'.$designation->jdname.'</option>';
                }
                $html .= '</select></td></tr>';
            }
            $html .= '<tr><td><label>Resume</label></td><td><input type="file" id="cv_file" placeholder="Upload Resume" name="cv_file" required></td></tr>';
            $html .= '<input type="hidden" name="action" value="sg_my_action">';
            // Output the nonce field
            $html .= wp_nonce_field( 'upload_cv_file', 'cv_nonce', true, false );

            $html .= '<tr><td colspan=2><input type="submit" name="submit_cv_form" value="' . esc_html__( 'Submit Application', 'job-plugin' ) . '"></td></tr>';
        
            $html .= '</tbody>';
        $html .= '</table>';
    $html .= '</form>';
    $html .= '<div class="dispmsg-sg"></div>';

    return $html;
}

add_action( 'wp_ajax_sg_my_action', 'sg_my_action' );
add_action( 'wp_ajax_nopriv_sg_my_action', 'sg_my_action' );
function sg_my_action() {
    global $wpdb; // this is how you get access to the database

    $allowed_extensions = array( 'pdf', 'doc', 'docx' );
    $file_type = wp_check_filetype( $_FILES['cv_file']['name'] );
    $file_extension = $file_type['ext'];


    //Check for valid file extension
    if ( ! in_array( $file_extension, $allowed_extensions ) ) {
        
    }
    // These files need to be included as dependencies when on the front end.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

    
        $attachment_id = media_handle_upload( 'cv_file', 0 );

        
        $file = wp_get_attachment_url($attachment_id);
 
        $table_name = $wpdb->prefix . 'job_list';
        $fnm=sanitize_text_field($_POST['fname']);
        $lnm=sanitize_text_field($_POST['lname']);
        $em=sanitize_email($_POST['uemail']);
        $ph=sanitize_text_field($_POST['phno']);
        $jtype=sanitize_text_field($_POST['jtype']);
        $wpdb->insert(
            $table_name,
            array(
                'fname' => $fnm,
                'lname' => $lnm,
                'jemail' => $em,
                'phno' => $ph,
                'jtype'=>$jtype,
                'attchid' => $attachment_id
            )
        );
        echo "done";
    wp_die(); // this is required to terminate immediately and return a proper response
}

function job_page_callback() {
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
    * Custom_Table_Example_List_Table class that will display our custom table
    * records in nice table
    */
class Custom_Table_Example_List_Table extends WP_List_Table
{
    /**
        * [REQUIRED] You must declare constructor and give some basic params
        */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'application',
            'plural' => 'applications',
        ));
    }

    /**
        * [REQUIRED] this is a default column renderer
        *
        * @param $item - row (key, value array)
        * @param $column_name - string (key)
        * @return HTML
        */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
        * [OPTIONAL] this is example, how to render specific column
        *
        * method name must be like this: "column_[column_name]"
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_age($item)
    {
        return '<em>' . $item['age'] . '</em>';
    }

    /**
        * [OPTIONAL] this is example, how to render column with actions,
        * when you hover row "Edit | Delete" links showed
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_name($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=persons_form&id=%s">%s</a>', $item['id'], __('Edit', 'custom_table_example')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', sanitize_text_field($_REQUEST['page']), $item['id'], __('Delete', 'custom_table_example')),
            'attchid' => srintf('<a href="'.wp_get_attachment_url($item[attchid]).'">%s</a>', $item['jid'], __('Show CV', 'job-plugin')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }

    /**
        * [REQUIRED] this is how checkbox column renders
        *
        * @param $item - row (key, value array)
        * @return HTML
        */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['jid']
        );
    }
    function column_attchid($item)
    {
        return sprintf(
            '<a href='.wp_get_attachment_url($item['attchid']).' target="_blank">View attchment</a>'
        );
    }

    /**
        * [REQUIRED] This method return columns to display in table
        * you can skip columns that you do not want to show
        * like content, or description
        *
        * @return array
        */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'jid' => __('Application Id', 'job-plugin'),
            'fname' => __('First Name', 'job-plugin'),
            'lname' => __('Last Name', 'job-plugin'),
            'jemail' => __('Email', 'job-plugin'),
            'phno' => __('Mobile Number', 'job-plugin'),
            'jtype' => __('Designation', 'job-plugin'),
            'attchid' => __('Resume', 'job-plugin'),
        );
        return $columns;
    }

    /**
        * [OPTIONAL] This method return columns that may be used to sort table
        * all strings in array - is column names
        * notice that true on name column means that its default sort
        *
        * @return array
        */
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'jid' => array('jid', true),
            'fname' => array('fname', false),
        );
        return $sortable_columns;
    }

    /**
        * [OPTIONAL] Return array of bult actions if has any
        *
        * @return array
        */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
        * [OPTIONAL] This method processes bulk actions
        * it can be outside of class
        * it can not use wp_redirect coz there is output already
        * in this example we are processing delete action
        * message about successful deletion will be shown on page in next part
        */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_list'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? sanitize_text_field($_REQUEST['id']) : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE jid IN($ids)");
            }
        }
    }

    /**
        * [REQUIRED] This is the most important method
        *
        * It will get rows from database and prepare them to be showed in table
        */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_list'; // do not forget about tables prefix
        
        $per_page = 20; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(jid) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'jid';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
        
        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
        
    }
}


    global $wpdb;

    $table = new Custom_Table_Example_List_Table();
    $table->prepare_items();
    // $table->display();

    ?>

    <div class="wrap">

        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2><?php _e('Application List', 'job_plugin')?> 
        </h2>
        <?php echo esc_html($message); ?>

        <form id="job-table" method="GET">
            <input type="hidden" name="page" value="<?php echo esc_html($_REQUEST['page']) ?>"/>
            <?php $table->display() ?>
        </form>

    </div>

    <?php

}
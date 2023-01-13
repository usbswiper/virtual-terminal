<?php 

if (!class_exists('WP_List_Table')) {
     require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
 }

class Users_List_Table extends WP_List_Table {

	public $user_ids = array();

    public function __construct( $newargs = array() ) {
       
       $this->user_ids = get_option('get_exclude_partner_users');

        parent::__construct();
    }

    public function prepare_items() {

    
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        
        $totalItems = !empty( $this->user_ids ) ? count( $this->user_ids ) : 0;
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => 20
        ) );
        $this->_column_headers = array( $columns, $hidden, $sortable );
        
        $this->items = $this->get_post_data();
    }

    public function get_columns() {
        $columns = array(
            'id'            => __( 'ID', 'bulk-featured-image'),
            'user_login'         => __( 'User Login', 'bulk-featured-image'),
            'display_name' => __( 'Display Name', 'bulk-featured-image'),
            'user_email' => __( 'User Email', 'bulk-featured-image')
        );
        return $columns;
    }

    public function get_hidden_columns() {
        return array();
    }

    public function get_sortable_columns() {
        return array(
            'user_login' => array('user_login', false),
        );
    }
    
    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'id':
            case 'user_login':
            case 'display_name':
            case 'user_email':
                return $item[ $column_name ];
            default:
                return $column_name;
        }
    }

    public function get_post_data() {
       
       $results = array();

       $user_lists = $this->user_ids;

       if( !empty( $user_lists ) && is_array( $user_lists ) ) {

       		foreach( $user_lists as $key => $user_id ) {

				$user_info = get_userdata($user_id);
       			$results[] = array(
					'id'            => $user_id,
					'user_login'    => $user_info->user_login,
					'display_name'  => $user_info->display_name,
					'user_email'    => $user_info->user_email
       			);
       		}
       }

       return $results;
    }
}
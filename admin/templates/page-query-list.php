<?php

/**
 * Execute custom list page
 */
function qw_list_queries_form() {
	$ListTable = new Query_Wrangler_List_Table();
	$ListTable->prepare_items();

	// if noheader is set, then we're bulk operating
	if ( ! isset( $_REQUEST['noheader'] ) )
	{ ?>
		<div class="wrap">
			<h2>
				<?php _e( 'Query Wrangler' ); ?>
				<a class="add-new-h2" href="admin.php?page=qw-create"><?php _e( 'Add New' ); ?></a>
			</h2>
			<form id="queries-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
				<input type="hidden" name="noheader" value="true"/>
				<?php $ListTable->display() ?>
			</form>

		</div>
		<?php
	}
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Query_Wrangler_List_Table extends WP_List_Table {

	function __construct() {
		//Set parent defaults
		parent::__construct( array(
			'singular' => 'query',     //singular name of the listed records
			'plural'   => 'queries',    //plural name of the listed records
			'ajax'     => FALSE        //does this table support ajax?
		) );

	}

	/**
	 * List of columns in table
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'name'    => 'Name',
			'type'    => 'Type',
			'details' => 'Details'
		);

		return $columns;
	}

	/**
	 * Sortable columns
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array( 'name', TRUE ),     //true means its already sorted
			'type' => array( 'type', FALSE ),
		);

		return $sortable_columns;
	}

	/**
	 * Bulk actions
	 *
	 * @return array
	 */
	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete'
		);

		return $actions;
	}

	/**
	 * Do bulk action
	 */
	function process_bulk_action() {
		if ( 'delete' === $this->current_action() ) {
			if ( is_array( $_REQUEST['query'] ) ) {
				foreach ( $_REQUEST['query'] as $query_id ) {
					qw_delete_query( $query_id );
				}
			}

			wp_redirect( get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=query-wrangler' );
		}
	}

	/**
	 * Main name and operations column
	 *
	 * @param $item
	 * @return string
	 */
	function column_name( $item ) {
		//Build row actions
		$actions = array(
			'edit'   => sprintf( '<a href="?page=query-wrangler&edit=%s">Edit</a>', $item['ID'] ),
			'export' => sprintf( '<a href="?page=query-wrangler&export=%s">Export</a>', $item['ID'] ),
			'delete' => sprintf( '<a class="qw-delete-query" href="?page=query-wrangler&noheader=true&action=delete&edit=%s">Delete</a>', $item['ID'] ),
		);

		// pages
		if ( $item['type'] == 'page' ) {
			$actions['view'] = '<a href="' . get_bloginfo( 'wpurl' ) . '/' . $item['path'] . '">View</a>';
		}

		//Return the title contents
		return '<a href="?page=query-wrangler&edit=' . $item['ID'] . '">' . $item['name'] . '</a>' .
		       ' <span style="color:silver">(ID: '.$item['ID'].')</span>' .
		       $this->row_actions( $actions );
	}

	/**
	 * Type column
	 *
	 * @param $item
	 * @return string
	 */
	function column_type( $item ){
		return ucfirst( $item['type'] );
	}

	/**
	 * Details column
	 *
	 * @param $item
	 * @return string
	 */
	function column_details( $item ){
		$details = '';
		$settings = QW_Settings::get_instance();

		if ( $item['type'] != 'override' ) {
			if ( $settings->get('shortcode_compat') ){
				$details .= 'Shortcode options:<br />[qw_query id=' . $item['ID'] . ']<br />[qw_query slug="' . $item['slug'] . '"]';
			}
			else {
				$details .= 'Shortcode options:<br />[query id=' . $item['ID'] . ']<br />[query slug="' . $item['slug'] . '"]';
			}
		}

		if ( $item['type'] == 'override' ) {
			$details = 'Overriding: ';

			$row = qw_get_query_by_id( $item['ID'] );
			if ( isset( $row->data['override'] ) ) {
				$all_overrides = qw_all_overrides();
				foreach ( $row->data['override'] as $type => $values ) {

					if ( isset( $all_overrides[ $type ] ) ) {
						$override = $all_overrides[ $type ];

						$details .= '<br>'.$override['title'] . ': ';

						if ( is_array( $values['values'] ) ) {
							$details.= implode( ", ", $values['values'] );
						}
						else {
							$details.= ", ". $values['values'];
						}
					}
				}
			}

		}

		return $details;
	}

	/**
	 * Checkbox item
	 *
	 * @param array $item
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item['ID']
		);
	}


	/**
	 * Prepare table data and handle pagination
	 */
	function prepare_items() {
		global $wpdb;
		$per_page = 12;
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// get the table data
		$this->process_bulk_action();
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'name'; //If no sort, default to title
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc

		$sql  = "SELECT id as ID, type, name, slug, path
                FROM " . $wpdb->prefix . "query_wrangler
                ORDER BY %s %s";

		$data = $wpdb->get_results( $wpdb->prepare( $sql, $orderby, $order ) , ARRAY_A );

		// handle pagination
		$current_page = $this->get_pagenum();
		$total_items = count( $data );
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->items = $data;
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );
	}

}

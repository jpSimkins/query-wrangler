<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class Query_Wrangler_List_Table
 */
class Query_Wrangler_List_Table extends WP_List_Table {

	protected $admin_page;
	protected $url;

	/**
	 * Start the normal table stuff
	 *
	 * Query_Wrangler_List_Table constructor.
	 *
	 * @param $admin_page QW_Admin_Pages
	 */
	function __construct( $admin_page ) {
		$this->admin_page = $admin_page;
		$this->url = $admin_page->base_url;

		//Set parent defaults
		parent::__construct( array(
				'singular' => 'query',     //singular name of the listed records
				'plural'   => 'queries',    //plural name of the listed records
				'ajax'     => FALSE        //does this table support ajax?
		) );
	}

	/**
	 * Custom function for outputting this table as a page.
	 */
	function do_the_deal(){
		$this->prepare_items();

		// if noheader is set, then we're bulk operating
		if ( ! isset( $_REQUEST['noheader'] ) )
		{ ?>
			<div class="wrap">
				<h2>
					<?php print esc_html( get_admin_page_title() ); ?>
					<a class="add-new-h2" href="<?php echo $this->url; ?>.create"><?php _e( 'Add New' ); ?></a>
				</h2>

				<form id="search-queries-filter" method="get">
					<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>"/>
					<?php $this->search_box( 'Search', 'post' ); ?>
				</form>
				<form id="queries-filter" method="get">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
					<input type="hidden" name="noheader" value="true"/>
					<?php $this->display() ?>
				</form>

			</div>
			<?php
		}
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
					$this->admin_page->delete_query( $query_id );
				}
			}

			wp_redirect( $this->url );
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
			'edit'   => sprintf( '<a href="%s.edit&query_id=%s">%s</a>',
					$this->url,
					$item['ID'],
					__( 'Edit' )
			),
			'export' => sprintf( '<a href="%s.export&query_id=%s">%s</a>',
					$this->url,
					$item['ID'],
					__( 'Export' )
			),
			'delete' => sprintf( '<a href="%s.delete&query_id=%s&noheader=true" class="qw-delete-query" >%s</a>',
					$this->url,
					$item['ID'],
					__( 'Delete' )
			),
		);

		// pages
		if ( $item['type'] == 'page' ) {
			$actions['view'] = sprintf( '<a href="%s/%s">%s</a>',
									get_bloginfo( 'wpurl' ),
									$item['path'],
									__( 'View' )
								);
		}

		//Return the title contents
		return sprintf( '<a href="%s.edit&query_id=%s">%s</a>' .
		       ' <span style="color:silver">(ID: %s)</span>',
				$this->url,
				$item['ID'],
				$item['name'],
				$item['ID']
		       ) .
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
			$details.= __( 'Shortcode' ) . '<br>';
			if ( $settings->get('shortcode_compat') ){
				$details .= '<code>[qw_query slug="' . $item['slug'] . '"]</code>';
			}
			else {
				$details .= '<code>[query slug="' . $item['slug'] . '"]</code>';
			}
		}

		if ( $item['type'] == 'override' ) {
			$details = __( 'Overriding' );

			$row = qw_get_query_by_id( $item['ID'] );
			if ( isset( $row->data['override'] ) ) {
				$handlers = qw_all_handlers();

				foreach ( $row->data['override'] as $type => $values ) {

					if ( isset( $handlers['override']['all_items'][ $type ] ) ) {
						$override = $handlers['override']['all_items'][ $type ];

						$details .= '<br>'.$override['title'] . ': ';

						if ( !empty( $values['values'] ) ){
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

		$sql = "SELECT `id` as `ID`, `type`, `name`, `slug`, `path` FROM {$wpdb->prefix}query_wrangler";
		$args = array();

		if ( !empty( $_REQUEST['s'] ) ){
			$sql.= " WHERE `name` LIKE %s";
			$args[] = '%' . $wpdb->esc_like( trim( $_REQUEST['s'] ) ) . '%';
		}

		// whitelist orderby
		$orderby = 'name';
		if ( ! empty( $_REQUEST['orderby'] ) &&
		     in_array( $_REQUEST['orderby'], array('name', 'type') ) ){
			$orderby = $_REQUEST['orderby'];
		}

		// whitelist order
		$order = 'ASC';
		if ( ! empty( $_REQUEST['order'] ) && strtolower( $_REQUEST['order'] ) == 'desc' ){
			$order = 'DESC';
		}

		$sql.= " ORDER BY %s {$order}";
		$args[] = $orderby;

		$sql = $wpdb->prepare( $sql, $args );
		$data = $wpdb->get_results( $sql, ARRAY_A );

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

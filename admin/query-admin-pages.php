<?php
/**
 * Handle the display of pages and actions
 */
function qw_page_router() {
	$redirect = FALSE;

	if ( isset( $_GET['action'] ) ) {
		$redirect = TRUE;
		switch ( $_GET['action'] ) {
			case 'update':
				qw_update_query( $_POST );
				qw_admin_redirect( $_GET['edit'] );
				break;

			case 'delete':
				qw_delete_query( $_GET['edit'] );
				// redirect to the list page
				qw_admin_redirect();
				break;

			case 'create':
				$new_query_id = qw_insert_new_query( $_POST );
				qw_admin_redirect( $new_query_id );
				break;

			case 'import':
				$new_query_id = qw_query_import( $_POST );
				qw_admin_redirect( $new_query_id );
				break;

			case 'save_settings':
				qw_save_settings( $_POST );
				qw_admin_redirect( NULL, 'qw-settings' );
				break;
		}
	}

	// see if we're editing a query
	if ( isset( $_GET['edit'] ) &&
	     is_numeric( $_GET['edit'] ) &&
	     ! $redirect )
	{
		qw_edit_query_form();
	}
	// export a query
	else if ( isset( $_GET['export'] ) && is_numeric( $_GET['export'] ) ) {
		qw_export_page();
	}
	// else we need a list of queries
	else {
		include QW_PLUGIN_DIR . '/admin/templates/page-query-list.php';
		qw_list_queries_form();
	}
}

/**
 * Update - existing query
 *
 * @param $post - $_POST data
 */
function qw_update_query( $post ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "query_wrangler";

	// if you can't tell, i'm having a lot of trouble with slashes
	$post = array_map( 'stripslashes_deep', $post );

	$options = $post[ QW_FORM_PREFIX ];

	// look for obvious errors
	if ( empty( $options['args']['posts_per_page'] ) ) {
		$options['args']['posts_per_page'] = 5;
	}
	if ( empty( $options['args']['offset'] ) ) {
		$options['args']['offset'] = 0;
	}
	if ( empty( $options['args']['post_status'] ) ) {
		$options['args']['post_status'] = 'publish';
	}

	// handle page settings
	if ( isset( $options['display']['page']['template-file'] ) ) {
		// handle template name
		if ( $options['display']['page']['template-file'] == 'index.php' ) {
			$options['display']['page']['template-name'] = 'Default';
		} else {
			$page_templates = get_page_templates();
			foreach ( $page_templates as $name => $file ) {
				if ( $options['display']['page']['template-file'] == $file ) {
					$options['display']['page']['template-name'] = $name;
				}
			}
		}
	}

	// hook for presave
	$query_id = absint( $_GET['edit'] );
	$options  = apply_filters( 'qw_pre_save', $options, $query_id );
	$new_data = qw_serialize( $options );

	// update for pages
	if ( $options['display']['page']['path'] ) {
		$page_path = ( $options['display']['page']['path'] ) ? $options['display']['page']['path'] : '';

		// handle opening slash
		// checking against $wp_query->query['pagename'], so, no slash
		if ( substr( $page_path, 0, 1 ) == '/' ) {
			$page_path = ltrim( $page_path, '/' );
		}

		$sql = "UPDATE " . $table_name . " SET data = %s, path = %s WHERE id = %d LIMIT 1";
		$wpdb->query( $wpdb->prepare( $sql,
				$new_data,
				$page_path,
				$query_id ) );
	} // update for widgets
	else {
		$sql = "UPDATE " . $table_name . " SET data = %s WHERE id = %d LIMIT 1";
		$t   = $wpdb->prepare( $sql, $new_data, $query_id );
		$wpdb->query( $t );
	}
}

/**
 * Delete - query by id
 *
 * @param int $query_id
 */
function qw_delete_query( $query_id ) {
	global $wpdb;
	$table = $wpdb->prefix . "query_wrangler";
	$wpdb->delete( $table, array( 'id' => $query_id ) );

	do_action( 'qw_delete_query', $query_id );

	// @todo - move this somewhere that subscribes to the action
	$table = $wpdb->prefix . "query_override_terms";
	$wpdb->delete( $table, array( 'query_id' => $query_id ) );
}

/**
 * Settings - Page
 */
function qw_settings_page() {
	include_once QW_PLUGIN_DIR . '/admin/templates/form-settings.php';
}

/**
 * Settings - Save
 *
 * @param $post
 */
function qw_save_settings( $post ) {
	$new = $post['qw-settings'];

	$settings = QW_Settings::get_instance();
	$settings->set( 'widget_theme_compat', (int) !empty( $new['widget_theme_compat'] ) );
	$settings->set( 'live_preview',        (int) !empty( $new['live_preview'] ) );
	$settings->set( 'show_silent_meta',    (int) !empty( $new['show_silent_meta'] ) );
	$settings->set( 'shortcode_compat',    (int) !empty( $new['shortcode_compat'] ) );
	$settings->set( 'meta_value_field_handler', absint( $new['meta_value_field_handler'] ) );
	$settings->save();
}

/**
 * Create - Page
 */
function qw_create_query_page() {
	include_once QW_PLUGIN_DIR . '/admin/templates/form-create.php';
}

/**
 * Create - Insert new query
 *
 * @param $post - $_POST data
 * @return int New Query ID
 */
function qw_insert_new_query( $post ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "query_wrangler";
	$create = $post['qw-create'];

	$values = array(
			'name' => sanitize_text_field( $create['name'] ),
			'slug' => sanitize_title( $create['name'] ),
			'type' => sanitize_text_field( $create['type'] ),
			'path' => NULL,
			'data' => qw_serialize( qw_default_query_data() ),
	);

	$wpdb->insert( $table_name, $values );

	return $wpdb->insert_id;
}

/**
 * Export - Page
 */
function qw_export_page() {
	include_once QW_PLUGIN_DIR . '/admin/templates/form-export.php';
}

/**
 * Export - Get code
 *
 * @param $query_id - the query's id number
 * @return string
 */
function qw_query_export( $query_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "query_wrangler";
	$sql        = "SELECT id,name,slug,type,path,data FROM " . $table_name . " WHERE id = " . $query_id;

	$row = $wpdb->get_row( $sql, ARRAY_A );
	unset( $row['id'] );

	$row['data'] = qw_unserialize( $row['data'] );
	$export      = var_export( $row, 1 );

	return "\$query = " . $export . ";";
}

/**
 * Import - Page
 */
function qw_import_page() {
	include_once QW_PLUGIN_DIR . '/admin/templates/form-import.php';
}

/**
 * Import a query into the database
 *
 * @param $post
 * @return int
 */
function qw_query_import( $post ) {
	global $wpdb;
	$table = $wpdb->prefix . "query_wrangler";

	$import = $post['qw-import'];
	eval( stripslashes( $import['query'] ) );

	if ( $import['name'] ) {
		$query['name'] = $import['name'];
		$query['slug'] = qw_make_slug( $import['name'] );
	}
	$query['data'] = qw_serialize( $query['data'] );
	$wpdb->insert( $table, $query );

	return $wpdb->insert_id;
}

/**
 * Edit - Page
 */
function qw_edit_query_form(){
	include_once QW_PLUGIN_DIR . '/admin/templates/form-editor-wrapper.php';
}

/**
 * Edit - Arguments
 */
function qw_edit_query_form_args() {
	$settings = QW_Settings::get_instance();

	if ( $query_id = qw_admin_get_current_query_id() ) {
		$row = qw_get_query_by_id( $query_id );
	}
	if ( ! $row ) {
		return;
	}

	$options     = $row->data;
	$display     = isset( $options['display'] ) ? array_map( 'stripslashes_deep', $options['display'] ) : array();
	$image_sizes = get_intermediate_image_sizes();
	$file_styles = qw_all_file_styles();

	// preprocess existing handlers
	$handlers = qw_preprocess_handlers( $options );

	// go ahead and make existing items wrapper forms
	// filters
	foreach ( $handlers['filter']['items'] as $k => &$filter ) {
		$args                   = array(
			'filter' => $filter,
			'weight' => $filter['weight'],
		);
		$filter['wrapper_form'] = theme( 'query_filter', $args );
	}
	// sorts
	foreach ( $handlers['sort']['items'] as $k => &$sort ) {
		$args                 = array(
			'sort'   => $sort,
			'weight' => $sort['weight'],
		);
		$sort['wrapper_form'] = theme( 'query_sort', $args );
	}

	$tokens = array();
	// fields
	foreach ( $handlers['field']['items'] as $k => &$field ) {
		$tokens[ $field['name'] ] = '{{' . $field['name'] . '}}';
		$args                     = array(
			'image_sizes' => $image_sizes,
			'file_styles' => $file_styles,
			'field'       => $field,
			'weight'      => $field['weight'],
			'options'     => $options,
			'display'     => $display,
			'tokens'      => $tokens,
		);
		$field['wrapper_form']    = theme( 'query_field', $args );
	}

	// overrides
	foreach ( $handlers['override']['items'] as $k => &$override ) {
		$args                     = array(
			'override' => $override,
			'weight'   => $override['weight'],
		);
		$override['wrapper_form'] = theme( 'query_override', $args );
	}

	// shortcode compatibility
	$shortcode = '[query slug="' . $row->slug . '"]';

	if ( $settings->get('shortcode_compat') ){
		$shortcode = '[qw_query slug="' . $row->slug . '"]';
	}

	// start building edit page data
	$editor_args = array(
		// query data
		'query_id'            => $row->id,
		'query_slug'          => $row->slug,
		'query_name'          => $row->name,
		'query_type'          => $row->type,
		'shortcode'           => $shortcode,
		'options'             => $options,
		'args'                => $options['args'],
		'display'             => $display,
		'query_page_title'    => isset( $options['display']['title'] ) ? $options['display']['title'] : '',
		'basics'              => qw_all_basic_settings(),
		'filters'             => $handlers['filter']['items'],
		'fields'              => $handlers['field']['items'],
		'sorts'               => $handlers['sort']['items'],
		'overrides'           => $handlers['override']['items'],
		// all datas
		'post_statuses'       => qw_all_post_statuses(),
		'styles'              => qw_all_styles(),
		'row_styles'          => qw_all_row_styles(),
		'row_complete_styles' => qw_all_row_complete_styles(),
		'page_templates'      => get_page_templates(),
		'post_types'          => qw_all_post_types(),
		'pager_types'         => qw_all_pager_types(),
		'all_overrides'       => qw_all_overrides(),
		'all_filters'         => qw_all_filters(),
		'all_fields'          => qw_all_fields(),
		'all_sorts'           => qw_all_sort_options(),
		'image_sizes'         => $image_sizes,
		'file_styles'         => $file_styles,
	);

	// Page Queries
	if ( $row->type == 'page' ) {
		$editor_args['query_page_path'] = $row->path;
	}

	// overrides
	if ( $row->type == 'override' ) {
		$editor_args['query_override_type'] =  isset( $row->override_type ) ? $row->override_type : null;
	}

	// add view link for pages
	if ( $row->type == 'page' && isset( $row->path ) ) {
		$editor_args['page_link'] .= ' <a class="add-new-h2" target="_blank" href="' . get_bloginfo( 'wpurl' ) . '/' . $row->path . '">View</a>';
	}

	return $editor_args;
}

/**
 * Edit - JSON data for js
 *
 * @param null $query_id
 * @return mixed|string|void
 */
function qw_edit_query_json( $query_id = NULL ) {
	$data = array(
			'allFields'      => qw_all_fields(),
			'allStyles'      => qw_all_styles(),
			'allRowStyles'   => qw_all_row_styles(),
			'allPostTypes'   => qw_all_post_types(),
			'allPagerTypes'  => qw_all_pager_types(),
			'allImageSizes'  => get_intermediate_image_sizes(),
			'allFileStyles'  => qw_all_file_styles(),
			'allFilters'     => qw_all_filters(),
			'allSortOptions' => qw_all_sort_options(),
			'allOverrides'   => qw_all_overrides(),
	);

	// editing a query
	if ( $query_id && $row = qw_get_query_by_id( $query_id ) ) {
		$row->options  = $row->data;
		$data['query'] = $row;
	}

	return json_encode( $data );
}

/*******************************************************************************
 * Assets
 ******************************************************************************/

/**
 * CSS - General admin
 * @todo move to real enqueue instead of printing in head
 */
function qw_admin_css() {
	print '<link rel="stylesheet" type="text/css" href="' . QW_PLUGIN_URL . '/admin/css/query-wrangler.css" />';
}

/**
 * CSS - Editor
 * @todo move to real enqueue instead of printing in head
 */
function qw_edit_query_css() {
	print '<link rel="stylesheet" type="text/css" href="' . QW_PLUGIN_URL . '/admin/css/query-wrangler-views.css" />';
}

/**
 * JS - List page
 */
function qw_admin_list_js() {
	wp_enqueue_script( 'qw-admin-list-js',
			plugins_url( '/admin/js/query-wrangler-list.js', dirname( __FILE__ ) ),
			array(),
			QW_VERSION,
			TRUE );
}

/**
 * JS - Editor
 */
function qw_admin_js() {
	// jquery unserialize form
	wp_enqueue_script( 'qw-unserialize-form',
			plugins_url( '/admin/js/jquery.unserialize-form.js',
					dirname( __FILE__ ) ),
			array(),
			QW_VERSION,
			TRUE );
	wp_enqueue_script( 'jquery-ui-core', FALSE, array( 'jquery' ) );
	wp_enqueue_script( 'jquery-ui-accordion' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'qw-admin-js',
			plugins_url( '/admin/js/query-wrangler.js', dirname( __FILE__ ) ),
			array( 'jquery-ui-core' ),
			QW_VERSION,
			TRUE );

	wp_enqueue_script( 'qw-edit-theme-views',
			plugins_url( '/admin/js/query-wrangler-views.js', dirname( __FILE__ ) ),
			array('qw-admin-js'),
			QW_VERSION,
			TRUE );

	// jquery ui rom cdn
	// @todo - non-cdn version? anything available in wp core?
	global $wp_scripts;
	$ui       = $wp_scripts->query( 'jquery-ui-core' );
	$url      = "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
	wp_enqueue_style( 'jquery-ui-smoothness', $url, FALSE, NULL );
}

/*******************************************************************************
 * Helper Functions
 ******************************************************************************/

/**
 * Simple redirect helper
 *
 * @param null $query_id
 * @param string $page
 */
function qw_admin_redirect( $query_id = NULL, $page = 'query-wrangler' ) {
	$url = admin_url( "admin.php?page=$page" );

	if ( $query_id ) {
		$url .= "&edit=" . (int) $query_id;
	}
	wp_redirect( $url );
	exit();
}

/**
 * Slug creation
 * @deprecated in favor of sanitize_title()
 */
function qw_make_slug( $string ) {
	$search = array( "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "-", "+", "=", "{", "}", "[", "]", "\\", "|", ":", ";", "'", "<", ",", ">", ".", "?", "/", "~", "`");

	return str_replace( " ", "_", strtolower( str_replace( $search, "", strip_tags( $string ) ) ) );
}

/**
 * Custom for outputting text/html into a textarea
 */
function qw_textarea( $value ) {
	return stripcslashes( esc_textarea( str_replace( "\\", "", $value ) ) );
}

/**
 * Get the current query being edited
 *
 * @return int|false
 */
function qw_admin_get_current_query_id() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'query-wrangler' && isset( $_GET['edit'] ) ) {
		return $_GET['edit'];
	}

	return FALSE;
}


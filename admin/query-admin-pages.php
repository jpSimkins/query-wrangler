<?php
/**
 * Handle the display of pages and actions
 */
function qw_page_handler() {
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
 * Export - Page
 */
function qw_export_page() {
	include_once QW_PLUGIN_DIR . '/admin/templates/form-export.php';
}

/**
 * Import - Page
 */
function qw_import_page() {
	include_once QW_PLUGIN_DIR . '/admin/templates/form-import.php';
}

/**
 * Edit - Page
 */
function qw_edit_query_form(){
	include_once QW_PLUGIN_DIR . '/admin/templates/form-editor-wrapper.php';
}

/**
 * Editor - Arguments
 */
function qw_get_editor_args() {
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

<?php

/**
 * Ajax for form templates
 */
function qw_form_ajax() {
	if ( $_POST['form'] == 'preview' ){
		include_once QW_PLUGIN_DIR . '/admin/templates/preview.php';
		exit;
	}

	if ( isset( $_POST['hook_key'], $_POST['handler'], $_POST['name'] ) ){
		// buffer the whole process in case of php warnings/notices
		ob_start();
		$qw_handlers = QW_Handlers::get_instance();

		$handler_type = $_POST['handler'];
		$name         = $_POST['name'];
		$hook_key     = $_POST['hook_key'];
		$weight       = !empty( $_POST['next_weight'] ) ? $_POST['next_weight'] : 0;

		$handler = $qw_handlers->all_handlers[ $handler_type ];
		$template = 'handler-' . $handler_type;

		// prepare and item and preprocess it
		$item = $handler['all_items'][ $hook_key ];
		$item['name']   = $name;
		$item['weight'] = $weight;

		$items = $qw_handlers->preprocess_handler_items( $handler_type, array( $name => $item ) );
		$item  = $items[ $name ];

		print qw_admin_template( $template,  array( $handler_type => $item ) );

		wp_send_json( array(
			'template' => ob_get_clean(),
		) );
	}

	exit;
}

/*
 * Random data grabs
 */
function qw_data_ajax() {
	if ( isset( $_POST['data'] ) ) {
		switch ( $_POST['data'] ) {
			case 'all_hooks':
				$query_id = isset( $_POST['queryId'] ) ? $_POST['queryId'] : NULL;
				$data     = qw_edit_query_json( $query_id );

				wp_send_json( $data );
				break;
		}
	}
}

/*
 * Scan for all templates used by a single query
 */
function qw_template_scan( $options ) {
	global $wpdb;
	$query_id       = $options['meta']['id'];
	$slug           = $options['meta']['slug'];
	$all_styles     = qw_all_styles();
	$all_row_styles = qw_all_row_styles();
	$style          = $all_styles[ $options['display']['style'] ];
	$row_style      = $all_row_styles[ $options['display']['row_style'] ];
	//print_r($row_style);
	$output    = array();
	$templates = array();

	//$options['display']['types']['this_instance']

	// start building theme arguments
	$wrapper_args = array(
			'slug'      => $slug,
			'tw_action' => 'find_only',
	);
	// template with wrapper
	$templates['wrapper'] = theme( 'query_display_wrapper',
			$wrapper_args,
			TRUE );

	$style_settings = array();
	if ( isset( $options['display']['style_settings'][ $style['hook_key'] ] ) ) {
		$style_settings = $options['display']['style_settings'][ $style['hook_key'] ];
	}
	// setup row template arguments
	$template_args = array(
			'template'       => $style['template'],
			'slug'           => $slug,
			'style'          => $style['hook_key'],
			'style_settings' => $style_settings,
			'tw_action'      => 'find_only',
	);
	// template the query rows
	$templates['style'] = theme( 'query_display_rows', $template_args );

	if ( $row_style['hook_key'] == "posts" ) {

		$row_style_settings = array( 'size' => 'complete' );

		if ( isset( $options['display'][ $row_style['hook_key'] . '_settings' ] ) ) {
			$row_style_settings = $options['display'][ $row_style['hook_key'] . '_settings' ];
		}

		$template_args          = array(
				'template'  => 'query-' . $row_style_settings['size'],
				'slug'      => $slug,
				'style'     => $row_style_settings['size'],
				'tw_action' => 'find_only',
		);
		$templates['row_style'] = theme( 'query_display_rows', $template_args );
	}

	if ( $row_style['hook_key'] == "fields" ) {

		$template_args          = array(
				'template'  => 'query-field',
				'slug'      => $slug,
				'style'     => $options['display']['row_style'],
				'tw_action' => 'find_only',
		);
		$templates['row_style'] = theme( 'query_display_rows', $template_args );
	}

	foreach ( $templates as $k => $template ) {
		foreach ( $template['suggestions'] as $suggestion ) {
			if ( isset( $template['found_suggestion'] ) && $suggestion == $template['found_suggestion'] ) {
				$output[ $k ][] = '<strong>' . $suggestion . '</strong>';
			} else {
				$output[ $k ][] = $suggestion;
			}
		}

		// see if this is the default template
		if ( isset( $template['found_path'] ) ) {
			if ( stripos( $template['found_path'], QW_PLUGIN_DIR ) !== FALSE ) {
				$output[ $k ]['found'] = '<em>(default) ' . $template['found_path'] . '</em>';
			} else {
				$output[ $k ]['found'] = '<strong>' . $template['found_path'] . '</strong>';
			}
		}
		//$output[$k]['template'] = $template;
	}

	return $output;
}

/**
 * @return array|mixed|void
 */
function qw_get_edit_preview_data(){
	$decode  = urldecode( $_POST['options'] );
	$options = array();
	parse_str( $decode, $options );

	$query_id = absint( $_POST['query_id'] );

	$options = $options['qw-query-options'];
	$options['args']['paged'] = 1;


	ob_start();

		global $wp_query;
		$temp     = $wp_query;
		$wp_query = NULL;

		ob_start();
			// get the query options, force override
			$options = qw_generate_query_options( $query_id, $options, TRUE );
			$options = apply_filters( 'qw_pre_preview', $options );

			// get formatted query arguments
			$args = qw_generate_query_args( $options );
			// set the new query
			$wp_query = new WP_Query( $args );

			// get the themed content
			print qw_template_query( $wp_query, $options );
		$preview = ob_get_clean();

		$templates = "These template files will be searched for relative to your theme folder.<br />
		              To override a query's template, copy the corresponding template from the <span style='font-family: monospace;'>query-wrangler/templates</span> folder to your theme folder (or THEME/templates) and rename it.
		              <pre>" . print_r( qw_template_scan( $options ), 1 ) . "</pre>";

		// php wp_query
		$php_wpquery = '<pre>$query = ' . var_export( $args, 1 ) . ';</pre>';

		// args
		$args = "<pre>" . print_r( $args, TRUE ) . "</pre>";

		// display
		$display = "<pre>" . htmlentities( print_r( $options['display'],TRUE ) ) . "</pre>";

		$new_query = "<pre>" . htmlentities( print_r( $wp_query, TRUE ) ) . "</pre>";
		$all_options = "<pre>" . htmlentities( print_r( $options, TRUE ) ) . "</pre>";

		// return
		$data = array(
				'preview'     => $preview,
				'php_wpquery' => $php_wpquery,
				'args'        => $args,
				'display'     => $display,
				'options'     => $all_options,
				'wpquery'     => $new_query,
				'templates'   => $templates,
		);

		$data = apply_filters( 'qw_post_preview', $data );

	$data['error_output'] = ob_get_clean();

	return $data;
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

/**
 * Ajax callback for meta_key autocomplete
 */
function qw_meta_key_autocomplete() {
	if ( isset( $_POST['qw_meta_key_autocomplete'] ) ) {
		$meta_key = sanitize_text_field( $_POST['qw_meta_key_autocomplete'] );

		global $wpdb;
		$like = $wpdb->esc_like( $meta_key );
		$query = $wpdb->prepare(
				"SELECT DISTINCT(`meta_key`) FROM {$wpdb->postmeta} WHERE `meta_key` LIKE '%s' LIMIT 15",
				'%' . $like. '%'
		);
		$results = $wpdb->get_col( $query );

		wp_send_json( array(
				'success' => TRUE,
				'values'  => $results,
		) );
	}
	exit;
}



/*
 * Generate form prefixes for handlers
 *
 * @param string
 *    $type = sort, field, filter, override
 */
function qw_make_form_prefix( $type, $name ) {
	$handlers = qw_all_handlers();

	if ( isset( $handlers[ $type ]['form_prefix'] ) ) {
		$output = QW_FORM_PREFIX . $handlers[ $type ]['form_prefix'] . '[' . $name . ']';
	} else {
		$output = QW_FORM_PREFIX . "[" . $name . "]";
	}

	return $output;
}

<?php

add_action( 'wp_ajax_qw_form_ajax', 'qw_form_ajax' );
add_action( 'wp_ajax_qw_data_ajax', 'qw_data_ajax' );
add_action( 'wp_ajax_qw_meta_key_autocomplete', 'qw_meta_key_autocomplete' );

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
		$qw_handlers = QW_Handler_Manager::get_instance();

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

/**
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

/**
 * Edit - JSON data for js
 *
 * @param null $query_id
 * @return mixed|string|void
 */
function qw_edit_query_json( $query_id = NULL )
{
	$qw_query = qw_get_query( $query_id );

	if ( !$qw_query ){
		return json_encode( array() );
	}

	$handlers = qw_all_handlers();

	$data = array(
		'query'          => $qw_query->row,
		'allFields'      => $handlers['field']['all_items'],
		'allFilters'     => $handlers['filter']['all_items'],
		'allOverrides'   => $handlers['override']['all_items'],
		'allSortOptions' => $handlers['sort']['all_items'],
	);

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

/**
 * Get preview developer data into an array of HTML outputs
 *
 * @return array
 */
function qw_get_edit_preview_data(){
	$decode  = urldecode( $_POST['options'] );
	$options = array();
	parse_str( $decode, $options );

	$query_id = absint( $_POST['query_id'] );

	$options = $options['qw-query-options'];
	$options['args']['paged'] = 1;


	ob_start();

		$qw_query = qw_get_query( $query_id );
		$qw_query->override_options( $options, true );
		$qw_query->is_preview = true;
		$qw_query->execute();
		$preview = $qw_query->output;

		$templates = "These template files will be searched for relative to your theme folder.<br />
		              To override a query's template, copy the corresponding template from the <span style='font-family: monospace;'>query-wrangler/templates</span> folder to your theme folder (or THEME/templates) and rename it.
		              <pre>" . print_r( qw_template_scan( $qw_query->options ), 1 ) . "</pre>";

		$php_wpquery = '<pre>$query = new WP_Query(' . var_export( $qw_query->args, 1 ) . ');</pre>';

		$new_query = "<pre>" . htmlentities( print_r( $qw_query->wp_query, TRUE ) ) . "</pre>";

		$qw_query_debug = "<pre>".  htmlentities( print_r( $qw_query, 1 ) )."</pre>";
		$all_options = "<pre>" . htmlentities( print_r( $qw_query->options, TRUE ) ) . "</pre>";

		// return
		$data = array(
			'preview'     => $preview,
			'php_wpquery' => $php_wpquery,
			'options'     => $all_options,
			'wpquery'     => $new_query,
			'qw_query_debug' => $qw_query_debug,
			'templates'   => $templates,
		);

		$data = apply_filters( 'qw_post_preview', $data );

	$data['error_output'] = ob_get_clean();

	return $data;
}


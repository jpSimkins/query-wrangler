<?php

add_action( 'tw_templates', 'qw_admin_templates' );

function qw_admin_templates( $templates ) {

	$settings = QW_Settings::get_instance();
	$theme = $settings->get( 'edit_theme', QW_DEFAULT_THEME );

	// editor theme template
	$templates['query_editor'] = array(
		'files'        => 'admin/editors/[theme]/[theme]-editor.php',
		'default_path' => QW_PLUGIN_DIR,
		'arguments'    => array(
			'theme' => $theme,
		),
	);

	// handlers.php will add handler wrapper templates

	return $templates;
}

/*
 * Create the new Query
 *
 * @param $_POST data
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

/*
 * Update existing query
 *
 * @param $_POST data
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

/*
 * Delete an existing query
 *
 * @param query id
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


/*
 * Export a query into code
 * @param
 *   $query_id - the query's id number
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

/*
 * Slug creation
 */
function qw_make_slug( $string ) {
	$search = array( "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "-", "+", "=", "{", "}", "[", "]", "\\", "|", ":", ";", "'", "<", ",", ">", ".", "?", "/", "~", "`");

	return str_replace( " ", "_", strtolower( str_replace( $search, "", strip_tags( $string ) ) ) );
}

/*
 * Custom for outputting text/html into a textarea
 */
function qw_textarea( $value ) {
	return stripcslashes( esc_textarea( str_replace( "\\", "", $value ) ) );
}

/*
 * Run init_callback for all Edit Themes
 */
function qw_init_edit_theme() {
	$themes  = qw_all_edit_themes();
	$current = QW_Settings::get_instance()->get( 'edit_theme' );

	if ( isset( $themes[ $current ] ) ) {
		$theme = $themes[ $current ];
	} else {
		$theme = $themes[ QW_DEFAULT_THEME ];
	}

	if ( function_exists( $theme['init_callback'] ) ) {
		$theme['init_callback']();
	}
}

// CSS
function qw_admin_css() {
	print '<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/smoothness/jquery-ui.css">
  <link rel="stylesheet" type="text/css" href="' . QW_PLUGIN_URL . '/admin/css/query-wrangler.css" />';
}

// admin list page
function qw_admin_list_js() {
	wp_enqueue_script( 'qw-admin-list-js',
		plugins_url( '/admin/js/query-wrangler-list.js', dirname( __FILE__ ) ),
		array(),
		QW_VERSION,
		TRUE );
}

// Base JS
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
}

// get the current query being edited
function qw_admin_get_current_query_id() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'query-wrangler' && isset( $_GET['edit'] ) ) {
		return $_GET['edit'];
	}

	return FALSE;
}

// Base Json data for query edit page
function qw_edit_json( $query_id = NULL ) {
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


/*
 * Checking current version of plugin to handle upgrades
 */
function qw_check_version() {
	if ( $last_version = get_option( 'qw_plugin_version' ) ) {
		// compare versions
		if ( $last_version < QW_VERSION ) {
			// include upgrade inc
			include_once QW_PLUGIN_DIR . '/upgrade.php';
			$upgrade_function   = 'qw_upgrade_' . qw_make_slug( $last_version ) . '_to_' . qw_make_slug( QW_VERSION );
			$upgrade_to_current = 'qw_upgrade_' . qw_make_slug( $last_version ) . '_to_current';

			if ( function_exists( $upgrade_function ) ) {
				$upgrade_function();
			} else if ( function_exists( $upgrade_to_current ) ) {
				$upgrade_to_current();
			}
			update_option( 'qw_plugin_version', QW_VERSION );
		}
	} else {
		// first upgrade
		include QW_PLUGIN_DIR . '/upgrade.php';
		qw_upgrade_12_to_13();
		// set our version number
		update_option( 'qw_plugin_version', QW_VERSION );
	}
}

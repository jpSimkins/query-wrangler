<?php

add_filter( 'qw_generate_query_args', 'qw_generate_filter_callback_args', 0, 2 );
add_filter( 'qw_generate_query_args', 'qw_generate_pager_query_args', 20, 2 );
add_filter( 'qw_generate_query_args', 'qw_generate_exposed_filter_callback_args', 30, 2 );

/**
 * Primary function for building and displaying a query
 *
 * @param int $query_id Id for the query
 * @param array $options_override an array for changing or adding query data options
 * @param bool $reset_post_data Reset the $wp_query after execution
 *
 * @return string Can return a string of html based on parameter $return
 */
function qw_execute_query( $query_id, $options_override = array(), $reset_post_data = TRUE )
{
	$qw_query = qw_get_query( $query_id );
	$qw_query
		->override_options( $options_override, false )
		->execute( $reset_post_data );

	return $qw_query->output;
}

/**
 * Get an existing query as QW_Query object
 *
 * @param $id
 *
 * @return null|QW_Query
 */
function qw_get_query( $id ) {
	if ( ! empty( $id ) ) {
		$query = new QW_Query( $id );

		if ( $query && is_a( $query, 'QW_Query' ) && ! $query->is_new ) {
			return $query;
		}
	}

	return NULL;
}

/**
 * Create a new empty QW_Query
 *
 * @return QW_Query object
 */
function qw_create_query() {
	return new QW_Query();
}


/**
 * Default values for  new query
 *
 * @return array Default query settings
 */
function qw_default_query_data() {
	return
		array (
			'display' => array (
				'title' => '',
				'style' => 'unformatted',
				'row_style' => 'posts',
				'post_settings' => array (
					'size' => 'complete',
				),
				'field_settings' => array (
					'group_by_field' => '__none__',
				),
				'template_part_settings' => array (
					'path' => '',
					'name' => '',
				),
				'header' => '',
				'footer' => '',
				'empty' => '',
				'wrapper-classes' => '',
				'page' => array (
					'pager' => array (
						'active' => '0',
						'type' => 'default',
					),
				),
			),
			'args' => array (
				'sorts' => array (
					'date' => array (
						'weight' => '0',
						'type' => 'date',
						'hook_key' => 'post_date',
						'name' => 'date',
						'order_value' => 'DESC',
					),
				),
				'filters' => array (
					'posts_per_page' => array (
						'weight' => '0',
						'type' => 'posts_per_page',
						'hook_key' => 'posts_per_page',
						'name' => 'posts_per_page',
						'posts_per_page' => '5',
					),
					'post_status' => array (
						'weight' => '1',
						'type' => 'post_status',
						'hook_key' => 'post_status',
						'name' => 'post_status',
						'post_status' => array( 'publish' => 'publish' ),
					),
					'post_types' => array (
						'weight' => '2',
						'type' => 'post_types',
						'hook_key' => 'post_types',
						'name' => 'post_types',
						'post_types' => array (
							'post' => 'post',
						),
					),
					'ignore_sticky_posts' => array (
						'weight' => '3',
						'type' => 'ignore_sticky_posts',
						'hook_key' => 'ignore_sticky_posts',
						'name' => 'ignore_sticky_posts',
						'ignore_sticky_posts' => 'on',
					),
				),
			),
		);
}

/**
 * @param $args
 * @param $options
 *
 * @return mixed
 */
function qw_generate_pager_query_args( $args, $options ){
	$paged = NULL;

	// if pager_key is enabled, trick qw_get_page_number
	if ( isset( $options['display']['page']['pager']['use_pager_key'] ) &&
	     isset( $options['display']['page']['pager']['pager_key'] ) &&
	     isset( $_GET[ $options['display']['page']['pager']['pager_key'] ] ) &&
	     is_numeric( $_GET[ $options['display']['page']['pager']['pager_key'] ] )
	) {
		$paged = $_GET[ $options['display']['page']['pager']['pager_key'] ];
	}

	// standard arguments
	$args['paged'] = ( $paged ) ? $paged : qw_get_page_number();

	// having any offset will break pagination
	if ( $args['paged'] > 1 ){
		unset( $args['offset'] );
	}

	return $args;
}

/**
 * Filters require a callback for setting their values in the $args array.
 * This processes those callbacks.
 *
 * @param $args
 * @param $options
 *
 * @return mixed
 */
function qw_generate_filter_callback_args( $args, $options ){

	$handlers = qw_get_query_handlers( $options );

	foreach ( $handlers as $handler_type => $handler ) {
		if ( is_array( $handler['items'] ) ) {
			foreach ( $handler['items'] as $name => $item ) {
				// Alter the query args
				// look for callback, and run it
				if ( isset( $item['query_args_callback'] ) && is_callable( $item['query_args_callback'] ) ) {
					call_user_func_array( $item['query_args_callback'], array( &$args, $item ) );
				}
			}
		}
	}

	return $args;
}

/**
 * @param $args
 * @param $options
 *
 * @return mixed
 */
function qw_generate_exposed_filter_callback_args( $args, $options ){

	$handlers = qw_get_query_handlers( $options );
	$submitted_data = qw_exposed_submitted_data();

	foreach ( $handlers as $handler_type => $handler ) {
		if ( is_array( $handler['items'] ) ) {
			foreach ( $handler['items'] as $name => $item ) {

				// Only work items that are exposed
				if ( !empty( $item['values']['is_exposed'] ) ) {

					if ( ! empty( $item['values']['exposed_key'] ) ) {
						// override exposed key
						$item['exposed_key'] = $item['values']['exposed_key'];
					}
					else {
						// default exposed key
						$item['exposed_key'] = 'exposed_' . $item['values']['name'];
					}

					// Process submitted exposed values
					if ( isset( $submitted_data[ $item['exposed_key'] ] ) && is_callable( $item['exposed_process'] ) ) {
						$value = $submitted_data[ $item['exposed_key'] ];
						call_user_func( $item['exposed_process'], $args, $item, $value );
					}
				}
			}
		}
	}

	return $args;
}

/**
 * Helper function: Get the current page number
 *
 * @param object $qw_query - the query being displayed
 *
 * @return int - the currentpage number
 */
function qw_get_page_number( $qw_query = NULL ) {
	// help figure out the current page
	$path_array = explode( '/page/', $_SERVER['REQUEST_URI'] );

	// look for WP paging first
	if ( ! is_null( $qw_query ) && isset( $qw_query->query_vars['paged'] ) ) {
		$page = $qw_query->query_vars['paged'];
	} // try wordpress method
	else if ( ! is_null( $qw_query ) && get_query_var( 'paged' ) ) {
		$page = get_query_var( 'paged' );
	} // paging with slashes
	else if ( isset( $path_array[1] ) ) {
		$page = explode( '/', $path_array[1] );
		$page = $page[0];
	} // paging with get variable
	else if ( isset( $_GET['page'] ) ) {
		$page = $_GET['page'];
	} // paging with a different get variable
	else if ( isset( $_GET['paged'] ) ) {
		$page = $_GET['paged'];
	} else {
		$page = 1;
	}

	return $page;
}

/**
 * Trim each item in an array w/ array_walk
 *   eg: array_walk($fruit, 'qw_trim');
 *
 * @param mixed
 */
function qw_trim( &$value ) {
	$value = trim( $value );
}

/**
 * Serialize wrapper functions for future changes.
 *
 * @param $array
 *
 * @return string
 */
function qw_serialize( $array ) {
	return serialize( $array );
}

/**
 * Custom: Fix unserialize problem with quotation marks
 *
 * @param $serial_str
 *
 * @return array
 */
function qw_unserialize( $serial_str ) {
	$data = maybe_unserialize( $serial_str );

	// if the string failed to unserialize, we may have a quotation problem
	if ( !is_array( $data ) ) {
		$serial_str = @preg_replace( '!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str );
		$data = maybe_unserialize( $serial_str );
	}

	if ( is_array( $data ) ) {
		// stripslashes twice for science
		$data = array_map( 'stripslashes_deep', $data );
		$data = array_map( 'stripslashes_deep', $data );

		return $data;
	}

	// if we're here the data wasn't unserialized properly.
	// return a modified version of the default query to prevent major failures.
	$default = qw_default_query_data();
	$default['display']['title'] = 'error unserializing query data';
	$default['args']['filters']['posts_per_page']['posts_per_page'] = 1;

	return $default;
}

/**
 * Support function for legacy, pre hook_keys discovery
 *
 * @param $all
 * @param $single
 *
 * @return int|string
 */
function qw_get_hook_key( $all, $single ) {
	// default to new custom_field (meta_value_new)
	$hook_key = '';

	// see if hook key is set
	if ( ! empty( $single['hook_key'] ) && isset( $all[ $single['hook_key'] ] ) ) {
		$hook_key = $single['hook_key'];
	} // look for type as key
	else if ( ! empty( $single['type'] ) ) {
		foreach ( $all as $key => $item ) {
			if ( $single['type'] == $item['type'] ) {
				$hook_key = $item['hook_key'];
				break;
			} else if ( $single['type'] == $key ) {
				$hook_key = $key;
				break;
			}
		}
	}

	return $hook_key;
}

/**
 * Replace contextual tokens within a string
 *
 * @param string $args - a query argument string
 *
 * @return string - query argument string with tokens replaced with values
 */
function qw_contextual_tokens_replace( $args ) {
	$matches = array();
	preg_match_all( '/{{([^}]*)}}/', $args, $matches );

	if ( isset( $matches[1] ) )
	{
		global $post;

		foreach ( $matches[1] as $i => $context_token )
		{
			if ( stripos( $context_token, ':' ) !== FALSE )
			{
				$a = explode( ':', $context_token );
				if ( $a[0] == 'post' && isset( $post->{$a[1]} ) )
				{
					$args = str_replace( $matches[0][ $i ], $post->{$a[1]}, $args );
				}
				else if ( $a[0] == 'query_var' && $replace = get_query_var( $a[1] ) ) {
					$args = str_replace( $matches[0][ $i ], $replace, $args );
				}
			}
		}
	}

	return $args;
}

/**
 * usort callback - sort by 'weight' key in array
 *
 * @param $a
 * @param $b
 *
 * @return int
 */
function qw_cmp( $a, $b ) {
	if ( $a['weight'] == $b['weight'] ) {
		return 0;
	}

	return ( $a['weight'] < $b['weight'] ) ? - 1 : 1;
}

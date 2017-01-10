<?php

add_filter( 'qw_generate_query_args', 'qw_generate_filter_callback_args', 0, 2 );

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

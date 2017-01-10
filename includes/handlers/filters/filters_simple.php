<?php

add_filter( 'qw_filters', 'qw_simple_filters' );

function qw_simple_filters( $filters ){

	/*
	 * Ignore sticky posts
	 */
	$filters['ignore_sticky_posts'] = array(
		'title'         => __( 'Ignore Sticky Posts' ),
		'description'   => __( 'Do not enforce stickiness in the resulting query.' ),
		'query_args_callback' => 'qw_simple_filter_args_callback',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'required' => true,
		'form_fields' => array(
			'ignore_sticky_post' => array(
				'type' => 'checkbox',
				'name' => 'ignore_sticky_posts',
				'default_value' => 0,
				'class' => array( 'qw-js-title' ),
			)
		),
	);

	/*
	 * Meta key
	 */
	$filters['meta_key'] = array(
		'title'               => __( 'Meta Key' ),
		'description'         => __( 'Filter for a specific meta_key.' ),
		'query_args_callback' => 'qw_simple_filter_args_callback',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'form_fields' => array(
			'meta_key' => array(
				'type' => 'text',
				'name' => 'meta_key',
				'description' => __( 'The meta_key for filtering results.' ),
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	/*
	 * Meta key/value compare
	 */
	$filters['meta_key_value'] = array(
		'title'               => __( 'Meta Key/Value Compare' ),
		'description'         => __( 'Filter for a specific meta_key / meta_value pair.' ),
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'query_args_callback' => 'qw_dynamic_filter_args_callback',
		'query_args_process' => array(
			'meta_key' => array(
				'values_key' => 'meta_key',
			),
			'meta_value' => array(
				'values_key' => 'meta_value',
				'process_callbacks' => array( 'stripslashes' ),
			),
			'meta_compare' => array(
				'values_key' => 'meta_compare',
			),
		),
		'form_fields' => array(
			'meta_key' => array(
				'type' => 'text',
				'name' => 'meta_key',
				'title' => __( 'Meta Key' ),
				'class' => array( 'qw-js-title' ),
			),
			'meta_compare' => array(
				'type' => 'select',
				'name' => 'meta_compare',
				'title' => __( 'Meta Compare' ),
				'description' => __( 'Determine how the query is filtered by the key value pairs.' ),
				'options' => array(
					'='  => __( 'Is equal to' ),
					'!=' => __( 'Is not equal to' ),
					'<'  => __( 'Is less than' ),
					'<=' => __( 'Is less than or equal to' ),
					'>'  => __( 'Is greater than' ),
					'>=' => __( 'Is greater than or equal to' ),
				),
				'class' => array( 'qw-js-title' ),
			),
			'meta_value' => array(
				'type' => 'text',
				'name' => 'meta_value',
				'title' => __( 'Meta Value' ),
				'class' => array( 'qw-js-title' ),
			),
		)
	);

	/*
	 * Meta value
	 */
	$filters['meta_value'] = array(
		'title'               => __( 'Meta Value' ),
		'description'         => __( 'Filter for a specific meta_value.' ),
		'query_args_callback' => 'qw_dynamic_filter_args_callback',
		'query_args_process' => array(
			'meta_value' => array(
				'values_key' => 'meta_value',
				'process_callbacks' => array( 'stripslashes' ),
			),
		),
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'form_fields' => array(
			'meta_value' => array(
				'type' => 'textarea',
				'name' => 'meta_value',
				'class' => array( 'qw-js-title' ),
			),
		),
	);

	/*
	 * Offset
	 */
	$filters['offset'] = array(
		'title'         => __( 'Offset' ),
		'description'   => __( 'Number of post to skip, or pass over. For example, if this field is 3, the first 3 items will be skipped and not displayed.' ),
		'query_args_callback' => 'qw_simple_filter_args_callback',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'form_fields' => array(
			'offset' => array(
				'type' => 'number',
				'name' => 'offset',
				'default_value' => 0,
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	/*
	 * Post status
	 */
	$filters['post_status'] = array(
		'title'         => __( 'Posts Status' ),
		'description'   => __( 'Select the post status of the items displayed.' ),
		'query_args_callback' => 'qw_simple_filter_args_callback',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'required' => true,
		'form_fields' => array(
			'post_status' => array(
				'type' => 'checkboxes',
				'name' => 'post_status',
				'default_value' => array(),
				'options' => qw_all_post_statuses(),
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	/*
	 * Posts per page
	 */
	$filters['posts_per_page'] = array(
		'title'         => __( 'Posts Per Page' ),
		'description'   => __( 'Number of posts to show per page. Use -1 to display all results.' ),
		'required'      => true,
		'query_args_callback' => 'qw_simple_filter_args_callback',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'form_fields' => array(
			'posts_per_page' => array(
				'type' => 'text',
				'name' => 'posts_per_page',
				'default_value' => 0,
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $filters;
}

/**
 * Most simple filters can use the same callback for query argument generation.
 * This looks for a value in the filter who's key in the values array
 * is the filter's hook_key.
 *
 * @param $args
 * @param $filter
 */
function qw_simple_filter_args_callback( &$args, $filter ){
	$key = $filter['hook_key'];

	if ( isset( $filter['values'][ $key ] ) ){
		$value = $filter['values'][ $key ];

		if ( is_string( $value ) ){
			$value = qw_contextual_tokens_replace( $value );
		}
		else if ( is_array( $value ) ) {
			foreach( $value as $item_key => $item_value ){
				$value[ $item_key ] = qw_contextual_tokens_replace( $item_value );
			}
		}

		$args[ $key ] = $value;
	}
}

/**
 * A filter query args callback that allows the filter item itself define some
 * dynamic aspects of how the filter values are transposed into args values.
 *
 * @param $args
 * @param $filter
 */
function qw_dynamic_filter_args_callback( &$args, $filter ){
	if ( !empty( $filter['query_args_process'] ) ){
		foreach( $filter['query_args_process'] as $args_key => $process ) {
			if ( isset( $filter['values'][ $process['values_key'] ] ) ){
				$value = $filter['values'][ $process['values_key'] ];

				if ( !empty( $process['process_callbacks'] ) ){
					foreach( $process['process_callbacks'] as $callback ){
						if ( is_callable( $callback ) ){
							$value = call_user_func( $callback, $value );
						}
					}
				}

				$args[ $args_key ] = $value;
			}
		}
	}
}

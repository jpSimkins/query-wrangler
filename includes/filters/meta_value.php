<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_meta_value' );

/**
 * @param $filters
 * @return mixed
 */
function qw_filter_meta_value( $filters ) {
	$filters['meta_value'] = array(
		'title'               => __( 'Meta Value' ),
		'description'         => __( 'Filter for a specific meta_value.' ),
		'query_args_callback' => 'qw_generate_query_args_meta_value',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'form_fields' => array(
			'meta_value' => array(
				'type' => 'textarea',
				'name' => 'meta_value',
				'class' => array( 'qw-js-title' ),
			),
		),
	);

	return $filters;
}

/**
 * @param $args
 * @param $filter
 */
function qw_generate_query_args_meta_value( &$args, $filter ) {
	if ( isset( $filter['values']['meta_value'] ) ) {
		$args['meta_value'] = stripslashes( $filter['values']['meta_value'] );
	}
}
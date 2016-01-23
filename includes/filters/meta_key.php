<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_meta_key' );

function qw_filter_meta_key( $filters ) {
	$filters['meta_key'] = array(
		'title'               => __( 'Meta Key' ),
		'description'         => __( 'Filter for a specific meta_key.' ),
		'query_args_callback' => 'qw_generate_query_args_meta_key',
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

	return $filters;
}

function qw_generate_query_args_meta_key( &$args, $filter ) {
	$args['meta_key'] = $filter['values']['meta_key'];
}
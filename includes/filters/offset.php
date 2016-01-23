<?php
// hook into qw_basics
add_filter( 'qw_filters', 'qw_basic_settings_offset' );

/*
 * Basic Settings
 */
function qw_basic_settings_offset( $basics ) {

	$basics['offset'] = array(
		'title'         => __( 'Offset' ),
		'description'   => __( 'Number of post to skip, or pass over. For example, if this field is 3, the first 3 items will be skipped and not displayed.' ),
		'query_args_callback' => 'qw_generate_query_args_offset',
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

	return $basics;
}

function qw_generate_query_args_offset( &$args, $filter ){
	$args['offset'] = $filter['values']['offset'];
}

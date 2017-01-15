<?php

add_filter( 'qw_handlers','qw_handler_type_field' );

/**
 * Fields add data to a row for use by the row_style for displaying data from a
 * post
 *
 * @param $handlers
 *
 * @return array
 */
function qw_handler_type_field( $handlers )
{
	$handlers['field']    = array(
		'title'            => __( 'Field' ),
		'description'      => __( 'Select Fields to add to this query output.' ),
		'all_callback'     => 'qw_all_field_handler_item_types',
	);

	return $handlers;
}

/**
 * Get all "Field" handler item types
 *
 * @param $handler
 *
 * @return array
 */
function qw_all_field_handler_item_types( $handler = NULL )
{
	$handler_item_types = apply_filters( 'qw_fields', array() );
	$handler_item_types = qw_set_hook_types( $handler_item_types );

	if ( $handler ){
		$handler_item_types = qw_pre_process_handler_item_types( $handler_item_types, $handler );
	}

	return $handler_item_types;
}

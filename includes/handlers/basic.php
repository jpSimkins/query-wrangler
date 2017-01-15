<?php

add_filter( 'qw_handlers','qw_handler_type_basic' );

/**
 * Fields add data to a row for use by the row_style for displaying data from a
 * post
 *
 * @param $handlers
 *
 * @return array
 */
function qw_handler_type_basic( $handlers )
{
	$handlers['basic'] = array(
		'title'            => __( 'Basic' ),
		'description'      => __( 'Select Basics to add to this query.' ),
		'all_callback'     => 'qw_all_basic_handler_item_types',
	);

	return $handlers;
}

/**
 * Get all "Basic" types registered w/ QW
 *
 * @param $handler
 *
 * @return array
 */
function qw_all_basic_handler_item_types( $handler )
{
	$handler_item_types = apply_filters( 'qw_basics', array() );
	$handler_item_types = qw_pre_process_handler_item_types( $handler_item_types, $handler );

	uasort( $handler_item_types, 'qw_cmp' );

	return $handler_item_types;
}

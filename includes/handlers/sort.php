<?php

add_filter( 'qw_handlers','qw_handler_type_sort' );

/**
 * Sorts add arguments to the array of arguments passed into WP_Query()
 *
 * @param $handlers
 *
 * @return array
 */
function qw_handler_type_sort( $handlers )
{
	$handlers['sort']     = array(
		'title'            => __( 'Sort Option' ),
		'description'      => __( 'Select options for sorting the query results.' ),
		'all_callback'     => 'qw_all_sort_handler_item_types',
	);

	return $handlers;
}

/**
 * Get all "Sort" (Order) handler item types
 *
 * @param $handler
 *
 * @return array
 */
function qw_all_sort_handler_item_types( $handler )
{
	$handler_item_types = apply_filters( 'qw_sort_options', array() );
	$handler_item_types = qw_pre_process_handler_item_types( $handler_item_types, $handler );
	$handler_item_types = qw_set_hook_types( $handler_item_types );

	return $handler_item_types;
}
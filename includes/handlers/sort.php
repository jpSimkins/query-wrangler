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
	$handler = new QW_Handler_Type(
		'sort',
		__( 'Sort' ),
		__( 'Select options for sorting the query results.' ),
		'qw_sort_options'
	);

	$handlers[ $handler->hook_key ] = $handler;

	return $handlers;
}

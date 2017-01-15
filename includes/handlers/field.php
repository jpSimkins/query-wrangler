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
	$handler = new QW_Handler_Type(
		'field',
		__( 'Field' ),
		__( 'Select Fields to add to this query output.' ),
		'qw_fields'
	);

	$handlers[ $handler->hook_key ] = $handler;

	return $handlers;
}

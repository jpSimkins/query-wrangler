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
	$handler = new QW_Handler_Type(
		'basic',
		__( 'Basic' ),
		__( 'Select Basics to add to this query.' ),
		'qw_basics'
		);

	$handlers[ $handler->hook_key ] = $handler;

	return $handlers;
}

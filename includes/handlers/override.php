<?php

add_filter( 'qw_handlers','qw_handler_type_override' );

/**
 * Override existing WP pages with a QW query
 *
 * @param $handlers
 *
 * @return array
 */
function qw_handler_type_override( $handlers )
{
	$handler = new QW_Handler_Type(
		'override',
		__( 'Override' ),
		__( 'Select overrides to affect the query results based on the context of where the query is displayed.' ),
		'qw_overrides'
	);

	$handlers[ $handler->hook_key ] = $handler;

	return $handlers;
}

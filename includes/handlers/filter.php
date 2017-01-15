<?php

add_filter( 'qw_handlers','qw_handler_type_filter' );

add_filter( 'qw_generate_query_args', 'qw_generate_filter_callback_args', 0, 2 );

/**
 * Filters add arguments to the array of arguments passed into WP_Query()
 *
 * @param $handlers
 *
 * @return array
 */
function qw_handler_type_filter( $handlers )
{
	$handler = new QW_Handler_Type(
		'filter',
		__( 'Filter' ),
		__( 'Select filters to affect the query results.' ),
		'qw_filters'
	);

	$handlers[ $handler->hook_key ] = $handler;

	return $handlers;
}

/**
 * Filters require a callback for setting their values in the $args array.
 * This processes those callbacks.
 *
 * @param $args
 * @param $options
 *
 * @return mixed
 */
function qw_generate_filter_callback_args( $args, $options ){

	$handlers = qw_get_query_handlers( $options );

	foreach ( $handlers as $handler_type => $handler ) {
		if ( is_array( $handler['items'] ) ) {
			foreach ( $handler['items'] as $name => $item ) {
				// Alter the query args
				// look for callback, and run it
				if ( isset( $item['query_args_callback'] ) && is_callable( $item['query_args_callback'] ) ) {
					call_user_func_array( $item['query_args_callback'], array( &$args, $item ) );
				}
			}
		}
	}

	return $args;
}

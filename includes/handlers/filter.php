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
	$handlers['filter']   = array(
		'title'            => __( 'Filter' ),
		'description'      => __( 'Select filters to affect the query results.' ),
		'all_callback'     => 'qw_all_filters',
		'data_callback'    => 'qw_handler_type_filter_get_data',
		'set_data_callback'=> 'qw_handler_type_filter_set_data',
		'form_prefix'      => '[args][filters]',
	);

	return $handlers;
}

/**
 * Get all "Filter" handler item types
 *
 * @return array
 */
function qw_all_filters()
{
	$filters = apply_filters( 'qw_filters', array() );
	$filters = qw_set_hook_keys( $filters );
	$filters = qw_set_hook_types( $filters );

	return $filters;
}

/**
 * Retrieve existing filter data from an array of query options
 *
 * @param $options
 *
 * @return array
 */
function qw_handler_type_filter_get_data( $options )
{
	$data = array();

	if ( !empty( $options['args']['filters'] ) ) {
		$data = $options['args']['filters'];
	}

	return $data;
}

/**
 * @param $data
 * @param $key
 * @param $value
 *
 * @return array
 */
function qw_handler_type_filter_set_data( $data, $key, $value )
{
	$data['args']['filters'][ $key ] = $value;

	return $data;
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

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
		'all_callback'     => 'qw_all_sort_options',
		'data_callback'    =>  'qw_handler_type_sort_get_data',
		'set_data_callback'=>  'qw_handler_type_sort_set_data',
		'form_prefix'      => '[args][sorts]',
	);

	return $handlers;
}

/**
 * Retrieve existing sort data from an array of query options
 *
 * @param $options
 *
 * @return array
 */
function qw_handler_type_sort_get_data( $options )
{
	$data = array();

	if ( !empty( $options['args']['sorts'] ) ) {
		$data = $options['args']['sorts'];
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
function qw_handler_type_sort_set_data( $data, $key, $value )
{
	$data['args']['sorts'][ $key ] = $value;

	return $data;
}
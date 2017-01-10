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
	$handlers['override'] = array(
		'title'            => __( 'Override' ),
		'description'      => __( 'Select overrides to affect the query results based on the context of where the query is displayed.' ),
		'all_callback'     => 'qw_all_overrides',
		'data_callback'    => 'qw_handler_type_override_get_data',
		'set_data_callback'=> 'qw_handler_type_override_set_data',
		'form_prefix'      => '[override]',
	);

	return $handlers;
}

/**
 * Retrieve existing override data from an array of query options
 *
 * @param $options
 * @return array
 */
function qw_handler_type_override_get_data( $options )
{
	$data = array();

	if ( !empty( $options['override'] ) ) {
		$data = $options['override'];
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
function qw_handler_type_override_set_data( $data, $key, $value )
{
	$data['override'][ $key ] = $value;

	return $data;
}
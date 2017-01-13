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
	$handlers['field']    = array(
		'title'            => __( 'Field' ),
		'description'      => __( 'Select Fields to add to this query output.' ),
		'all_callback'     => 'qw_all_fields',
		'data_callback'    => 'qw_handler_type_field_get_data',
		'set_data_callback'=> 'qw_handler_type_field_set_data',
		'form_prefix'      => '[display][field_settings][fields]',
	);

	return $handlers;
}

/**
 * Get all "Field" handler item types
 *
 * @return array
 */
function qw_all_fields()
{
	$fields = apply_filters( 'qw_fields', array() );
	$fields = qw_set_hook_keys( $fields );
	$fields = qw_set_hook_types( $fields );

	return $fields;
}

/**
 * Retrieve existing Field data from an array of query options
 *
 * @param $options
 *
 * @return array
 */
function qw_handler_type_field_get_data( $options )
{
	$data = array();

	if ( !empty( $options['display']['field_settings']['fields'] ) ) {
		$data = $options['display']['field_settings']['fields'];
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
function qw_handler_type_field_set_data( $data, $key, $value )
{
	$data['display']['field_settings']['fields'][ $key ] = $value;

	return $data;
}
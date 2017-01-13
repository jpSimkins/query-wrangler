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
	$handlers['basic'] = array(
		'title'            => __( 'Basic' ),
		'description'      => __( 'Select Basics to add to this query.' ),
		'all_callback'     => 'qw_all_basic_settings',
		'data_callback'    => 'qw_handler_type_basic_get_data',
		'set_data_callback'=> 'qw_handler_type_basic_set_data',
		'form_prefix'      => '[display][basic]',
	);

	return $handlers;
}

/**
 * Get all "Basic" types registered w/ QW
 *
 * @return array
 */
function qw_all_basic_settings()
{
	$basics = apply_filters( 'qw_basics', array() );
	$basics = qw_set_hook_keys( $basics );
//	$basics = qw_set_hook_types( $basics );

	uasort( $basics, 'qw_cmp' );

	//d($basics);

	return $basics;
}

/**
 * Retrieve existing Field data from an array of query options
 *
 * @param $options
 *
 * @return array
 */
function qw_handler_type_basic_get_data( $options )
{
	$data = array();

	if ( !empty( $options['display']['basic'] ) ) {
		$data = $options['display']['basic'];
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
function qw_handler_type_basic_set_data( $data, $key, $value )
{
	$data['display']['basic'][ $key ] = $value;

	return $data;
}
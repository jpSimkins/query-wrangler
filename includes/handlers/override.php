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
 * Get all "Override" handler item types
 *
 * @return array
 */
function qw_all_overrides()
{
	$overrides = apply_filters( 'qw_overrides', array() );
	$overrides = qw_set_hook_keys( $overrides );

	foreach ( $overrides as $type => $override ) {
		// set override's type as a value if not provided by override
		if ( empty( $override['type'] ) ) {
			$overrides[ $type ]['type'] = $type;
		}
	}

	// sort them by title
	$titles = array();
	foreach ( $overrides as $key => $override ) {
		$titles[ $key ] = $override['title'];
	}
	array_multisort( $titles, SORT_ASC, $overrides );

	return $overrides;
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
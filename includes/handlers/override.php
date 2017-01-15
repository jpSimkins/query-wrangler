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
		'all_callback'     => 'qw_all_override_handler_item_types',
	);

	return $handlers;
}

/**
 * Get all "Override" handler item types
 *
 * @param $handler
 *
 * @return array
 */
function qw_all_override_handler_item_types( $handler = NULL )
{
	$handler_item_types = apply_filters( 'qw_overrides', array() );
	$handler_item_types = qw_set_hook_types( $handler_item_types );

	if ( $handler ){
		$handler_item_types = qw_pre_process_handler_item_types( $handler_item_types, $handler );
	}

	return $handler_item_types;
}

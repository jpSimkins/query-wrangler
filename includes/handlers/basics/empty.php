<?php

// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_empty_settings' );

add_filter( 'qw_template_query_wrapper_args', 'qw_template_query_empty_wrapper_args', 10, 3 );

/**
 * Header is a simple textarea displayed if the query has no results
 *
 * @param $basics
 * @return mixed
 */
function qw_basic_empty_settings( $basics )
{
	$basics['empty'] = array(
		'title'         => __( 'Empty Text' ),
		'description'   => __( 'The content placed here will appear if the query has no results.' ),
		'weight'        => 7,
		'required'      => true,
		'form_fields' => array(
			'empty' => array(
				'type' => 'textarea',
				'name' => 'empty',
				'class' => array( 'qw-field-textarea', 'qw-js-title' ),
			)
		),
	);

	return $basics;
}

/**
 * Add empty to wrapper arguments
 *
 * @param $args
 * @param $wp_query
 * @param $options
 *
 * @return mixed
 */
function qw_template_query_empty_wrapper_args( $args, $wp_query, $options )
{
	if ( count( $wp_query->posts ) <= 0 &&
	     !empty( $options['display']['basic']['empty']['empty'] ) )
	{
		$args['content'] = '<div class="query-empty">' . $options['display']['basic']['empty']['empty'] . '</div>';
	}

	return $args;
}

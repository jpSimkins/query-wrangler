<?php

// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_footer_settings' );

add_filter( 'qw_template_query_wrapper_args', 'qw_template_query_header_wrapper_args', 10, 3 );

/**
 * Footer is a simple textarea displayed beneath the query
 *
 * @param $basics
 * @return mixed
 */
function qw_basic_footer_settings( $basics )
{
	$basics['footer'] = array(
		'title'         => __( 'Footer' ),
		'description'   => __( 'The content placed here will appear below the resulting query.' ),
		'weight'        => 6,
		'required'      => true,
		'form_fields' => array(
			'footer' => array(
				'type' => 'textarea',
				'name' => 'footer',
				'class' => array( 'qw-field-textarea', 'qw-js-title' ),
			)
		),
	);

	return $basics;
}

/**
 * Add footer to wrapper arguments
 *
 * @param $args
 * @param $wp_query
 * @param $options
 *
 * @return mixed
 */
function qw_template_query_footer_wrapper_args( $args, $wp_query, $options )
{
	if ( !empty( $options['display']['basic']['footer']['footer'] ) ) {
		$args['footer'] = $options['display']['basic']['footer']['footer'];
	}

	return $args;
}

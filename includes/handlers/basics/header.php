<?php

// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_header_settings' );

add_filter( 'qw_template_query_wrapper_args', 'qw_template_query_header_wrapper_args', 10, 3 );

/**
 * Header is a simple textarea displayed above the query
 *
 * @param $basics
 * @return mixed
 */
function qw_basic_header_settings( $basics )
{
	$basics['header'] = array(
		'title'         => __( 'Header' ),
		'description'   => __( 'The content placed here will appear above the resulting query.' ),
		'weight'        => 5,
		'required'      => true,
		'form_prefix'   => QW_FORM_PREFIX . '[display]',
		'form_fields' => array(
			array(
				'type' => 'textarea',
				'name' => 'header',
				'class' => array( 'qw-field-textarea', 'qw-js-title' ),
			)
		)
	);

	return $basics;
}

/**
 * Add header to wrapper arguments
 *
 * @param $args
 * @param $wp_query
 * @param $options
 *
 * @return mixed
 */
function qw_template_query_header_wrapper_args( $args, $wp_query, $options )
{
	if ( !empty( $options['display']['header'] ) ) {
		$args['header'] = $options['display']['header'];
	}

	return $args;
}
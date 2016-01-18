<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_meta_value' );

/**
 * @param $filters
 * @return mixed
 */
function qw_filter_meta_value( $filters ) {
	$filters['meta_value'] = array(
		'title'               => __( 'Meta Value' ),
		'description'         => __( 'Filter for a specific meta_value.' ),
		'form_callback'       => 'qw_filter_meta_value_form',
		'query_args_callback' => 'qw_generate_query_args_meta_value',
		'query_display_types' => array( 'page', 'widget', 'override' ),
	);

	return $filters;
}

/**
 * @param $filter
 */
function qw_filter_meta_value_form( $filter ) {
	$form = new QW_Form_Fields( array(
			'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
			'type' => 'textarea',
			'name' => 'meta_value',
			'value' => isset( $filter['values']['meta_value'] ) ? $filter['values']['meta_value'] : '',
			'class' => array( 'qw-js-title' ),
	) );
}

/**
 * @param $args
 * @param $filter
 */
function qw_generate_query_args_meta_value( &$args, $filter ) {
	if ( isset( $filter['values']['meta_value'] ) ) {
		$args['meta_value'] = stripslashes( $filter['values']['meta_value'] );
	}
}
<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_meta_key' );

function qw_filter_meta_key( $filters ) {
	$filters['meta_key'] = array(
		'title'               => __( 'Meta Key' ),
		'description'         => __( 'Filter for a specific meta_key.' ),
		'form_callback'       => 'qw_filter_meta_key_form',
		'query_args_callback' => 'qw_generate_query_args_meta_key',
		'query_display_types' => array( 'page', 'widget', 'override' ),
	);

	return $filters;
}

function qw_filter_meta_key_form( $filter ) {
	$form = new QW_Form_Fields( array(
			'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
			'type' => 'text',
			'name' => 'meta_key',
			'description' => __( 'The meta_key for filtering results.' ),
			'value' => isset( $filter['values']['meta_key'] ) ? $filter['values']['meta_key'] : '',
			'class' => array( 'qw-js-title' ),
	) );
}

function qw_generate_query_args_meta_key( &$args, $filter ) {
	$args['meta_key'] = $filter['values']['meta_key'];
}
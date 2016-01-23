<?php
// hook into qw_basics
add_filter( 'qw_filters', 'qw_basic_settings_offset' );

/*
 * Basic Settings
 */
function qw_basic_settings_offset( $basics ) {

	$basics['offset'] = array(
		'title'         => __( 'Offset' ),
		'description'   => __( 'Number of post to skip, or pass over. For example, if this field is 3, the first 3 items will be skipped and not displayed.' ),
		'form_callback' => 'qw_basic_offset_form',

		'query_args_callback' => 'qw_generate_query_args_offset',
		'query_display_types' => array( 'page', 'widget', 'override' ),

	);

	return $basics;
}

/**
 * @param $filter
 * @param $args
 */
function qw_basic_offset_form( $filter ) {

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'number',
		'name' => 'offset',
		'description' => $filter['description'],
		'value' => isset( $filter['values']['offset'] ) ? $filter['values']['offset'] : 0,
		'class' => array( 'qw-js-title' ),
	) );
}

function qw_generate_query_args_offset( &$args, $filter ){
	$args['offset'] = $filter['values']['offset'];
}

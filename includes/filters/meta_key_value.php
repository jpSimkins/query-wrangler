<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_meta_key_value' );

/**
 * @param $filters
 * @return mixed
 */
function qw_filter_meta_key_value( $filters ) {

	$filters['meta_key_value'] = array(
		'title'               => __( 'Meta Key/Value Compare' ),
		'description'         => __( 'Filter for a specific meta_key / meta_value pair.' ),
		'form_callback'       => 'qw_filter_meta_key_value_form',
		'query_args_callback' => 'qw_generate_query_args_meta_key_value',
		'query_display_types' => array( 'page', 'widget', 'override' ),
	);

	return $filters;
}

/**
 * @param $filter
 */
function qw_filter_meta_key_value_form( $filter ) {
	$form = new QW_Form_Fields( array(
			'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
			'type' => 'text',
			'name' => 'meta_key',
			'title' => __( 'Meta Key' ),
			'value' => isset( $filter['values']['meta_key'] ) ? $filter['values']['meta_key'] : '',
			'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
			'type' => 'select',
			'name' => 'meta_compare',
			'title' => __( 'Meta Compare' ),
			'description' => __( 'Determine how the query is filterd by the key value pairs.' ),
			'value' => isset( $filter['values']['meta_compare'] ) ? $filter['values']['meta_compare'] : '',
			'options' => array(
				"="  => __( "Is equal to" ),
				"!=" => __( "Is not equal to" ),
				"<"  => __( "Is less than" ),
				"<=" => __( "Is less than or equal to" ),
				">"  => __( "Is greater than" ),
				">=" => __( "Is greater than or equal to" ),
			),
			'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
			'type' => 'text',
			'name' => 'meta_value',
			'title' => __( 'Meta Value' ),
			'value' => isset( $filter['values']['meta_value'] ) ? stripcslashes( $filter['values']['meta_value'] ) : '',
			'class' => array( 'qw-js-title' ),
	) );
}

/**
 * @param $args
 * @param $filter
 */
function qw_generate_query_args_meta_key_value( &$args, $filter ) {
	$args['meta_key']     = $filter['values']['meta_key'];
	$args['meta_value']   = stripslashes( $filter['values']['meta_value'] );
	$args['meta_compare'] = $filter['values']['meta_compare'];
}
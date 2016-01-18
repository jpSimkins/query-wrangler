<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_meta_query' );

/**
 * @param $filters
 *
 * @return mixed
 */
function qw_filter_meta_query( $filters ) {

	$filters['meta_query'] = array(
		'title'               => __( 'Meta Query' ),
		'description'         => __( 'Filter for a single meta query' ),
		'form_callback'       => 'qw_filter_meta_query_form',
		'query_args_callback' => 'qw_generate_query_args_meta_query',
		'query_display_types' => array( 'page', 'widget', 'override' ),
	);

	return $filters;
}

/**
 * @param $filter
 */
function qw_filter_meta_query_form( $filter ) {
	$form = new QW_Form_Fields( array(
	    'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
	    'type' => 'text',
	    'name' => 'key',
	    'title' => __( 'Meta Key' ),
	    'value' => isset( $filter['values']['key'] ) ? $filter['values']['key'] : '',
	    'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
	    'type' => 'text',
	    'name_prefix' => '[value]',
	    'name' => '0',
	    'title' => __( 'Meta Value 1' ),
	    'value' => isset( $filter['values']['value'][0] ) ?  stripcslashes( $filter['values']['value'][0] ) : '',
	    'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
	    'type' => 'text',
	    'name_prefix' => '[value]',
	    'name' => '1',
	    'title' => __( 'Meta Value 2' ),
	    'description' => __( "Only use Meta Value 2 when compare is 'IN', 'NOT IN', 'BETWEEN', or 'NOT BETWEEN'." ),
	    'value' => isset( $filter['values']['value'][1] ) ?  stripcslashes( $filter['values']['value'][1] ) : '',
	    'class' => array( 'qw-js-title' ),
	) );

	$types = array(
		"CHAR",
		"NUMERIC",
		"BINARY",
		"DATE",
		"DATETIME",
		"DECIMAL",
		"SIGNED",
		"TIME",
		"UNSIGNED"
	);

	print $form->render_field( array(
	    'type' => 'select',
	    'name' => 'type',
	    'title' => __( 'Type' ),
	    'description' => __( '' ),
	    'value' => isset( $filter['values']['type'] ) ? $filter['values']['type'] : '',
	    'options' => array_combine( $types, $types ),
	    'class' => array( 'qw-js-title' ),
	) );

	$compares = array(
		"=",
		"!=",
		"<",
		"<=",
		">",
		">=",
		"LIKE",
		"NOT LIKE",
		"IN",
		"NOT IN",
		"BETWEEN",
		"NOT BETWEEN",
		"EXISTS",
		"NOT EXISTS",
	);
	print $form->render_field( array(
	    'type' => 'select',
	    'name' => 'compare',
	    'title' => __( 'Compare' ),
	    'value' => isset( $filter['values']['compare'] ) ? $filter['values']['compare'] : '',
	    'options' => array_combine( $compares, $compares ),
	    'class' => array( 'qw-js-title' ),
	) );
}

/**
 * @param $args
 * @param $filter
 */
function qw_generate_query_args_meta_query( &$args, $filter ) {
	if ( ! empty( $filter['values']['value'][1] ) ) {
		$value = $filter['values']['value'];
	} else {
		$value = $filter['values']['value'][0];
	}

	$args['meta_query'][] = array(
		'key'     => $filter['values']['key'],
		'value'   => $value,
		'compare' => $filter['values']['compare'],
		'type'    => $filter['values']['type'],
	);
}
<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_categories' );

function qw_filter_categories( $filters ) {

	$filters['categories'] = array(
		'title'               => __( 'Categories' ),
		'description'         => __( 'Select which categories to pull posts from, and how to treat those categories.' ),
		'form_callback'       => 'qw_filter_categories_form',
		'query_args_callback' => 'qw_generate_query_args_categories',
		'query_display_types' => array( 'page', 'widget' ),
	);

	return $filters;
}

/**
 * Options for "categories" filter
 *
 * @param $filter
 */
function qw_filter_categories_form( $filter ) {
	$form = new QW_Form_Fields( array(
			'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
			'type' => 'select',
			'name' => 'cat_operator',
			'title' => __( 'Categories operator' ),
			'description' => __( 'Determines how the selected categories are queried.' ),
			'value' => isset( $filter['values']['cat_operator'] ) ? $filter['values']['cat_operator'] : '',
			'options' => array(
				"cat"              => __( "Any category plus children categories" ),
				"category__in"     => __( "Any category without children categories" ),
				"category__and"    => __( "All categories selected" ),
				"category__not_in" => __( "Not in the categories selected" ),
			),
			'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
			'type' => 'checkboxes',
			'name' => 'cats',
			'title' => __( 'Categories' ),
			'value' => isset( $filter['values']['cats'] ) ? $filter['values']['cats'] : array(),
			'options' => get_terms( 'category', array( 'fields' => 'id=>name', 'hide_empty' => 0 ) ),
			'class' => array( 'qw-js-title' ),
	) );
}

/**
 * Alter the values for the given category operator
 *
 * @param $args
 * @param $filter
 */
function qw_generate_query_args_categories( &$args, $filter ) {
	// category__not_in wants and array of term ids
	if ( isset( $filter['values']['cat_operator'] ) && isset( $filter['values']['cats'] ) ) {
		if ( $filter['values']['cat_operator'] == 'category__not_in' && is_array( $filter['values']['cats'] ) ) {
			$args[ $filter['values']['cat_operator'] ] = array_keys( $filter['values']['cats'] );
		} // cats wants a comma separated string
		else if ( $filter['values']['cat_operator'] == 'cat' && is_array( $filter['values']['cats'] ) ) {
			$args[ $filter['values']['cat_operator'] ] = implode( ",",
				array_keys( $filter['values']['cats'] ) );
		}
	} //
	else if ( isset( $filter['values']['cats'] ) ) {
		$args[ $filter['values']['cat_operator'] ] = $filter['values']['cats'];
	}
}
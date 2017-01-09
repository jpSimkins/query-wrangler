<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_categories' );

function qw_filter_categories( $filters ) {

	$filters['categories'] = array(
		'title'               => __( 'Categories' ),
		'description'         => __( 'Select which categories to pull posts from, and how to treat those categories.' ),
		'query_args_callback' => 'qw_generate_query_args_categories',
		'query_display_types' => array( 'page', 'widget' ),
		'form_fields' => array(
			'cat_operator' => array(
				'type' => 'select',
				'name' => 'cat_operator',
				'title' => __( 'Categories operator' ),
				'description' => __( 'Determines how the selected categories are queried.' ),
				'options' => array(
					'cat'              => __( 'Any category plus children categories' ),
					'category__in'     => __( 'Any category without children categories' ),
					'category__and'    => __( 'All categories selected' ),
					'category__not_in' => __( 'Not in the categories selected' ),
				),
				'class' => array( 'qw-js-title' ),
			),
			'cats' => array(
				'type' => 'checkboxes',
				'name' => 'cats',
				'title' => __( 'Categories' ),
				'options' => get_terms( 'category', array( 'fields' => 'id=>name', 'hide_empty' => 0 ) ),
				'default_value' => array(),
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $filters;
}

/**
 * Alter the values for the given category operator
 *
 * @param $args
 * @param $filter
 */
function qw_generate_query_args_categories( &$args, $filter ) {
	// category__not_in wants an array of term ids
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

<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_simple_basic_settings' );

/**
 * Simple basic settings don't require much work beyond a few form fields
 *
 * @param $basics
 * @return mixed
 */
function qw_simple_basic_settings( $basics ) {
	$basics['title'] = array(
		'title'         => __( 'Title' ),
		'description'   => __( 'The title above the query page or widget' ),
		'weight'        => 1,
		'required'      => true,
		'form_fields' => array(
			'display_title' => array(
				'type' => 'text',
				'name' => 'title',
				'class' => array( 'qw-text-long', 'qw-js-title' ),
			)
		),
	);
	$basics['wrapper_classes'] = array(
		'title'         => __( 'Wrapper Classes' ),
		'description'   => __( 'The CSS class names will be added to the query. This enables you to use specific CSS code for each query. You may define multiples classes separated by spaces.' ),
		'weight'        => 4,
		'required'      => true,
		'form_fields' => array(
			'wrapper_classes' => array(
				'type' => 'text',
				'name' => 'wrapper_classes',
				'class' => array( 'qw-text-long', 'qw-js-title' ),
			)
		)
	);
	$basics['page_path'] = array(
		'title'               => __( 'Page path' ),
		'description'         => __( 'The path or permalink you want this page to use. Avoid using spaces and capitalization for best results.' ),
		'query_display_types' => array( 'page', ),
		'weight'              => 10,
		'required'      => true,
		'form_fields' => array(
			'page_path' => array(
				'type' => 'text',
				'name' => 'path',
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $basics;
}


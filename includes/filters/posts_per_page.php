<?php

add_filter( 'qw_filters', 'qw_basic_settings_posts_per_page' );

/*
 * Basic Settings
 */
function qw_basic_settings_posts_per_page( $basics ) {

	$basics['posts_per_page'] = array(
		'title'         => __( 'Posts Per Page' ),
		'description'   => __( 'Number of posts to show per page. Use -1 to display all results.' ),
		'form_callback' => 'qw_basic_posts_per_page_form',

		'query_args_callback' => 'qw_generate_query_args_posts_per_page',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'required' => true,

	);

	return $basics;
}

/**
 * @param $filter
 * @param $args
 */
function qw_basic_posts_per_page_form( $filter ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'posts_per_page',
		'description' => $filter['description'],
		'value' => isset( $filter['values']['posts_per_page'] ) ? $filter['values']['posts_per_page'] : 5,
		'class' => array( 'qw-js-title' ),
	) );
}

function qw_generate_query_args_posts_per_page( &$args, $filter ){
	$args['posts_per_page'] = $filter['values']['posts_per_page'];
}

<?php

add_filter( 'qw_filters', 'qw_basic_settings_posts_per_page' );

/*
 * Basic Settings
 */
function qw_basic_settings_posts_per_page( $basics ) {

	$basics['posts_per_page'] = array(
		'title'         => __( 'Posts Per Page' ),
		'description'   => __( 'Number of posts to show per page. Use -1 to display all results.' ),
		'required'      => true,
		'query_args_callback' => 'qw_generate_query_args_posts_per_page',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'form_fields' => array(
			'posts_per_page' => array(
				'type' => 'text',
				'name' => 'posts_per_page',
				'default_value' => 5,
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $basics;
}

function qw_generate_query_args_posts_per_page( &$args, $filter ){
	$args['posts_per_page'] = $filter['values']['posts_per_page'];
}

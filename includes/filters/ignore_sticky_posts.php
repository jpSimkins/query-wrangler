<?php
// hook into qw_basics
add_filter( 'qw_filters', 'qw_basic_ignore_sticky_posts' );

/*
 * Basic Settings
 */
function qw_basic_ignore_sticky_posts( $basics ) {

	$basics['ignore_sticky_posts'] = array(
		'title'         => _( 'Ignore Sticky Posts' ),
		'description'   => __( 'Do not enforce stickiness in the resulting query.' ),
		'query_args_callback' => 'qw_generate_query_args_ignore_sticky_posts',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'required' => true,
		'form_fields' => array(
			'ignore_sticky_post' => array(
				'type' => 'checkbox',
				'name' => 'ignore_sticky_posts',
				'default_value' => 0,
				'class' => array( 'qw-js-title' ),
			)
		),

	);

	return $basics;
}

function qw_generate_query_args_ignore_sticky_posts( &$args, $filter ){
	$args['ignore_sticky_posts'] = $filter['values']['ignore_sticky_posts'];
}
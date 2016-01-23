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
		'form_callback' => 'qw_basic_ignore_sticky_posts_form',

		'query_args_callback' => 'qw_generate_query_args_ignore_sticky_posts',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'required' => true,

	);

	return $basics;
}

/**
 * @param $filter
 * @param $args
 */
function qw_basic_ignore_sticky_posts_form( $filter ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'checkbox',
		'name' => 'ignore_sticky_posts',
		'description' => $filter['description'],
		'value' => isset( $filter['values']['ignore_sticky_posts'] ) ? $filter['values']['ignore_sticky_posts'] : 0,
		'class' => array( 'qw-js-title' ),
	) );
}

function qw_generate_query_args_ignore_sticky_posts( &$args, $filter ){
	$args['ignore_sticky_posts'] = $filter['values']['ignore_stick_posts'];
}
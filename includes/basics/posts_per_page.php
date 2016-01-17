<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_posts_per_page' );

/*
 * Basic Settings
 */
function qw_basic_settings_posts_per_page( $basics ) {

	$basics['posts_per_page'] = array(
		'title'         => __( 'Posts Per Page' ),
		'description'   => __( 'Number of posts to show per page. Use -1 to display all results.' ),
		'option_type'   => 'args',
		'form_callback' => 'qw_basic_posts_per_page_form',
		'weight'        => 0,
	);

	return $basics;
}

/**
 * @param $item
 * @param $args
 */
function qw_basic_posts_per_page_form( $item, $args ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'posts_per_page',
		'description' => $item['description'],
		'value' => isset( $args['posts_per_page'] ) ? $args['posts_per_page'] : 5,
		'class' => array( 'qw-js-title' ),
	) );
}

<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_ignore_sticky_posts' );

/*
 * Basic Settings
 */
function qw_basic_ignore_sticky_posts( $basics ) {

	$basics['ignore_sticky_posts'] = array(
		'title'         => _( 'Ignore Sticky Posts' ),
		'description'   => __( 'Do not enforce stickiness in the resulting query.' ),
		'option_type'   => 'args',
		'form_callback' => 'qw_basic_ignore_sticky_posts_form',
	);

	return $basics;
}

/**
 * @param $item
 * @param $args
 */
function qw_basic_ignore_sticky_posts_form( $item, $args ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'checkbox',
		'name' => 'ignore_sticky_posts',
		'description' => $item['description'],
		'value' => isset( $args['ignore_sticky_posts'] ) ? $args['ignore_sticky_posts'] : 0,
		'class' => array( 'qw-js-title' ),
	) );
}

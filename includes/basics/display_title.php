<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_display_title' );

/*
 * Basic Settings
 */
function qw_basic_settings_display_title( $basics ) {
	$basics['display_title'] = array(
		'title'         => __( 'Display Title' ),
		'description'   => __( 'The title above the query page or widget' ),
		'option_type'   => 'display',
		'form_callback' => 'qw_basic_display_title_form',
		'weight'        => 0,
	);

	return $basics;
}

/**
 * @param $item
 * @param $display
 */
function qw_basic_display_title_form( $item, $display ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'title',
		'description' => $item['description'],
		'value' => isset( $display['title'] ) ? $display['title'] : '',
		'class' => array( 'qw-text-long', 'qw-js-title' ),
	) );
}

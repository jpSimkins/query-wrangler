<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_wrapper_settings' );

/*
 * Basic Settings
 */
function qw_basic_settings_wrapper_settings( $basics ) {
	$basics['wrapper_classes'] = array(
		'title'         => __( 'Wrapper Classes' ),
		'description'   => __( 'The CSS class names will be added to the query. This enables you to use specific CSS code for each query. You may define multiples classes separated by spaces.' ),
		'option_type'   => 'display',
		'form_callback' => 'qw_basic_wrapper_classes_form',
		'weight'        => 0,
	);

	return $basics;
}

/**
 * @param $item
 * @param $display
 */
function qw_basic_wrapper_classes_form( $item, $display ) {

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
			'type' => 'text',
			'name' => 'wrapper-classes',
			'description' => $item['description'],
			'value' => isset( $display['wrapper-classes'] ) ? $display['wrapper-classes'] : '',
			'class' => array( 'qw-js-title' ),
	) );
}

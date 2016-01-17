<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_empty' );

/*
 * Basic Settings
 */
function qw_basic_settings_empty( $basics ) {
	$basics['empty'] = array(
		'title'         => __( 'Empty Text' ),
		'description'   => __( 'The content placed here will appear if the query has no results.' ),
		'option_type'   => 'display',
		'form_callback' => 'qw_basic_empty_form',
		'weight'        => 0,
	);

	return $basics;
}

/**
 * @param $item
 * @param $display
 */
function qw_basic_empty_form( $item, $display ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'textarea',
		'name' => 'empty',
		'description' => $item['description'],
		'value' => isset( $display['empty'] ) ? $display['empty'] : '',
		'class' => array( 'qw-field-textarea', 'qw-js-title' ),
	) );
}

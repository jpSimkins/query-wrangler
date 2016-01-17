<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_offset' );

/*
 * Basic Settings
 */
function qw_basic_settings_offset( $basics ) {

	$basics['offset'] = array(
		'title'         => __( 'Offset' ),
		'description'   => __( 'Number of post to skip, or pass over. For example, if this field is 3, the first 3 items will be skipped and not displayed.' ),
		'option_type'   => 'args',
		'form_callback' => 'qw_basic_offset_form',
		'weight'        => 0,
	);

	return $basics;
}

/**
 * @param $item
 * @param $args
 */
function qw_basic_offset_form( $item, $args ) {

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'number',
		'name' => 'offset',
		'description' => $item['description'],
		'value' => isset( $args['offset'] ) ? $args['offset'] : 0,
		'class' => array( 'qw-js-title' ),
	) );
}

<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_footer' );

/*
 * Basic Settings
 */
function qw_basic_settings_footer( $basics ) {

	$basics['footer'] = array(
		'title'         => __( 'Footer' ),
		'description'   => __( 'The content placed here will appear below the resulting query.' ),
		'option_type'   => 'display',
		'form_callback' => 'qw_basic_footer_form',
		'weight'        => 0,
	);

	return $basics;
}

/**
 * @param $item
 * @param $display
 */
function qw_basic_footer_form( $item, $display ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'textarea',
		'name' => 'footer',
		'description' => $item['description'],
		'value' => isset( $display['footer'] ) ? $display['footer'] : '',
		'class' => array( 'qw-field-textarea', 'qw-js-title' ),
	) );
}

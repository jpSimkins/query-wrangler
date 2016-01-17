<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_header' );

/*
 * Basic Settings
 */
function qw_basic_settings_header( $basics ) {
	$basics['header'] = array(
		'title'         => __( 'Header' ),
		'description'   => __( 'The content placed here will appear above the resulting query.' ),
		'option_type'   => 'display',
		'form_callback' => 'qw_basic_header_form',
		'weight'        => 0,
	);

	return $basics;
}

/**
 * @param $item
 * @param $display
 */
function qw_basic_header_form( $item, $display ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'textarea',
		'name' => 'header',
		'description' => $item['description'],
		'value' => isset( $display['header'] ) ? qw_textarea( $display['header'] ) : '',
		'class' => array( 'qw-field-textarea', 'qw-js-title' ),
	) );
}

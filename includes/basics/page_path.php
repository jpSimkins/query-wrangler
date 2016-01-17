<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_page_path' );

/*
 * Basic Settings
 */
function qw_basic_settings_page_path( $basics ) {
	$basics['page_path'] = array(
		'title'               => __( 'Page path' ),
		'description'         => __( 'The path or permalink you want this page to use. Avoid using spaces and capitalization for best results.' ),
		'option_type'         => 'display',
		'form_callback'       => 'qw_basic_page_path_form',
		'query_display_types' => array( 'page', ),
		'weight'              => 0,
	);

	return $basics;
}

/**
 * @param $item
 * @param $display
 */
function qw_basic_page_path_form( $item, $display ) {

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name_prefix' => '[page]',
		'name' => 'path',
		'description' => $item['description'],
		'value' => isset( $display['page']['path'] ) ? $display['page']['path'] : '',
		'class' => array( 'qw-js-title' ),
	) );
}

<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_page_template' );

/*
 * Basic Settings
 */
function qw_basic_settings_page_template( $basics ) {
	$basics['page_template'] = array(
		'title'               => __( 'Page Template' ),
		'description'         => __( 'Select which page template should wrap this query page.' ),
		'option_type'         => 'display',
		'form_callback'       => 'qw_basic_page_template_form',
		'query_display_types' => array( 'page', 'override' ),
		'weight'              => 0,
	);

	return $basics;
}

/**
 * @param $item
 * @param $display
 */
function qw_basic_page_template_form( $item, $display ) {
	$options = array(
		'__none__'  => __( 'None - Allow theme to determine template' ),
		'index.php' => __( 'Default - index.php' ),
	);
	$options = array_replace( $options, array_flip( get_page_templates() ) );

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name_prefix' => '[page]',
		'name' => 'template-file',
		'description' => $item['description'],
		'value' => isset( $display['page']['template-file'] ) ? $display['page']['template-file'] : '',
		'options' => $options,
		'class' => array( 'qw-js-title' ),
	) );
}

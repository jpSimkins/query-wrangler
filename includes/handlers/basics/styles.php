<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_style' );

/**
 * Display Styles
 *
 * @param $basics
 *
 * @return array
 */
function qw_basic_settings_style( $basics )
{
	$basics['style'] = array(
		'title'         => __( 'Format' ),
		'description'   => __( 'How this query should be styled' ),
		'form_callback' => 'qw_basic_display_style_form',
		'weight'        => 2,
		'required'      => true,
		'form_prefix'   => QW_FORM_PREFIX . '[display][style_settings]',
	);

	return $basics;
}

/**
 *
 * @param $item
 * @param $display
 */
function qw_basic_display_style_form( $item, $display )
{
	$styles = qw_all_styles();
	$styles_options = array();
	foreach ( $styles as $key => $details ) {
		$styles_options[ $key ] = $details['title'];
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'style',
		'description' => $item['description'],
		'value' => isset( $display['style'] ) ? $display['style'] : '',
		'options' => $styles_options,
		'class' => array( 'qw-js-title' ),
	) );
//
//	/*
//	 * Get the current settings values saved to this query
//	 */
//	foreach( $styles as $hook_key => $item ){
//		$styles[ $hook_key ]['values'] = !empty( $display['style_settings'] ) ? $display['style_settings'] : array();
//	}
//
//	print qw_admin_template( 'select-settings-group', array(
//		'items' => $styles,
//		'display' => $display,
//	) );
}

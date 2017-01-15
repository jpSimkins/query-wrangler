<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_style' );

add_filter( 'qw_template_query_template_args', 'qw_template_query_style_template_args', 0, 3 );

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
	);

	return $basics;
}

/**
 * Get all Template Style options for the Basic "Style" handler item type
 *
 * return array
 *
 * @param $handler_item_type
 *
 * @return mixed|void
 */
function qw_all_styles( $handler_item_type = NULL )
{
	$styles = apply_filters( 'qw_styles', array() );

	// @todo uggh
	if ( $handler_item_type ){
		$styles = qw_pre_process_handler_item_type_settings( $styles, $handler_item_type );
	}

	return $styles;
}

/**
 * Form for configuring display style
 *
 * @param $handler_item_type
 * @param $display
 */
function qw_basic_display_style_form( $handler_item_type, $display )
{
	$styles = qw_all_styles( $handler_item_type );
	$styles_options = array();
	foreach ( $styles as $key => $details ) {
		$styles_options[ $key ] = $details['title'];
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $handler_item_type['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'style',
		'description' => $handler_item_type['description'],
		'value' => isset( $display['style'] ) ? $display['style'] : '',
		'options' => $styles_options,
		'class' => array( 'qw-js-title' ),
	) );
}

/**
 * Filter implements - qw_template_query_template_args
 *
 * @param $template_args
 * @param $wp_query
 * @param $options
 *
 * @return array
 */
function qw_template_query_style_template_args( $template_args, $wp_query, $options )
{
	$styles = qw_all_styles();

	$style = $styles[ $options['basic']['style']['style'] ];
	$style['hook_key'] = $options['basic']['style']['style'];

	$template_args['template'] = 'query-' . $style['hook_key'];
	$template_args['style'] = $style['hook_key'];

	return $template_args;
}
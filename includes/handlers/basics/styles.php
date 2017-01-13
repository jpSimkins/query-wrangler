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
 */
function qw_all_styles()
{
	$styles = apply_filters( 'qw_styles', array() );
	$styles = qw_set_hook_keys( $styles );

//	foreach ( $styles as $hook_key => $style ) {
//		$styles[ $hook_key ]['form_prefix'] = QW_FORM_PREFIX . "[display][style_settings][{$style['settings_key']}]";
//	}

	return $styles;
}

/**
 * Form for configuring display style
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

/**
 * Get the settings values for each display style in this query
 *
 * @param $styles
 * @param $display
 *
 * @return mixed
 */
function qw_styles_get_settings_values( $styles, $display )
{
	foreach( $styles as $hook_key => $style )
	{
		$styles[ $hook_key ]['settings'] = array();

		if ( isset( $style['settings_key'], $display[ $style['settings_key'] ] ) ) {
			$styles[ $hook_key ]['settings'] = $display[ $style['settings_key'] ];
		}
	}

	return $styles;
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
	$styles = qw_styles_get_settings_values( $styles, $options['display'] );

	$style = $styles[ $options['display']['style'] ];

	$template_args['template'] = 'query-' . $style['hook_key'];
	$template_args['style'] = $style['hook_key'];
	$template_args['style_settings'] = $style['settings'];

	return $template_args;
}
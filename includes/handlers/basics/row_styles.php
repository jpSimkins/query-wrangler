<?php

add_filter( 'qw_basics', 'qw_basic_settings_row_style' );

add_filter( 'qw_template_query_template_args', 'qw_template_query_row_style_template_args', 10, 3 );

/**
 * Basic Settings
 *
 * @param $basics
 *
 * @return array
 */
function qw_basic_settings_row_style( $basics )
{
	$basics['row_style'] = array(
		'title'         => __( 'Show' ),
		'description'   => __( 'How should each row in this query be presented?' ),
		'form_callback' => 'qw_basic_display_row_style_form',
		'weight'        => 3,
		'required'      => true,
	);

	return $basics;
}

/**
 * Get all Row Style options for the Basic "Row Styles" handler item type
 *
 * @return array
 */
function qw_all_row_styles()
{
	$row_styles = apply_filters( 'qw_row_styles', array() );
	$row_styles = qw_set_hook_keys( $row_styles );

	return $row_styles;
}

/**
 * Get the current settings values saved to this query
 *
 * @param $row_styles
 * @param $display
 *
 * @return mixed
 */
function qw_row_styles_get_settings_values( $row_styles, $display )
{
	foreach ( $row_styles as $hook_key => $style ) {
		$row_styles[ $hook_key ]['values'] = array();

		if ( !empty( $style['settings_key'] ) &&
		     !empty( $display[ $style['settings_key'] ] ) )
		{
			$row_styles[ $hook_key ]['values'] =  $display[ $style['settings_key'] ];
		}
	}

	return $row_styles;
}

/**
 * Callback to display row_styles selection form
 *
 * @param $item
 * @param $options
 */
function qw_basic_display_row_style_form( $item, $options )
{
	$row_styles = qw_all_row_styles();
	$row_styles = qw_row_styles_get_settings_values( $row_styles, $options['display'] );

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	$row_style_options = array();

	foreach ( $row_styles as $key => $details ) {
		$row_style_options[ $key ] = $details['title'];
	}

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'row_style',
		'description' => $item['description'],
		'value' => isset( $item['values']['row_style'] ) ? $item['values']['row_style'] : '',
		'options' => $row_style_options,
		'class' => array( 'qw-js-title', 'qw-select-group-toggle' ),
	) );

	print qw_admin_template( 'select-settings-group', array(
		'items' => $row_styles,
		'display' => $options['display'],
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
function qw_template_query_row_style_template_args( $template_args, $wp_query, $options )
{
	$row_styles = qw_all_row_styles();
	$row_style = $row_styles[ $options['display']['row_style'] ];

	if ( is_callable( $row_style['make_rows_callback'] ) ) {
		$template_args['rows'] = call_user_func( $row_style['make_rows_callback'], $wp_query, $options );
	}

	return $template_args;
}
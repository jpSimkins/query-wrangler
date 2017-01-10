<?php

add_filter( 'qw_basics', 'qw_basic_settings_row_style' );

/**
 * Basic Settings
 *
 * @param $basics
 *
 * @return array
 */
function qw_basic_settings_row_style( $basics )
{
	$basics['display_row_style'] = array(
		'title'         => __( 'Show' ),
		'description'   => __( 'How should each row in this query be presented?' ),
		'form_callback' => 'qw_basic_display_row_style_form',
		'weight'        => 3,
		'required'      => true,
		'form_prefix'   => QW_FORM_PREFIX . '[display]',
	);

	return $basics;
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
 * @param $display
 */
function qw_basic_display_row_style_form( $item, $display )
{
	$row_styles = qw_all_row_styles();
	$row_styles = qw_row_styles_get_settings_values( $row_styles, $display );

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
		'value' => isset( $display['row_style'] ) ? $display['row_style'] : '',
		'options' => $row_style_options,
		'class' => array( 'qw-js-title', 'qw-select-group-toggle' ),
	) );

	print qw_admin_template( 'select-settings-group', array(
		'items' => $row_styles,
		'display' => $display,
	) );
}

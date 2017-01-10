<?php

add_filter( 'qw_basics', 'qw_basic_settings_pager' );

/**
 * Pager types
 *
 * @param $basics
 *
 * @return array
 */
function qw_basic_settings_pager( $basics )
{
	$basics['pager'] = array(
		'title'         => __( 'Pager' ),
		'description'   => __( 'Select which type of pager to use.' ),
		'weight'        => 12,
		'required'      => true,
		'form_prefix'   => QW_FORM_PREFIX . '[display][pager]',
		'form_callback' => 'qw_basic_pager_form',
	);

	return $basics;
}

/**
 * Pager form
 * Additional settings provided by individual pager types
 *
 * @param $pager
 * @param $display
 */
function qw_basic_pager_form( $pager, $display )
{
	$pager_types = qw_all_pager_types();
	$pager_types = qw_pager_types_get_settings( $pager_types, $display );

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $pager['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'checkbox',
		'name' => 'active',
		'value' => !empty( $display['pager']['active'] ),
		'title' => __( 'Use Pagination' ),
		'class' => array( 'qw-js-title' ),
	) );

	/*
	 * Select pager type and settings
	 */
	$pager_types_options = array();

	foreach( $pager_types as $key => $details ){
		$pager_types_options[ $key ] = $details['title'];
	}

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'type',
		'value' => !empty( $display['pager']['type'] ) ? $display['pager']['type'] : '',
		'title' => __( 'Pager Type' ),
		'description' => __( 'Select the type of pager to use.' ),
		'options' => $pager_types_options,
		'class' => array( 'qw-js-title', 'qw-select-group-toggle' ),
	) );

	print qw_admin_template( 'select-settings-group', array(
		'items' => $pager_types,
		'display' => $display,
	) );
}

/**
 * Gather all the settings values for all pager types
 *
 * @param $pager_types
 * @param $display
 *
 * @return mixed
 */
function qw_pager_types_get_settings( $pager_types, $display ){
	// Get the current settings values saved to this query
	foreach( $pager_types as $hook_key => $item ){
		if ( !empty( $item['settings_key'] ) ) {
			$pager_types[ $hook_key ]['values'] = array();

			if ( !empty( $display['pager'][ $item['settings_key'] ] ) ) {
				$pager_types[ $hook_key ]['values'] = $display['pager'][ $item['settings_key'] ];
			}
		}
	}

	return $pager_types;
}

/**
 * Custom Pager function
 *
 * @param $pager_type
 * @param object $qw_query Object
 * @param $display
 *
 * @return string HTML processed pager
 */
function qw_make_pager( $pager_type, &$qw_query, $display ) {
	$pagers = qw_all_pager_types();
	$pager = $pagers[ $pager_type ];

	$callback = $pagers['default']['callback'];

	if ( is_callable( $pager['callback'] ) ) {
		$callback = $pager['callback'];
	}

	$pager['values'] = $display['pager'][ $pager['settings_key'] ];

	// execute callback
	$pager_themed = call_user_func( $callback, $pager, $qw_query );

	return $pager_themed;
}

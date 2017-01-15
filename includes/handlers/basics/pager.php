<?php

add_filter( 'qw_basics', 'qw_basic_settings_pager' );

add_filter( 'qw_generate_query_args', 'qw_generate_pager_query_args', 20, 2 );
add_filter( 'qw_template_query_wrapper_args', 'qw_template_query_pager_wrapper_args', 10, 3 );

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
		'form_callback' => 'qw_basic_pager_form',
	);

	return $basics;
}

/**
 * Get all Pager options for the Basic "Pager" handler item type
 *
 * @param $handler_item_type
 *
 * @return array
 */
function qw_all_pager_types( $handler_item_type )
{
	$pagers = apply_filters( 'qw_pager_types', array() );
	$pagers = qw_set_hook_keys( $pagers );

	foreach( $pagers as $hook_key => $pager ){
		if ( !empty( $pager['settings_key'] ) ){
			$pagers[ $hook_key ]['form_prefix'] = "{$handler_item_type['form_prefix']}[{$pager['settings_key']}]";
		}
	}

	return $pagers;
}

/**
 * Pager form
 * Additional settings provided by individual pager types
 *
 * @param $handler_item_type
 * @param $options
 */
function qw_basic_pager_form( $handler_item_type, $options )
{
	$pager_types = qw_all_pager_types( $handler_item_type );
	$pager_types = qw_pager_types_get_settings( $pager_types, $options );

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $handler_item_type['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'checkbox',
		'name' => 'active',
		'value' => !empty( $handler_item_type['values']['active'] ),
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
		'value' => !empty( $handler_item_type['values']['type'] ) ? $handler_item_type['values']['type'] : '',
		'title' => __( 'Pager Type' ),
		'description' => __( 'Select the type of pager to use.' ),
		'options' => $pager_types_options,
		'class' => array( 'qw-js-title', 'qw-select-group-toggle' ),
	) );

	print qw_admin_template( 'select-settings-group', array(
		'handler_item_type' => $handler_item_type,
		'settings_group_options' => $pager_types,
		'query_data' => $options,
	) );
}

/**
 * Gather all the settings values for all pager types
 *
 * @param $pager_types
 * @param $options
 *
 * @return mixed
 */
function qw_pager_types_get_settings( $pager_types, $options ){
	// Get the current settings values saved to this query
	foreach( $pager_types as $hook_key => $item ){
		if ( !empty( $item['settings_key'] ) ) {
			$pager_types[ $hook_key ]['values'] = array();

			if ( !empty( $options['pager'][ $item['settings_key'] ] ) ) {
				$pager_types[ $hook_key ]['values'] = $options['pager'][ $item['settings_key'] ];
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

/**
 * @param $args
 * @param $options
 *
 * @return mixed
 */
function qw_generate_pager_query_args( $args, $options ){
	$paged = NULL;

	// if pager_key is enabled, trick qw_get_page_number
	if ( isset( $options['display']['page']['pager']['use_pager_key'] ) &&
	     isset( $options['display']['page']['pager']['pager_key'] ) &&
	     isset( $_GET[ $options['display']['page']['pager']['pager_key'] ] ) &&
	     is_numeric( $_GET[ $options['display']['page']['pager']['pager_key'] ] )
	) {
		$paged = $_GET[ $options['display']['page']['pager']['pager_key'] ];
	}

	// standard arguments
	$args['paged'] = ( $paged ) ? $paged : qw_get_page_number();

	// having any offset will break pagination
	if ( $args['paged'] > 1 ){
		unset( $args['offset'] );
	}

	return $args;
}

/**
 * Filter implements - qw_template_query_wrapper_args
 *
 * @param $wrapper_args
 * @param $wp_query
 * @param $options
 *
 * @return mixed
 */
function qw_template_query_pager_wrapper_args( $wrapper_args, $wp_query, $options )
{
	if ( count( $wp_query->posts ) > 0 &&
	     !empty( $options['display']['page']['pager']['active'] ) &&
	     !empty( $options['display']['page']['pager']['type'] ) )
	{
		$type = $options['display']['page']['pager']['type'];

		$pager_classes = array(
			'query-pager',
			"pager-{$type}",
		);

		$wrapper_args['pager_classes'] = implode( " ", $pager_classes );
		$wrapper_args['pager'] = qw_make_pager( $type, $wp_query, $options['display'] );
	}

	return $wrapper_args;
}

/**
 * Helper function: Get the current page number
 *
 * @param object $wp_query - the query being displayed
 *
 * @return int - the currentpage number
 */
function qw_get_page_number( $wp_query = NULL )
{
	// help figure out the current page
	$path_array = explode( '/page/', $_SERVER['REQUEST_URI'] );

	// default value
	$page = 1;

	// look for paging in this query
	if ( ! is_null( $wp_query ) && isset( $wp_query->query_vars['paged'] ) ) {
		$page = $wp_query->query_vars['paged'];
	}
	// try global query
	else if ( ! is_null( $wp_query ) && get_query_var( 'paged' ) ) {
		$page = get_query_var( 'paged' );
	}
	// paging with slashes
	else if ( isset( $path_array[1] ) ) {
		$page = explode( '/', $path_array[1] );
		$page = $page[0];
	}
	// paging with get variable
	else if ( isset( $_GET['page'] ) ) {
		$page = $_GET['page'];
	}
	// paging with a different get variable
	else if ( isset( $_GET['paged'] ) ) {
		$page = $_GET['paged'];
	}

	return $page;
}

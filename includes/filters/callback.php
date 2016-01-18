<?php

add_filter( 'qw_filters', 'qw_filter_callback' );

/**
 * Add the callback filter to QW's list of filters
 *
 * @param $filters
 *
 * @return mixed
 */
function qw_filter_callback( $filters ) {
	$filters['callback'] = array(
		'title'               => __( 'Callback' ),
		'description'         => __( 'Provide a callback that can alter the query arguments in any way.' ),
		'form_callback'       => 'qw_filter_callback_form',
		'query_args_callback' => 'qw_filter_callback_execute',
		'query_display_types' => array( 'page', 'widget', 'override' ),
	);

	return $filters;
}

/**
 * Form for callback filter
 *
 * @param $filter
 */
function qw_filter_callback_form( $filter ) {
	$form = new QW_Form_Fields( array(
			'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
			'type' => 'text',
			'name' => 'callback',
			'title' => __( 'Callback' ),
			'description' => __( 'The callback function will be provided the $args and $filter variables, and should return the modified $args array.' ),
			'help' => __( 'Example') . ' <code>function my_filter_callback($args, $filter){ return $args; }</code>',
			'value' => isset( $filter['values']['callback'] ) ? $filter['values']['callback'] : '',
			'class' => array( 'qw-js-title' ),
	) );
}

/**
 * Execute the callback filter
 *
 * @param $args
 * @param $filter
 */
function qw_filter_callback_execute( &$args, $filter ) {
	if ( isset( $filter['values']['callback'] ) && function_exists( $filter['values']['callback'] ) ) {
		$args = $filter['values']['callback']( $args, $filter );
	}
}
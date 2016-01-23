<?php
// hook into qw_basics
add_filter( 'qw_filters', 'qw_basic_settings_post_status' );

/*
 * Basic Settings
 */
function qw_basic_settings_post_status( $basics ) {

	$basics['post_status'] = array(
		'title'         => __( 'Posts Status' ),
		'description'   => __( 'Select the post status of the items displayed.' ),
		'query_args_callback' => 'qw_generate_query_args_post_status',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'required' => true,
		'form_fields' => array(
			'post_status' => array(
				'type' => 'checkboxes',
				'name' => 'post_status',
				'default_value' => array( 'publish' => 'publish' ),
				'options' => qw_all_post_statuses(),
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $basics;
}

function qw_generate_query_args_post_status( &$args, $filter ) {
	$args['post_status'] = $filter['values']['post_status'];
}

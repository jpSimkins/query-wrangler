<?php
// hook into qw_basics
add_filter( 'qw_filters', 'qw_basic_settings_post_status' );

// add default fields to the hook filter
add_filter( 'qw_post_statuses', 'qw_default_post_statuses', 0 );

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
				'options' => qw_all_post_statuses(),
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $basics;
}

/*
 * Post statuses as a hook for contributions
 */
function qw_default_post_statuses( $post_statuses ) {
	$post_statuses['publish'] = array(
		'title' => __( 'Published' ),
	);
	$post_statuses['pending'] = array(
		'title' => __( 'Pending' ),
	);
	$post_statuses['draft']   = array(
		'title' => __( 'Draft' ),
	);
	$post_statuses['future']  = array(
		'title' => __( 'Future (Scheduled)' ),
	);
	$post_statuses['trash']   = array(
		'title' => __( 'Trashed' ),
	);
	$post_statuses['private'] = array(
		'title' => __( 'Private' ),
	);
	$post_statuses['any'] = array(
		'title' => __( 'Any' ),
	);

	return $post_statuses;
}

function qw_generate_query_args_post_status( &$args, $filter ) {
	$args['post_status'] = $filter['values']['post_status'];
}

<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_post_status' );

// add default fields to the hook filter
add_filter( 'qw_post_statuses', 'qw_default_post_statuses', 0 );

/*
 * Basic Settings
 */
function qw_basic_settings_post_status( $basics ) {

	$basics['post_status'] = array(
		'title'         => __( 'Posts Status' ),
		'description'   => __( 'Select the post status of the items displayed.' ),
		'option_type'   => 'args',
		'form_callback' => 'qw_basic_post_status_form',
		'weight'        => 0,
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

/**
 * @param $item
 * @param $args
 */
function qw_basic_post_status_form( $item, $args ) {
	$post_statuses = array();
	foreach( qw_all_post_statuses() as $key => $details ) {
		$post_statuses[ $key ] = $details['title'];
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'post_status',
		'description' => $item['description'],
		'value' => isset( $args['post_status'] ) ? $args['post_status'] : '',
		'options' => $post_statuses,
		'class' => array( 'qw-js-title' ),
	) );
}

<?php

add_filter( 'qw_filters', 'qw_filter_post_status' );

/**
 * Post Status filter
 *
 * @param $filters
 *
 * @return array
 */
function qw_filter_post_status( $filters )
{
	$filters['post_status'] = array(
		'title'         => __( 'Posts Status' ),
		'description'   => __( 'Select the post status of the items displayed.' ),
		'query_args_callback' => 'qw_simple_filter_args_callback',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		'required' => true,
		'form_fields' => array(
			'post_status' => array(
				'type' => 'checkboxes',
				'name' => 'post_status',
				'default_value' => array(),
				'options' => qw_all_post_stati(),
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $filters;
}

/**
 * Get a simple list of WP post_status values and titles
 *
 * @return array
 */
function qw_all_post_stati()
{
	$post_stati = array(
		'any' => array(
			'title' => __( 'Any' ),
		)
	);

	foreach( get_post_stati( array(), 'objects' ) as $post_status )
	{
		$post_stati[ $post_status->name ]['title'] = $post_status->label;
	}

	$post_stati = apply_filters( 'qw_post_statuses', $post_stati );

	return $post_stati;
}

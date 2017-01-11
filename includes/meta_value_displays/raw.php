<?php

add_filter( 'qw_meta_value_display_handlers', 'qw_meta_value_display_handlers_default' );

/**
 * Default meta value handlers
 *
 * @param $displays
 *
 * @return array
 */
function qw_meta_value_display_handlers_default( $displays )
{
	$displays['none'] = array(
		'title'    => __('Raw'),
		'callback' => 'qw_get_post_meta',
	);

	return $displays;
}

/**
 * Simple get_post_meta wrapper
 *
 * @param $post
 * @param $field
 *
 * @return array
 */
function qw_get_post_meta( $post, $field )
{
	return get_post_meta( $post->ID, $field['meta_key'] );
}
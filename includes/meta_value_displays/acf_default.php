<?php

add_filter( 'qw_meta_value_display_handlers', 'qw_meta_value_display_handlers_acf_default' );

/**
 * Advanced custom fields meta value handlers
 *
 * http://wordpress.org/plugins/advanced-custom-fields/
 *
 * @param $displays
 *
 * @return array
 */
function qw_meta_value_display_handlers_acf_default( $displays )
{
	if ( function_exists( 'get_field' ) ) {
		$displays['acf_default'] = array(
			'title'    => 'Advanced Custom Fields: get_field',
			'callback' => 'qw_get_acf_field',
		);
	}

	return $displays;
}

/**
 * Advanced custom field generic handler
 *
 * @param $post
 * @param $field
 *
 * @return bool|mixed|string
 */
function qw_get_acf_field( $post, $field )
{
	$output = '';
	if ( function_exists( 'get_field' ) ) {
		$output = get_field( $field['meta_key'], $post->ID );
	}

	return $output;
}

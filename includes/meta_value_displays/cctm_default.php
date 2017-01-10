<?php

add_filter( 'qw_meta_value_display_handlers', 'qw_meta_value_display_handlers_cctm_default' );

/**
 * Custom Content Type Manager meta value handlers
 *
 * https://wordpress.org/plugins/custom-content-type-manager/
 *
 * @param $displays
 *
 * @return array
 */
function qw_meta_value_display_handlers_cctm_default( $displays )
{
	if ( function_exists( 'get_custom_field' ) ) {
		$displays['cctm_default'] = array(
			'title'    => 'CCTM: get_custom_field',
			'callback' => 'qw_get_cctm_field',
		);
	}

	return $displays;
}

/**
 * Custom Content Type Manager generic field handler
 *
 * @param $post
 * @param $field
 *
 * @return string
 */
function qw_get_cctm_field( $post, $field )
{
	$output = '';
	if ( function_exists( 'get_custom_field' ) ) {
		$field_name = $field['meta_key'];
		if ( isset( $field['cctm_chaining'] ) && ! empty( $field['cctm_chaining'] ) ) {
			$field_name = $field_name . $field['cctm_chaining'];
		}

		$output = get_custom_field( $field_name );
	}

	return $output;
}
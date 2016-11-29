<?php

/**
 * Simple template function for admin stuff
 *
 * @param $__template_name
 * @param array $__args
 *
 * @return string
 */
function qw_admin_template( $__template_name, $__args = array() ){
	$__template_file = QW_PLUGIN_DIR . "/admin/templates/{$__template_name}.php";

	if ( file_exists( $__template_file ) ){
		ob_start();
		extract( $__args );
		include $__template_file;
		return ob_get_clean();
	}

	return '';
}

/**
 * Helper to handle HTMl inside of json export
 *
 * @param $data
 *
 * @return mixed
 */
function qw_query_escape_export( $data ){
	if ( isset( $data['display']['field_settings']['fields'] ) ) {
		$fields = &$data['display']['field_settings']['fields'];

		foreach( $fields as $field_name => $field ) {
			$fields[ $field_name ]['custom_output'] = htmlspecialchars( $field['custom_output'], ENT_QUOTES, 'UTF-8', false );
			$fields[ $field_name ]['empty_field_content'] = htmlspecialchars( $field['empty_field_content'], ENT_QUOTES, 'UTF-8', false );
		}
	}

	return $data;
}

/**
 * Helper to handle HTMl inside of json import
 *
 * @param $data
 *
 * @return mixed
 */
function qw_query_decode_import( $data ){
	if ( isset( $data['display']['field_settings']['fields'] ) ) {
		$fields = &$data['display']['field_settings']['fields'];

		foreach( $fields as $field_name => $field ) {
			$fields[ $field_name ]['custom_output'] = htmlspecialchars_decode( $field['custom_output'] );
			$fields[ $field_name ]['empty_field_content'] = htmlspecialchars_decode( $field['empty_field_content'] );
		}
	}

	return $data;
}


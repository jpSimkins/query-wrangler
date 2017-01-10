<?php

/**
 * Trim each item in an array w/ array_walk
 *   eg: array_walk($fruit, 'qw_trim');
 *
 * @param mixed
 */
function qw_trim( &$value ) {
	$value = trim( $value );
}

/**
 * Serialize wrapper functions for future changes.
 *
 * @param $array
 *
 * @return string
 */
function qw_serialize( $array ) {
	return serialize( $array );
}

/**
 * Custom: Fix unserialize problem with quotation marks
 *
 * @param $serial_str
 *
 * @return array
 */
function qw_unserialize( $serial_str ) {
	$data = maybe_unserialize( $serial_str );

	// if the string failed to unserialize, we may have a quotation problem
	if ( !is_array( $data ) ) {
		$serial_str = @preg_replace( '!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str );
		$data = maybe_unserialize( $serial_str );
	}

	if ( is_array( $data ) ) {
		// stripslashes twice for science
		$data = array_map( 'stripslashes_deep', $data );
		$data = array_map( 'stripslashes_deep', $data );

		return $data;
	}

	// if we're here the data wasn't unserialized properly.
	// return a modified version of the default query to prevent major failures.
	$default = qw_default_query_data();
	$default['display']['title'] = 'error unserializing query data';
	$default['args']['filters']['posts_per_page']['posts_per_page'] = 1;

	return $default;
}

/**
 * usort callback - sort by 'weight' key in array
 *
 * @param $a
 * @param $b
 *
 * @return int
 */
function qw_cmp( $a, $b ) {
	if ( $a['weight'] == $b['weight'] ) {
		return 0;
	}

	return ( $a['weight'] < $b['weight'] ) ? - 1 : 1;
}

/**
 * Simple helper functions for very common task of recording an item's original
 * unique index.
 *
 * @param $items
 *
 * @return mixed
 */
function qw_set_hook_keys( $items )
{
	foreach( $items as $hook_key => $item ){
		$items[ $hook_key ]['hook_key'] = $hook_key;
	}
	return $items;
}

/**
 * Support function for legacy, pre hook_keys discovery
 *
 * @param $all
 * @param $single
 *
 * @return int|string
 */
function qw_get_hook_key( $all, $single ) {
	// default to new custom_field (meta_value_new)
	$hook_key = '';

	// see if hook key is set
	if ( ! empty( $single['hook_key'] ) && isset( $all[ $single['hook_key'] ] ) ) {
		$hook_key = $single['hook_key'];
	} // look for type as key
	else if ( ! empty( $single['type'] ) ) {
		foreach ( $all as $key => $item ) {
			if ( $single['type'] == $item['type'] ) {
				$hook_key = $item['hook_key'];
				break;
			} else if ( $single['type'] == $key ) {
				$hook_key = $key;
				break;
			}
		}
	}

	return $hook_key;
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


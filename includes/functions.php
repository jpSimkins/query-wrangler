<?php

/**
 * temp fix
 *
 * @param $options
 *
 * @return mixed|void
 */
function qw_get_query_handlers( $options ){
	$handlers = QW_Handler_Manager::get_instance();

	return $handlers->get_query_handlers( $options );
}

/**
 * All Handlers
 *
 * Handlers are groups of items that can be added and removed from a query
 * eg: filters, sorts, fields
 *
 * @return array
 */
function qw_all_handlers()
{
	$handlers = apply_filters( 'qw_handlers', array() );
	$handlers = qw_pre_process_handler_types( $handlers );

	return $handlers;
}

function qw_pre_process_handler_types( $handlers )
{
	foreach ( $handlers as $hook_key => $handler ) {
		$handler['hook_key'] = $hook_key;
		$handler['form_prefix'] = QW_FORM_PREFIX . "[{$hook_key}]" ;
		$handler['all_items'] = call_user_func( $handler['all_callback'], $handler );

		$handlers[ $hook_key ] = $handler;
	}

	return $handlers;
}

/**
 * Simple helper functions for very common task of recording an item's original
 * unique index.
 *
 * @param $item_types
 * @param $handler
 *
 * @return mixed
 */
function qw_pre_process_handler_item_types( $item_types, $handler )
{
	foreach( $item_types as $hook_key => $item ){
		$item['hook_key'] = $hook_key;
		$item['form_prefix'] = "{$handler['form_prefix']}[{$hook_key}]";

		$item_types[ $hook_key ] = $item;
	}
	return $item_types;
}

/**
 * Simple helper functions for very common task of recording an item's original
 * unique index.
 *
 * @param $settings
 * @param $handler_item_type
 *
 * @return mixed
 */
function qw_pre_process_handler_item_type_settings( $settings, $handler_item_type )
{
	foreach( $settings as $hook_key => $item ){
		$item['hook_key'] = $hook_key;
		$item['form_prefix'] = "{$handler_item_type['form_prefix']}[{$hook_key}]";

		$settings[ $hook_key ] = $item;
	}

	return $settings;
}

/**
 * @param $items
 *
 * @return mixed
 */
function qw_set_hook_keys( $items ){
	foreach ($items as $hook_key => $item ){
		$items[ $hook_key ]['hook_key'] = $hook_key;
	}

	return $items;
}

/**
 * @param $items
 *
 * @return mixed
 */
function qw_set_hook_types( $items )
{
	foreach( $items as $hook_key => $item )
	{
		if ( ! isset( $item['type'] ) ) {
			$items[ $hook_key ]['type'] = $hook_key;
		}
	}
	return $items;
}

/**
 * Return Default Template File
 *
 * @return string
 */
function qw_default_template_file() {
	return apply_filters( 'qw_default_template_file', 'index.php' );
}

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

function qw_array_by_key( $items, $key )
{

	// sort them by title
	$titles = array();
	foreach ( $items as $i => $item ) {
		$titles[ $i ] = $item['title'];
	}

	array_multisort( $titles, SORT_ASC, $items );
}

/**
 * Replace contextual tokens within a string
 *
 * @param string $args - a query argument string
 *
 * @return string - query argument string with tokens replaced with values
 */
function qw_contextual_tokens_replace( $args ) {
	$matches = array();
	preg_match_all( '/{{([^}]*)}}/', $args, $matches );

	if ( isset( $matches[1] ) )
	{
		global $post;

		foreach ( $matches[1] as $i => $context_token )
		{
			if ( stripos( $context_token, ':' ) !== FALSE )
			{
				$a = explode( ':', $context_token );
				if ( $a[0] == 'post' && isset( $post->{$a[1]} ) )
				{
					$args = str_replace( $matches[0][ $i ], $post->{$a[1]}, $args );
				}
				else if ( $a[0] == 'query_var' && $replace = get_query_var( $a[1] ) ) {
					$args = str_replace( $matches[0][ $i ], $replace, $args );
				}
			}
		}
	}

	return $args;
}

/**
 * Support function for legacy, pre hook_keys discovery
 *
 * @param $all
 * @param $single
 *
 * @return int|string
 */
function qw_get_hook_key( $all, $single )
{
	$hook_key = '';

	// see if hook key is set
	if ( ! empty( $single['hook_key'] ) && isset( $all[ $single['hook_key'] ] ) ) {
		$hook_key = $single['hook_key'];
	} // look for type as key
	else if ( ! empty( $single['type'] ) )
	{
		foreach ( $all as $key => $item )
		{
			if ( $single['type'] == $item['type'] ) {
				$hook_key = $item['hook_key'];
				break;
			}
			else if ( $single['type'] == $key ) {
				$hook_key = $key;
				break;
			}
		}
	}

	return $hook_key;
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


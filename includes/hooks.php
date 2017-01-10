<?php

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
	$handlers = qw_set_hook_keys( $handlers );

	foreach ( $handlers as $hook_key => $handler ) {
		$handlers[ $hook_key ]['all_items'] = call_user_func( $handler['all_callback'] );
	}

	return $handlers;
}

/**
 * Get all "Basic" types registered w/ QW
 *
 * @return array
 */
function qw_all_basic_settings()
{
	$basics = apply_filters( 'qw_basics', array() );
	$basics = qw_set_hook_keys( $basics );

	uasort( $basics, 'qw_cmp' );

	return $basics;
}

/**
 * Get all "Field" handler item types
 *
 * @return array
 */
function qw_all_fields()
{
	$fields = apply_filters( 'qw_fields', array() );
	$fields = qw_set_hook_keys( $fields );

	foreach ( $fields as $type => $field ) {
		if ( ! isset( $field['type'] ) ) {
			$fields[ $type ]['type'] = $type;
		}
	}

	// sort them by title
	$titles = array();
	foreach ( $fields as $key => $field ) {
		$titles[ $key ] = $field['title'];
	}
	array_multisort( $titles, SORT_ASC, $fields );

	return $fields;
}

/**
 * Get all "Filter" handler item types
 *
 * @return array
 */
function qw_all_filters()
{
	$filters = apply_filters( 'qw_filters', array() );
	$filters = qw_set_hook_keys( $filters );

	foreach ( $filters as $type => $filter ) {
		// set filter's type as a value if not provided by filter
		if ( ! isset( $filter['type'] ) ) {
			$filters[ $type ]['type'] = $type;
		}
	}

	// sort them by title
	$titles = array();
	foreach ( $filters as $key => $filter ) {
		$titles[ $key ] = $filter['title'];
	}
	array_multisort( $titles, SORT_ASC, $filters );

	return $filters;
}

/**
 * Get all "Override" handler item types
 *
 * @return array
 */
function qw_all_overrides()
{
	$overrides = apply_filters( 'qw_overrides', array() );
	$overrides = qw_set_hook_keys( $overrides );

	foreach ( $overrides as $type => $override ) {
		// set override's type as a value if not provided by override
		if ( empty( $override['type'] ) ) {
			$overrides[ $type ]['type'] = $type;
		}
	}

	// sort them by title
	$titles = array();
	foreach ( $overrides as $key => $override ) {
		$titles[ $key ] = $override['title'];
	}
	array_multisort( $titles, SORT_ASC, $overrides );

	return $overrides;
}

/**
 * Get all "Sort" (Order) handler item types
 *
 * @return array
 */
function qw_all_sort_options()
{
	$sort_options = apply_filters( 'qw_sort_options', array() );
	$sort_options = qw_set_hook_keys( $sort_options );

	// set some defaults for very simple hooks
	foreach ( $sort_options as $type => $option ) {
		if ( ! isset( $option['type'] ) ) {
			$sort_options[ $type ]['type'] = $type;
		}
	}

	// sort them by title
	$titles = array();
	foreach ( $sort_options as $key => $sort ) {
		$titles[ $key ] = $sort['title'];
	}
	array_multisort( $titles, SORT_ASC, $sort_options );

	return $sort_options;
}

/**
 * Get all Template Style options for the Basic "Style" handler item type
 *
 * return array
 */
function qw_all_styles()
{
	$styles = apply_filters( 'qw_styles', array() );
	$styles = qw_set_hook_keys( $styles );

//	foreach ( $styles as $hook_key => $style ) {
//		$styles[ $hook_key ]['form_prefix'] = QW_FORM_PREFIX . "[display][style_settings][{$style['settings_key']}]";
//	}

	return $styles;
}

/**
 * Get all Row Style options for the Basic "Row Styles" handler item type
 *
 * @return array
 */
function qw_all_row_styles()
{
	$row_styles = apply_filters( 'qw_row_styles', array() );
	$row_styles = qw_set_hook_keys( $row_styles );

	return $row_styles;
}

/**
 * Get all Pager options for the Basic "Pager" handler item type
 *
 * @return array
 */
function qw_all_pager_types()
{
	$pagers = apply_filters( 'qw_pager_types', array() );
	$pagers = qw_set_hook_keys( $pagers );

	foreach( $pagers as $hook_key => $pager ){
		if ( !empty( $pager['settings_key'] ) ){
			$pagers[ $hook_key ]['form_prefix'] = QW_FORM_PREFIX . "[display][pager][{$pager['settings_key']}]";
		}
	}

	return $pagers;
}

/**
 * Meta value field display handlers
 *
 * @return array
 */
function qw_get_meta_value_display_handlers()
{
	$displays = apply_filters( 'qw_meta_value_display_handlers', array() );
	$displays = qw_set_hook_keys( $displays );

	return $displays;
}

/**
 * List of all public Post Types registered in WordPress
 *
 * @return array
 */
function qw_all_post_types()
{
	// Get all verified post types
	$post_types = get_post_types( array(
		'public'   => TRUE,
		'_builtin' => FALSE
	),
	'names',
	'and' );

	// Add standard types
	$post_types['post'] = 'post';
	$post_types['page'] = 'page';

	$post_types = apply_filters( 'qw_post_types', $post_types );

	// sort types
	ksort( $post_types );

	return $post_types;
}

/**
 * Get a simple list of WP post_status values and titles
 *
 * @return array
 */
function qw_all_post_statuses() {

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

/**
 * Simple list of all File Styles used by certain "Field" handler item types
 *
 * @return array
 */
function qw_all_file_styles()
{
	$default = array(
		'link' => array(
			'title' => __( 'Filename Link to File' ),
		),
		'link_url' => array(
			'title' => __( 'URL Link to File' ),
		),
		'url' => array(
			'title' => __( 'URL of File' ),
		),
	);

	$styles = apply_filters( 'qw_file_styles', $default );

	return $styles;
}

/**
 * Return Default Template File
 *
 * @return string
 */
function qw_default_template_file() {
	return apply_filters( 'qw_default_template_file', 'index.php' );
}



<?php
/*
 * All Handlers
 *
 * Handlers are groups of items that can be added and removed from a query
 * eg: filters, sorts, fields
 */
function qw_all_handlers() {
	$handlers = apply_filters( 'qw_handlers', array() );
	foreach ( $handlers as $hook_key => $handler ) {
		$handlers[ $hook_key ]['hook_key']  = $hook_key;
		$handlers[ $hook_key ]['all_items'] = call_user_func( $handler['all_callback'] );
	}

	return $handlers;
}

/*
 * Basic Settings
 */
function qw_all_basic_settings() {
	$basics = apply_filters( 'qw_basics', array() );

	foreach ( $basics as $hook_key => $basic ) {
		$basics[ $hook_key ]['form_prefix'] = QW_FORM_PREFIX . '[display]';
		$basics[ $hook_key ]['hook_key']    = $hook_key;
	}

	uasort( $basics, 'qw_cmp' );

	return $basics;
}

/*
 * Fields Hook
 */
function qw_all_fields() {
	$fields = apply_filters( 'qw_fields', array() );
	foreach ( $fields as $type => $field ) {
		if ( ! isset( $field['type'] ) ) {
			$fields[ $type ]['type'] = $type;
		}
		// maintain the hook's key
		$fields[ $type ]['hook_key'] = $type;
	}

	// sort them by title
	$titles = array();
	foreach ( $fields as $key => $field ) {
		$titles[ $key ] = $field['title'];
	}
	array_multisort( $titles, SORT_ASC, $fields );

	return $fields;
}

/*
 * filters Hook
 */
function qw_all_filters() {
	$filters = apply_filters( 'qw_filters', array() );

	foreach ( $filters as $type => $filter ) {
		// set filter's type as a value if not provided by filter
		if ( ! isset( $filter['type'] ) ) {
			$filters[ $type ]['type'] = $type;
		}
		// maintain the hook's key
		$filters[ $type ]['hook_key'] = $type;
	}

	// sort them by title
	$titles = array();
	foreach ( $filters as $key => $filter ) {
		$titles[ $key ] = $filter['title'];
	}
	array_multisort( $titles, SORT_ASC, $filters );

	return $filters;
}

/*
 * overrides Hook
 */
function qw_all_overrides() {
	$overrides = apply_filters( 'qw_overrides', array() );

	foreach ( $overrides as $type => $override ) {
		// set override's type as a value if not provided by override
		if ( empty( $override['type'] ) ) {
			$overrides[ $type ]['type'] = $type;
		}

		// maintain the hook's key
		$overrides[ $type ]['hook_key'] = $type;
	}

	// sort them by title
	$titles = array();
	foreach ( $overrides as $key => $override ) {
		$titles[ $key ] = $override['title'];
	}
	array_multisort( $titles, SORT_ASC, $overrides );

	return $overrides;
}

/*
 * Sort Options Hook
 */
function qw_all_sort_options() {
	$sort_options = apply_filters( 'qw_sort_options', array() );

	// set some defaults for very simple hooks
	foreach ( $sort_options as $type => $option ) {
		if ( ! isset( $option['type'] ) ) {
			$sort_options[ $type ]['type'] = $type;
		}

		// maintain hook's key
		$sort_options[ $type ]['hook_key'] = $type;
	}

	// sort them by title
	$titles = array();
	foreach ( $sort_options as $key => $sort ) {
		$titles[ $key ] = $sort['title'];
	}
	array_multisort( $titles, SORT_ASC, $sort_options );

	return $sort_options;
}

/*
 * Post Statuses
 */
function qw_all_post_statuses() {
	$default = array(
		'publish' => array(
			'title' => __( 'Published' ),
		) ,
		'pending' => array(
			'title' => __( 'Pending' ),
		) ,
		'draft' => array(
			'title' => __( 'Draft' ),
		) ,
		'future' => array(
			'title' => __( 'Future (Scheduled)' ),
		) ,
		'trash' => array(
			'title' => __( 'Trashed' ),
		) ,
		'private' => array(
			'title' => __( 'Private' ),
		) ,
		'any' => array(
			'title' => __( 'Any' ),
		) ,
	);

	$post_statuses = apply_filters( 'qw_post_statuses', $default );

	return $post_statuses;
}

/**
 * Styles Hook
 *
 * return array
 */
function qw_all_styles() {
	$styles = apply_filters( 'qw_styles', array() );

	foreach ( $styles as $hook_key => $style ) {
		$styles[ $hook_key ]['hook_key']    = $hook_key;
		$styles[ $hook_key ]['form_prefix'] = QW_FORM_PREFIX . "[display][style_settings][{$hook_key}]";

		if ( ! isset( $style['settings_key'] ) ) {
			$styles[ $hook_key ]['settings_key'] = $hook_key . '_settings';
		}
	}

	return $styles;
}

/**
 * Row Styles Hook
 *
 * @return array
 */
function qw_all_row_styles() {
	$row_styles = apply_filters( 'qw_row_styles', array() );
	foreach ( $row_styles as $k => $row_style ) {
		$row_styles[ $k ]['hook_key'] = $k;
	}

	return $row_styles;
}

/**
 * Pager types
 */
function qw_all_pager_types() {
	$pagers = apply_filters( 'qw_pager_types', array() );

	foreach( $pagers as $hook_key => $pager ){
		$pagers[ $hook_key ]['hook_key'] = $hook_key;
	}

	return $pagers;
}

/*
 * File Styles Hook
 */
function qw_all_file_styles() {

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

/*
 * Post types
 */
function qw_all_post_types() {
	$post_types = apply_filters( 'qw_post_types', array() );

	// Get all verified post types
	$post_types += get_post_types( array(
		'public'   => TRUE,
		'_builtin' => FALSE
	),
		'names',
		'and' );
	// Add standard types
	$post_types['post'] = 'post';
	$post_types['page'] = 'page';
	// sort types
	ksort( $post_types );

	return $post_types;
}

/*
 * Return Default Template File
 */
function qw_default_template_file() {
	return apply_filters( 'qw_default_template_file', 'index.php' );
}


/**
 * default custom_field (meta_value_new) field display handlers
 */

function qw_get_meta_value_display_handlers(){
	return apply_filters( 'qw_meta_value_display_handlers', array() );
}

// add default meta value handlers
add_filter( 'qw_meta_value_display_handlers', 'qw_meta_value_display_handlers_default' );

/*
 * Default meta value handlers
 */
function qw_meta_value_display_handlers_default( $handlers ) {
	$handlers['none'] = array(
		'title'    => '-none-',
		'callback' => 'qw_get_post_meta',
	);
	// advanced custom fields: http://wordpress.org/plugins/advanced-custom-fields/
	if ( function_exists( 'get_field' ) ) {
		$handlers['acf_default'] = array(
			'title'    => 'Advanced Custom Fields: get_field',
			'callback' => 'qw_get_acf_field',
		);
	}
	// cctm: https://wordpress.org/plugins/custom-content-type-manager/
	if ( function_exists( 'get_custom_field' ) ) {
		$handlers['cctm_default'] = array(
			'title'    => 'CCTM: get_custom_field',
			'callback' => 'qw_get_cctm_field',
		);
	}

	return $handlers;
}

/*
 * return simple get_post_meta array
 */
function qw_get_post_meta( $post, $field ) {
	return get_post_meta( $post->ID, $field['meta_key'] );
}

/*
 * Advanced custom field generic handler
 */
function qw_get_acf_field( $post, $field ) {
	$output = '';
	if ( function_exists( 'get_field' ) ) {
		$output = get_field( $field['meta_key'], $post->ID );
	}

	return $output;
}

/*
 * Custom Content Type Manager generic field handler
 */
function qw_get_cctm_field( $post, $field ) {
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
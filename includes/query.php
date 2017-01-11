<?php

/**
 * Primary function for building and displaying a query
 *
 * @param int $query_id Id for the query
 * @param array $options_override an array for changing or adding query data options
 * @param bool $reset_post_data Reset the $wp_query after execution
 *
 * @return string Can return a string of html based on parameter $return
 */
function qw_execute_query( $query_id, $options_override = array(), $reset_post_data = TRUE )
{
	$qw_query = qw_get_query( $query_id );
	$qw_query
		->override_options( $options_override, false )
		->execute( $reset_post_data );

	return $qw_query->output;
}

/**
 * Get an existing query as QW_Query object
 *
 * @param $id
 *
 * @return null|QW_Query
 */
function qw_get_query( $id ) {
	if ( ! empty( $id ) ) {
		$query = new QW_Query( $id );

		if ( $query && is_a( $query, 'QW_Query' ) && ! $query->is_new ) {
			return $query;
		}
	}

	return NULL;
}

/**
 * Create a new empty QW_Query
 *
 * @return QW_Query object
 */
function qw_create_query() {
	return new QW_Query();
}


/**
 * Default values for  new query
 *
 * @return array Default query settings
 */
function qw_default_query_data() {
	return
		array (
			'display' => array (
				'title' => '',
				'style' => 'unformatted',
				'row_style' => 'posts',
				'post_settings' => array (
					'size' => 'complete',
				),
				'field_settings' => array (
					'group_by_field' => '__none__',
				),
				'template_part_settings' => array (
					'path' => '',
					'name' => '',
				),
				'header' => '',
				'footer' => '',
				'empty' => '',
				'wrapper-classes' => '',
				'page' => array (
					'pager' => array (
						'active' => '0',
						'type' => 'default',
					),
				),
			),
			'args' => array (
				'sorts' => array (
					'date' => array (
						'weight' => '0',
						'type' => 'date',
						'hook_key' => 'post_date',
						'name' => 'date',
						'order_value' => 'DESC',
					),
				),
				'filters' => array (
					'posts_per_page' => array (
						'weight' => '0',
						'type' => 'posts_per_page',
						'hook_key' => 'posts_per_page',
						'name' => 'posts_per_page',
						'posts_per_page' => '5',
					),
					'post_status' => array (
						'weight' => '1',
						'type' => 'post_status',
						'hook_key' => 'post_status',
						'name' => 'post_status',
						'post_status' => array( 'publish' => 'publish' ),
					),
					'post_types' => array (
						'weight' => '2',
						'type' => 'post_types',
						'hook_key' => 'post_types',
						'name' => 'post_types',
						'post_types' => array (
							'post' => 'post',
						),
					),
					'ignore_sticky_posts' => array (
						'weight' => '3',
						'type' => 'ignore_sticky_posts',
						'hook_key' => 'ignore_sticky_posts',
						'name' => 'ignore_sticky_posts',
						'ignore_sticky_posts' => 'on',
					),
				),
			),
		);
}

/**
 * Get a query's id by using its slug
 *
 * @param $slug
 *
 * @return null|string
 */
function qw_get_query_by_slug( $slug ) {
	global $wpdb;

	$sql = $wpdb->prepare( "SELECT `id` FROM {$wpdb->prefix}query_wrangler WHERE `slug` = '%s'", $slug );
	return $wpdb->get_var( $sql );
}

/**
 * Get an unserialized query row from the database, using the query's id
 *
 * @param $id
 *
 * @return bool|mixed
 */
function qw_get_query_by_id( $id ) {
	global $wpdb;
	$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}query_wrangler WHERE `id` = %d LIMIT 1", $id );
	$query = $wpdb->get_row( $sql );

	if ( $query ) {
		$query->data = qw_unserialize( $query->data );

		return $query;
	}

	return FALSE;
}

/**
 * Get a query's id by that is set to override a specific term_id
 *
 * @param $term_id
 *
 * @return bool
 */
function qw_get_query_by_override_term( $term_id ) {

	global $wpdb;

	$sql = $wpdb->prepare(
		"SELECT qw.id FROM {$wpdb->prefix}query_wrangler as qw
         LEFT JOIN {$wpdb->prefix}query_override_terms as ot ON ot.query_id = qw.id
         WHERE qw.type = 'override' AND ot.term_id = %d
         LIMIT 1",
		$term_id );

	$row = $wpdb->get_row( $sql );

	if ( $row ) {
		return $row->id;
	}

	return FALSE;
}

/**
 * Get all queries of the type widget
 *
 * @return array of query widgets with key as query id
 */
function qw_get_all_widgets() {
	global $wpdb;
	$rows = $wpdb->get_results( "SELECT `id`,`name` FROM {$wpdb->prefix}query_wrangler WHERE `type` = 'widget'" );

	if ( is_array( $rows ) ) {
		$widgets = array();
		foreach ( $rows as $row ) {
			$widgets[ $row->id ] = $row->name;
		}

		return $widgets;
	}
}

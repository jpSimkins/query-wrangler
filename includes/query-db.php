<?php

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

<?php

add_filter( 'qw_filters', 'qw_filter_taxonomy_relation' );

/*
 * Add filter to qw_filters
 */
function qw_filter_taxonomy_relation( $filters ) {
	$filters['taxonomy_relation'] = array(
		'title'               => __( 'Taxonomy Relation' ),
		'description'         => __( 'Define how multiple taxonomy filters interact with each other.' ),
		'query_args_callback' => 'qw_filter_taxonomy_relation_args',
		'query_display_types' => array( 'page', 'widget' ),
		'form_fields' => array(
			'taxonomy_relation' => array(
				'type' => 'select',
				'name' => 'taxonomy_relation',
				'description' => __( 'How do multiple taxonomy filters relate to each other?' ),
				'help' => __( 'AND requires posts to contain at least one term from each taxonomy filter. OR allows posts to contain any terms from all of the taxonomy filters.' ),
				'options' => array(
					'AND' => __( 'AND' ),
					'OR' => __( 'OR' ),
				),
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $filters;
}

/**
 * Convert values into query args
 *
 * @param $args
 * @param $filter
 */
function qw_filter_taxonomy_relation_args( &$args, $filter ) {
	if ( isset( $filter['values']['taxonomy_relation'] ) ) {
		$args['tax_query']['relation'] = $filter['values']['taxonomy_relation'];
	}
}

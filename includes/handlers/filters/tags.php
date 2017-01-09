<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_tags' );

function qw_filter_tags( $filters ) {

	$filters['tags'] = array(
		'title'               => 'Tags',
		'description'         => 'Select which tags to use.',
		'query_args_callback' => 'qw_generate_query_args_tags',
		'query_display_types' => array( 'page', 'widget' ),
		'form_fields' => array(
			'tag_operator' => array(
				'type' => 'select',
				'name' => 'tag_operator',
				'title' => __( 'Tags operator' ),
				'description' => __( 'Determines how the selected tags are queried.' ),
				'default_value' => 'tag__in',
				'options' => array(
					'tag__in'     => __( 'Any of the selected tags' ),
					'tag__and'    => __( 'All of the selected tags' ),
					'tag__not_in' => __( 'None of the selected tags' ),
				),
				'class' => array( 'qw-js-title' ),
			),
			'tags' => array(
				'type' => 'checkboxes',
				'name' => 'tags',
				'title' => __( 'Tags' ),
				'default_value' => array(),
				'options' => get_terms( 'post_tag', array( 'fields' => 'id=>name', 'hide_empty' => 0 ) ),
				'class' => array( 'qw-js-title' ),
			)
		),
	);

	return $filters;
}


function qw_generate_query_args_tags( &$args, $filter ) {
	if ( isset( $filter['values']['tags'] ) && is_array( $filter['values']['tags'] ) ) {
		$args[ $filter['values']['tag_operator'] ] = array_keys( $filter['values']['tags'] );
	}
}

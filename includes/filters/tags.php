<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_tags' );

function qw_filter_tags( $filters ) {

	$filters['tags'] = array(
		'title'               => 'Tags',
		'description'         => 'Select which tags to use.',
		'form_callback'       => 'qw_filter_tags_form',
		'query_args_callback' => 'qw_generate_query_args_tags',
		'query_display_types' => array( 'page', 'widget' ),
	);

	return $filters;
}

function qw_filter_tags_form( $filter ) {
	$form = new QW_Form_Fields( array(
			'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
			'type' => 'select',
			'name' => 'tag_operator',
			'title' => __( 'Tags operator' ),
			'description' => __( 'Determines how the selected tags are queried.' ),
			'value' => isset( $filter['values']['tag_operator'] ) ? $filter['values']['tag_operator'] : '',
			'options' => array(
				"tag__in"     => "Any of the selected tags",
				"tag__and"    => "All of the selected tags",
				"tag__not_in" => "None of the selected tags",
			),
			'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
			'type' => 'checkboxes',
			'name' => 'tags',
			'title' => __( 'Tags' ),
			'value' => isset( $filter['values']['tags'] ) ? $filter['values']['tags'] : array(),
			'options' => get_terms( 'post_tag', array( 'fields' => 'id=>name', 'hide_empty' => 0 ) ),
			'class' => array( 'qw-js-title' ),
	) );
}

function qw_generate_query_args_tags( &$args, $filter ) {
	if ( isset( $filter['values']['tags'] ) && is_array( $filter['values']['tags'] ) ) {
		$args[ $filter['values']['tag_operator'] ] = array_keys( $filter['values']['tags'] );
	}
}

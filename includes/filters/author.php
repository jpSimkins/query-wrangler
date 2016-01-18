<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_author' );

function qw_filter_author( $filters ) {

	$filters['author'] = array(
		'title'               => 'Author',
		'description'         => 'Filter posts by author',
		'form_callback'       => 'qw_filter_author_form',
		'query_args_callback' => 'qw_generate_query_args_author',
		'query_display_types' => array( 'page', 'widget' ),
	);

	return $filters;
}

/**
 * @param $filter
 */
function qw_filter_author_form( $filter ) {
	$author_operators = array(
		"author"         => __( "Author IDs" ),
		"author_name"    => __( "Author nice name" ),
		"author__in"     => __( "Authors in list of IDs" ),
		"author__not_in" => __( "Authors Not in list of author IDs" ),
	);

	$form = new QW_Form_Fields( array(
            'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
	    'type' => 'select',
	    'name' => 'author_operator',
	    'title' => __( 'Author Options' ),
	    'description' => __( 'Show posts that are from the listed authors' ),
	    'value' => isset( $filter['values']['author_operator'] ) ? $filter['values']['author_operator'] : '',
	    'options' => $author_operators,
	    'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
	    'type' => 'text',
	    'name' => 'author_values',
	    'title' => __( 'Values' ),
	    'description' => __( 'Provide the values appropriate for the author option.  IDs should be comma separated.' ),
	    'value' => isset( $filter['values']['author_values'] ) ? $filter['values']['author_values'] : '',
	    'class' => array( 'qw-js-title' ),
	) );
}


function qw_generate_query_args_author( &$args, $filter ) {
	if ( ! isset( $filter['values']['author_operator'] ) ) {
		$filter['values']['author_operator'] = '';
	}
	if ( ! isset( $filter['values']['author_values'] ) ) {
		$filter['values']['author_values'] = '';
	}

	$op = $filter['values']['author_operator'];
	if ( $op == "author" || $op == "author_name" ) {
		$args[ $op ] = $filter['values']['author_values'];
	} else {
		// turn values into array
		$args[ $op ] = array_map( 'trim',
			explode( ",", $filter['values']['author_values'] ) );
	}
}
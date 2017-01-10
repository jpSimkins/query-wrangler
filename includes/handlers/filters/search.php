<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_search' );

/**
 * @param $filters
 *
 * @return mixed
 */
function qw_filter_search( $filters ) {

	$filters['search'] = array(
		'title'               => __( 'Search' ),
		'description'         => __( 'Searches for keywords' ),
		'query_args_callback' => 'qw_dynamic_filter_args_callback',
		'query_args_process' => array(
			's' => array(
				'values_key' => 'search',
				'process_callbacks' => array( 'stripslashes' ),
			)
		),
		'query_display_types' => array( 'page', 'widget' ),
		'exposed_form'        => 'qw_filter_search_exposed_form',
		'exposed_process'     => 'qw_filter_search_exposed_process',
		'form_fields' => array(
			'search' =>  array(
				'type' => 'text',
				'name' => 'search',
				'class' => array( 'qw-js-title' ),
			)
		),
	);

	return $filters;
}

/**
 * Process submitted exposed form values
 *
 * @param $args
 * @param $filter
 * @param $values
 */
function qw_filter_search_exposed_process( &$args, $filter, $values ) {
	// default values if submitted is empty
	qw_filter_search_exposed_default_values( $filter, $values );

	// check allowed values
	if ( isset( $filter['values']['exposed_limit_values'] ) ) {
		if ( $values == $filter['values']['search'] ) {
			$args['s'] = $values;
		}
	} else {
		$args['s'] = $values;
	}
}

/**
 * Exposed form
 *
 * @param $filter
 * @param $values
 */
function qw_filter_search_exposed_form( $filter, $values ) {
	// default values
	qw_filter_search_exposed_default_values( $filter, $values );
	?>
	<input type="text"
	       name="<?php print $filter['exposed_key']; ?>"
	       value="<?php print esc_attr( $values ); ?>"/>
	<?php
}

/**
 * Simple helper function to handle default values
 *
 * @param $filter
 * @param $values
 */
function qw_filter_search_exposed_default_values( $filter, &$values ) {
	if ( isset( $filter['values']['exposed_default_values'] ) ) {
		if ( is_null( $values ) ) {
			$values = $filter['values']['search'];
		}
	}
}

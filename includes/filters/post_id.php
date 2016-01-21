<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_post_id' );

/**
 * @param $filters
 * @return mixed
 */
function qw_filter_post_id( $filters ) {
	$filters['post_id'] = array(
		'title'               => __( 'Post IDs' ),
		'description'         => __( 'Provide a list of post_ids to show or not show.' ),
		'form_callback'       => 'qw_filter_post_id_form',
		'query_args_callback' => 'qw_generate_query_args_post_id',
		'query_display_types' => array( 'page', 'widget', 'override' ),
		// exposed
		'exposed_form'        => 'qw_filter_post_id_exposed_form',
		'exposed_process'     => 'qw_filter_post_id_exposed_process',
	);

	return $filters;
}

/**
 * @param $filter
 */
function qw_filter_post_id_form( $filter ) {
	$form = new QW_Form_Fields( array(
	    'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
	    'type' => 'text',
	    'name' => 'post_ids',
	    'title' => __( 'Provide post_ids as a comma separated list' ),
	    'value' => isset( $filter['values']['post_ids'] ) ? $filter['values']['post_ids'] : '',
	    'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
	    'type' => 'text',
	    'name' => 'post_ids_callback',
	    'title' => __( 'Or, provide a callback function name that returns an array of post_ids' ),
	    'description' => __( 'Note: you cannot expose a filter if using a callback.' ),
	    'value' => isset( $filter['values']['post_ids_callback'] ) ? $filter['values']['post_ids_callback'] : '',
	    'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
	    'type' => 'select',
	    'name' => 'compare',
	    'title' => __( 'Compare' ),
	    'description' => __( 'How to treat these post IDs.' ),
	    'value' => isset( $filter['values']['compare'] ) ? $filter['values']['compare'] : '',
	    'options' => array(
	        'post__in' => __( 'Only these posts' ),
	        'post__not_in' => __( 'Not these posts' ),
	    ),
	    'class' => array( 'qw-js-title' ),
	) );
}

/**
 * @param $args
 * @param $filter
 */
function qw_generate_query_args_post_id( &$args, $filter ) {
	if ( isset( $filter['values']['post_ids_callback'] ) && is_callable( $filter['values']['post_ids_callback'] ) ) {
		$pids = call_user_func( $filter['values']['post_ids_callback'], $args );
	} else {
	    $values = qw_contextual_tokens_replace( $filter['values']['post_ids'] );
		$pids = explode( ",", $values );
	}

	array_walk( $pids, 'qw_trim' );
	$args[ $filter['values']['compare'] ] = $pids;
}


/**
 * Process submitted exposed form values
 *
 * @param $args
 * @param $filter
 * @param $values
 */
function qw_filter_post_id_exposed_process( &$args, $filter, $values ) {
	// default values if submitted is empty
	qw_filter_post_id_exposed_default_values( $filter, $values );

	// make into array
	$values = explode( ",", $values );
	array_walk( $values, 'qw_trim' );

	// check allowed values
	if ( isset( $filter['values']['exposed_limit_values'] ) ) {
		$allowed = explode( ",", $filter['values']['post_ids'] );
		// trim spaces
		array_walk( $allowed, 'qw_trim' );
		array_walk( $values, 'qw_trim' );
		// loop through and check allowed values
		foreach ( $values as $k => $value ) {
			if ( ! in_array( $value, $allowed ) ) {
				unset( $values[ $k ] );
			}
		}
	}
	// set the values
	$args[ $filter['values']['compare'] ] = $values;
}

/**
 * Exposed form
 *
 * @param $filter
 * @param $values
 */
function qw_filter_post_id_exposed_form( $filter, $values ) {
	// adjust for default values
	qw_filter_post_id_exposed_default_values( $filter, $values );
	?>
	<input type="text"
	       name="<?php print $filter['exposed_key']; ?>"
	       value="<?php print $values ?>"/>
	<?php
}

/**
 * Simple helper function to handle default values
 *
 * @param $filter
 * @param $values
 */
function qw_filter_post_id_exposed_default_values( $filter, &$values ) {
	if ( isset( $filter['values']['exposed_default_values'] ) ) {
		if ( is_null( $values ) ) {
			$values = $filter['values']['post_ids'];
		}
	}
}

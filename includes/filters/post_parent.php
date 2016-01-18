<?php

// add default filters to the filter
add_filter( 'qw_filters', 'qw_filter_post_parent' );

/**
 * @param $filters
 *
 * @return mixed
 */
function qw_filter_post_parent( $filters ) {

	$filters['post_parent'] = array(
		'title'               => __( 'Post Parent' ),
		'description'         => __( 'Use only with post type "Page" to show results with the chosen parent ID.' ),
		'form_callback'       => 'qw_filter_post_parent_form',
		'query_args_callback' => 'qw_generate_query_args_post_parent',
		'query_display_types' => array( 'page', 'widget' ),
		// exposed
		'exposed_form'        => 'qw_filter_post_parent_exposed_form',
		'exposed_process'     => 'qw_filter_post_parent_exposed_process',
	);

	return $filters;
}

/**
 * @param $args
 * @param $filter
 */
function qw_generate_query_args_post_parent( &$args, $filter ) {
	$args['post_parent'] = $filter['values']['post_parent'];
}

/**
 * @param $filter
 */
function qw_filter_post_parent_form( $filter ) {
	$form = new QW_Form_Fields( array(
			'form_field_prefix' => $filter['form_prefix'],
	) );

	print $form->render_field( array(
			'type' => 'text',
			'name' => 'post_parent',
			'value' => isset( $filter['values']['post_parent'] ) ? $filter['values']['post_parent'] : '',
			'class' => array( 'qw-js-title' ),
	) );
}

/**
 * Process submitted exposed form values
 *
 * @param $args
 * @param $filter
 * @param $values
 */
function qw_filter_post_parent_exposed_process( &$args, $filter, $values ) {
	// default values if submitted is empty
	qw_filter_post_parent_exposed_default_values( $filter, $values );

	// check allowed values
	if ( isset( $filter['values']['exposed_limit_values'] ) ) {
		if ( $values == $filter['values']['post_parent'] ) {
			$args['post_parent'] = $values;
		}
	}
	else {
		$args['post_parent'] = $values;
	}
}

/**
 * Exposed form
 *
 * @param $filter
 * @param $values
 */
function qw_filter_post_parent_exposed_form( $filter, $values ) {
	// default values
	qw_filter_post_parent_exposed_default_values( $filter, $values );
	?>
	<input type="text"
	       name="<?php print $filter['exposed_key']; ?>"
	       value="<?php print $values; ?>"/>
	<?php
}

/**
 * Simple helper function to handle default values
 *
 * @param $filter
 * @param $values
 */
function qw_filter_post_parent_exposed_default_values( $filter, &$values ) {
	if ( isset( $filter['values']['exposed_default_values'] ) ) {
		if ( is_null( $values ) ) {
			$values = $filter['values']['post_parent'];
		}
	}
}

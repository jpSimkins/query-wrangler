<?php

// add default overrides to the filter
add_filter( 'qw_overrides', 'qw_override_categories' );
add_filter( 'qw_pre_save', 'qw_override_categories_pre_save', 10, 2 );

function qw_override_categories( $overrides ) {

	$overrides['cats'] = array(
		'title'              => 'Categories',
		'description'        => 'Override output based on categories',
		'form_callback'      => 'qw_override_categories_form',
		'get_query_callback' => 'qw_override_categories_get_query',
	);

	return $overrides;
}

/**
 * Hook into qw_pre_save and add save additional data
 * Save term relationships to the query_override_terms table
 *
 * @param $options
 * @param $query_id
 *
 * @return mixed
 */
function qw_override_categories_pre_save( $options, $query_id ) {

	// no matter what, we delete all previous relationships
	global $wpdb;
	$table = $wpdb->prefix . "query_override_terms";

	// get a list of term_ids from this taxonomy for pre-save deletion
	$terms = get_terms( 'category', array( 'hide_empty' => FALSE ) );

	// delete all existing relationships
	foreach ( $terms as $term ) {
		$wpdb->delete( $table,
			array(
				'query_id' => $query_id,
				'term_id'  => $term->term_id,
			) );
	}

	// merge tags
	if ( isset( $options['override']['cats']['values'] ) &&
	     is_array( $options['override']['cats']['values'] )
	) {
		// new relationships to save
		$insert_terms = array();
		foreach ( $options['override']['cats']['values'] as $term_id => $name ) {
			if ( term_exists( $term_id, 'category' ) ) {
				$insert_terms[] = $term_id;
			}
		}

		// loop through all terms and insert them
		foreach ( $insert_terms as $term_id ) {
			$wpdb->insert( $table,
				array(
					'query_id' => $query_id,
					'term_id'  => $term_id,
				) );
		}
	}

	return $options;
}

/**
 * Settings for this override
 *
 * @param $override
 */
function qw_override_categories_form( $override ) {
	$category_ids = get_terms( 'category', array( 'fields' => 'ids', 'hide_empty' => 0 ) );
	$categories = array();
	foreach( $category_ids as $cat_id ){
		$categories[ $cat_id ] = get_cat_name( $cat_id );
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $override['form_prefix'],
	) );

	print $form->render_field( array(
			'type' => 'checkboxes',
			'name' => 'values',
			'description' => __( 'Select which categories to override.' ),
			'value' => isset( $override['values']['values'] ) ? $override['values']['values'] : array(),
			'options' => $categories,
			'class' => array( 'qw-js-title' ),
	) );
}

/**
 * Determine if this override should be executed
 * return a QW_Query object if so, otherwise return false;
 *
 * @return bool|QW_Query
 */
function qw_override_categories_get_query( $wp_query ) {
	if ( $wp_query->is_category() && $wp_query->is_archive() ) {
		$term     = $wp_query->get_queried_object();
		$query_id = qw_get_query_by_override_term( $term->term_id );

		if ( $query_id && $qw_query = qw_get_query( $query_id ) ) {

			// add the appropriate filter to the query
			$qw_query->add_handler_item( 'filter',
				'categories',
				array(
					'cat_operator' => 'cat',
					'cats'         => array( $term->term_id => $term->name ),
				) )
				// override the post title
				     ->override_options( array(
					'display' => array(
						'title' => single_term_title( '', FALSE ),
					)
				) );

			return $qw_query;
		}
	}

	return FALSE;
}
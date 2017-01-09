<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_taxonomy_terms' );

/**
 * Add field to qw_fields
 *
 * @param $fields
 *
 * @return array
 */
function qw_field_taxonomy_terms( $fields ) {
	$options = array();
	$taxes = get_taxonomies( array(
		'public' => true,
	), 'objects' );

	foreach( $taxes as $key => $tax ){
		$options[ $key ] = $tax->label;
	}

	$fields['taxonomy_terms'] = array(
		'title'            => 'Taxonomy Terms',
		'description'      => 'Information relating to the author of a post.',
		//'form_callback'    => 'qw_field_taxonomy_terms_form',
		'output_callback'  => 'qw_field_taxonomy_terms_output',
		'output_arguments' => TRUE,
		'form_fields' => array(

			'taxonomy_name' => array(
				'type' => 'select',
				'name' => 'taxonomy_name',
				'title' => __( 'Taxonomy' ),
				'options' => $options,
				'class' => array( 'qw-js-title' ),
			),
			'link_to_term' => array(
				'type' => 'checkbox',
				'name' => 'link_to_term',
				'title' => __( 'Link to the term page' ),
				'class' => array( 'qw-js-title' ),
			),
		)
	);

	return $fields;
}

/**
 * Output callback
 *
 * @param $post
 * @param $field
 *
 * @return null|string
 */
function qw_field_taxonomy_terms_output( $post, $field ) {
	$output = array();

	$terms = get_the_terms( $post->ID, $field['taxonomy_name'] );

	foreach( $terms as $term ){
		if ( !empty( $field['link_to_term'] ) ) {
			$output[] = '<a href="' . get_term_link( $term->term_id ) . '">' . $term->name . '</a>';
		}
		else {
			$output[] = $term->name;
		}
	}

	return "<span class='qw-taxonomy-term'>".
	       implode( "</span><span class='qw-taxonomy-term'>", $output ).
	       "</span>";
}

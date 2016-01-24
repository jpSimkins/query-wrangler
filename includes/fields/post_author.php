<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_author' );

/**
 * Add field to qw_fields
 *
 * @param $fields
 * @return array
 */
function qw_field_author( $fields ) {

	$fields['post_author'] = array(
		'title'            => __( 'Post Author' ),
		'description'      => __( 'Information relating to the author of a post.' ),
		'output_callback'  => 'qw_get_the_author',
		'output_arguments' => TRUE,
		'form_fields' => array(
			'output_type' => array(
				'type' => 'select',
				'name' => 'output_type',
				'title' => __( 'Author Field Settings' ),
				'description' => __( '' ),
				'options' => array(
					'name' => __( 'Author Name' ),
					'ID'   => __( 'Author ID' ),
				),
				'class' => array( 'qw-js-title' ),
			),
			'link_to_author' => array(
				'type' => 'checkbox',
				'name' => 'link_to_author',
				'title' => __( 'Link to author page' ),
				'description' => __( '' ),
				'default_value' => 0,
				'class' => array( 'qw-js-title' ),
			)
		),
	);

	return $fields;
}

/**
 * Author output callback
 *
 * @param $post
 * @param $field
 *
 * @return string
 */
function qw_get_the_author( $post, $field ) {
	switch ( $field['output_type'] ) {
		case 'ID':
			$author = $post->post_author;
			break;

		case 'name':
		default:
			$author = get_the_author();
			break;
	}

	if ( isset( $field['link_to_author'] ) ) {
		$author = '<a href="' . get_author_posts_url( $post->post_author ) . '">' . $author . '</a>';
	}

	return $author;
}

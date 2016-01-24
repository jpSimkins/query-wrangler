<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_author_avatar' );

/**
 * Add field to qw_fields
 *
 * @param $fields
 *
 * @return array
 */
function qw_field_author_avatar( $fields ) {

	$fields['post_author_avatar'] = array(
		'title'            => __( 'Post Author Avatar' ),
		'description'      => __( 'Avatar for the author of a post.' ),
		'output_callback'  => 'qw_get_avatar',
		'output_arguments' => TRUE,
		'form_fields' => array(
			'size' => array(
				'type' => 'number',
				'name' => 'size',
				'title' => __( 'Avatar Size' ),
				'description' => __( 'Desired avatar size in pixels.' ),
				'default_value' => 96,
				'class' => array( 'qw-js-title' ),
			),
			'link_to_author' => array(
				'type' => 'checkbox',
				'name' => 'link_to_author',
				'title' => __( 'Link to author page' ),
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $fields;
}

/**
 * Avatar output callback
 *   - get_avatar( $id_or_email, $size, $default, $alt );
 *
 * @param $post
 * @param $field
 *
 * @return string
 */
function qw_get_avatar( $post, $field ) {
	if ( isset( $field['link_to_author'] ) ) {
		$output = '<a href="' . get_author_posts_url( $post->post_author ) . '">' . get_avatar( $post->post_author,
				$field['size'] ) . '</a>';
	} else {
		$output = get_avatar( $post->post_author, $field['size'] );
	}

	return $output;
}

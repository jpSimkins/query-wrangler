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
		'title'            => 'Post Author Avatar',
		'description'      => 'Avatar for the author of a post.',
		'form_callback'    => 'qw_field_author_avatar_form',
		'output_callback'  => 'qw_get_avatar',
		'output_arguments' => TRUE,
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

/**
 * Avatar form callback
 *
 * @param $field
 */
function qw_field_author_avatar_form( $field ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $field['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'number',
		'name' => 'size',
		'title' => __( 'Avatar Size' ),
		'description' => __( 'Desired avatar size in pixels.' ),
		'value' => isset( $field['values']['size'] ) ? $field['values']['size'] : '',
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'checkbox',
		'name' => 'link_to_author',
		'title' => __( 'Link to author page' ),
		'value' => isset( $field['values']['link_to_author'] ) ? $field['values']['link_to_author'] : false,
		'class' => array( 'qw-js-title' ),
	) );
}
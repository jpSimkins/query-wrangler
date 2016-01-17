<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_featured_image' );

/*
 * Add field to qw_fields
 */
function qw_field_featured_image( $fields ) {

	$fields['featured_image'] = array(
		'title'            => 'Featured Image',
		'description'      => 'The "post_thumbnail" of a given row.',
		'output_callback'  => 'qw_theme_featured_image',
		'output_arguments' => TRUE,
		'form_callback'    => 'qw_field_featured_image_form',
	);

	return $fields;
}

/*
 * Image attachment settings Form
 */
function qw_field_featured_image_form( $field ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $field['form_prefix'],
	) );


	print $form->render_field( array(
		'type' => 'select',
		'name' => 'image_display_style',
		'title' => __( 'Image Display Style' ),
		'value' => isset( $field['values']['image_display_style'] ) ? $field['values']['image_display_style'] : '',
		'options' => get_intermediate_image_sizes(),
		'class' => array( 'qw-js-title' ),
	) );
}

/*
 * Turn a list of images into html
 *
 * @param $post
 * @param $field
 */
function qw_theme_featured_image( $post, $field ) {
	$style = $field['image_display_style'];
	if ( has_post_thumbnail( $post->ID ) ) {
		$image_id = get_post_thumbnail_id( $post->ID, $style );

		return wp_get_attachment_image( $image_id, $style );
	}
}

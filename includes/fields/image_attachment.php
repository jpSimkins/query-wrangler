<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_image_attachment' );

/**
 * Add field to qw_fields
 *
 * @param $fields
 * @param array
 */
function qw_field_image_attachment( $fields ) {

	$image_styles = get_intermediate_image_sizes();
	$image_styles = array_combine( $image_styles, $image_styles );

	$fields['image_attachment'] = array(
		'title'            => __( 'Image Attachment' ),
		'description'      => __( 'Image files that are attached to a post.' ),
		'output_callback'  => 'qw_theme_image',
		'output_arguments' => TRUE,
		'form_fields' => array(
			'image_display_count' => array(
				'type' => 'number',
				'name' => 'image_display_count',
				'title' => __( 'Number of items to show' ),
				'description' => __( '' ),
				'class' => array( 'qw-js-title' ),
			),
			'featured_image' => array(
				'type' => 'checkbox',
				'name' => 'featured_image',
				'title' => __( 'Featured Image Only' ),
				'class' => array( 'qw-js-title' ),
			),
			'image_display_style' => array(
				'type' => 'select',
				'name' => 'image_display_style',
				'title' => __( 'Image Display Style' ),
				'options' => $image_styles,
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $fields;
}

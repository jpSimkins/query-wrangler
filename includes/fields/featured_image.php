<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_featured_image' );

/*
 * Add field to qw_fields
 */
function qw_field_featured_image( $fields ) {

	$image_styles = get_intermediate_image_sizes();
	$image_styles = array_combine( $image_styles, $image_styles );

	$fields['featured_image'] = array(
		'title'            => __( 'Featured Image' ),
		'description'      => __( 'The "post_thumbnail" of a given row.' ),
		'output_callback'  => 'qw_theme_featured_image',
		'output_arguments' => TRUE,
		'form_fields' => array(
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

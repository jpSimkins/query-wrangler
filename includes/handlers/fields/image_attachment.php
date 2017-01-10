<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_image_attachment' );

/**
 * Add field to qw_fields
 *
 * @param $fields
 *
 * @return array
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

/**
 * Turn a list of images into html
 *
 * @param $post
 * @param $field
 *
 * @return string
 */
function qw_theme_image( $post, $field ) {
	$style = $field['image_display_style'];
	$count = $field['image_display_count'];
	$featured_image_id = isset( $field['featured_image'] ) ? get_post_thumbnail_id( $post->ID ) : NULL;
	$images = qw_get_post_images( $post->ID );
	$output = '';

	if ( is_array( $images ) ) {
		$i      = 0;
		foreach ( $images as $image ) {
			if ( $featured_image_id ) {
				if ( $image->ID == $featured_image_id ) {
					$output .= wp_get_attachment_image( $image->ID, $style );
				}

			} else {
				if ( $count == 0 || ( $i < $count ) ) {
					$output .= wp_get_attachment_image( $image->ID, $style );
				}
			}
			$i ++;
		}
	}

	return $output;
}

/**
 * Get all images attached to a single post
 *
 * @param int $post_id The Wordpress ID for the post or page to get images from
 *
 * @return array of images
 */
function qw_get_post_images( $post_id ) {
	$child_args = array(
		"post_type"      => "attachment",
		"post_mime_type" => "image",
		"post_parent"    => $post_id
	);
	// Get images for this post
	$images = get_children( $child_args );

	// If images exist for this page
	if ( !empty( $images ) )
	{
		// sort this so menu order matters
		$sorted   = array();
		$unsorted = array();

		foreach ( $images as $image )
		{
			if ( $image->menu_order !== 0 ) {
				$sorted[ $image->menu_order ] = $image;
			} else {
				$unsorted[] = $image;
			}
		}
		// sort menu order
		ksort( $sorted );

		// reset array
		$sorted = array_values( $sorted );

		// add unsorted
		$sorted = array_merge( $sorted, $unsorted );

		$images = $sorted;
	}

	return $images;
}

<?php

/**
 * Get files attached to a post
 *
 * @param int $post_id The WP post id
 * @return Array of file posts
 */
function qw_get_post_files( $post_id ) {
	$child_args = array(
		"post_type"   => "attachment",
		"post_parent" => $post_id,
	);
	// Get images for this post
	$files = get_posts( $child_args );

	if ( is_array( $files ) ) {
		return $files;
	}

	return FALSE;
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
	if ( is_array( $images ) ) {
		// sort this so menu order matters
		$sorted   = array();
		$unsorted = array();
		foreach ( $images as $image ) {
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

		return $sorted;
	}
}

/**
 * TODO - allow images to use file styles
 *
 * @return array
 */
function _qw_get_image_styles() {
	$image_styles = qw_all_file_styles();
	$image_sizes  = get_intermediate_image_sizes();

	foreach ( $image_sizes as $key => $size ) {
		$image_styles[ $size ] = array(
			'description' => $size,
			'callback'    => 'qw_theme_image',
		);
	}

	return $image_styles;
}

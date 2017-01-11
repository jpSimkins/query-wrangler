<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_file_attachment' );

/**
 * Add field to qw_fields
 *
 * @param $fields
 *
 * @return array
 */
function qw_field_file_attachment( $fields ) {

	$file_styles = array();
	foreach(qw_all_file_styles() as $style => $details ){
		$file_styles[ $style ] = $details['title'];
	}

	$fields['file_attachment'] = array(
		'title'            => __( 'File Attachment' ),
		'description'      => __( 'Files that are attached to a post.' ),
		'output_callback'  => 'qw_theme_file',
		'output_arguments' => TRUE,
		'form_fields' => array(
			'file_display_count' => array(
				'type' => 'number',
				'name' => 'file_display_count',
				'title' => __( 'Number of items to show' ),
				'default_value' => 0,
				'class' => array( 'qw-js-title' ),
			),
			'file_display_style' => array(
				'type' => 'select',
				'name' => 'file_display_style',
				'title' => __( 'File Display Style' ),
				'options' => $file_styles,
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $fields;
}

/**
 * Simple list of all File Styles used by certain "Field" handler item types
 *
 * @return array
 */
function qw_all_file_styles()
{
	$default = array(
		'link' => array(
			'title' => __( 'Filename Link to File' ),
		),
		'link_url' => array(
			'title' => __( 'URL Link to File' ),
		),
		'url' => array(
			'title' => __( 'URL of File' ),
		),
	);

	$styles = apply_filters( 'qw_file_styles', $default );

	return $styles;
}

/**
 * Get and theme attached post files
 *
 * @param $post
 * @param $field
 *
 * @return string
 */
function qw_theme_file( $post, $field ) {
	$style = ( $field['file_display_style'] ) ? $field['file_display_style'] : 'link';
	$count = ( $field['file_display_count'] ) ? $field['file_display_count'] : 0;
	$files = qw_get_post_files( $post->ID );
	$items = array();
	$output = '';

	if ( !empty( $files ) )
	{
		$i = 0;
		foreach ( $files as $file )
		{
			if ( ( $count == 0 || ( $i < $count ) ) &&
			     substr( $file->post_mime_type, 0, 5 ) != "image")
			{
				switch ( $style ) {
					case 'url':
						$items[] = wp_get_attachment_url( $file->ID );
						break;

					case 'link':
						// complete file name
						$file_name = explode( "/", $file->guid );
						$file_name = $file_name[ count( $file_name ) - 1 ];
						$items[] = '<a href="' . wp_get_attachment_url( $file->ID ) . '" class="query-file-link">' . $file_name . '</a>';
						break;

					case 'link_url':
						$items[] = '<a href="' . wp_get_attachment_url( $file->ID ) . '" class="query-file-link">' . $file->guid . '</a>';
						break;
				}
			}
			$i ++;
		}
	}

	if ( !empty( $items ) ){
		$output = "<span class='qw-file-attachment'>".implode( "</span><span class='qw-file-attachment'>", $items ) ."</span>";
	}

	return $output;
}

/**
 * Get files attached to a post
 *
 * @param int $post_id The WP post id
 *
 * @return array of file posts
 */
function qw_get_post_files( $post_id ) {
	$child_args = array(
		"post_type"   => "attachment",
		"post_parent" => $post_id,
	);

	// Get images for this post
	return get_posts( $child_args );
}

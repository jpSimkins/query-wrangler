<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_file_attachment' );

// add default file styles to the filter
add_filter( 'qw_file_styles', 'qw_default_file_styles', 0 );

/**
 * Add field to qw_fields
 *
 * @param $fields
 */
function qw_field_file_attachment( $fields ) {

	$fields['file_attachment'] = array(
		'title'            => __( 'File Attachment' ),
		'description'      => __( 'Files that are attached to a post.' ),
		'output_callback'  => 'qw_theme_file',
		'output_arguments' => TRUE,
		'form_callback'    => 'qw_field_file_attachment_form',
	);

	return $fields;
}

/**
 * File Styles
 *
 * @return array of file styles
 */
function qw_default_file_styles( $file_styles ) {
	$file_styles['link']     = array(
		'description' => __( 'Filename Link to File' ),
		//'callback' => 'qw_theme_file',
	);
	$file_styles['link_url'] = array(
		'description' => __( 'URL Link to File' ),
		//'callback' => 'qw_theme_file',
	);
	$file_styles['url']      = array(
		'description' => __( 'URL of File' ),
		//'callback' => 'qw_theme_file',
	);

	return $file_styles;
}

/**
 * File attachment settings Form
 *
 * @param $field
 */
function qw_field_file_attachment_form( $field ) {
	$file_styles = array();

	foreach( qw_all_file_styles() as $key => $style ){
		$file_styles[ $key ] = $style['description'];
	}


	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $field['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'number',
		'name' => 'file_display_count',
		'title' => __( 'Number of items to show' ),
		'value' => isset( $field['values']['file_display_count'] ) ? $field['values']['file_display_count'] : 0,
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'file_display_style',
		'title' => __( 'File Display Style' ),
		'value' => isset( $field['values']['file_display_style'] ) ? $field['values']['file_display_style'] : '',
		'options' => $file_styles,
		'class' => array( 'qw-js-title' ),
	) );
}

/**
 * Get and theme attached post files
 *
 * @param $post
 * @param $field
 * @return string
 */
function qw_theme_file( $post, $field ) {
	$style = ( $field['file_display_style'] ) ? $field['file_display_style'] : 'link';
	$count = ( $field['file_display_count'] ) ? $field['file_display_count'] : 0;

	$files = qw_get_post_files( $post->ID );
	if ( is_array( $files ) ) {
		$output = '';
		$i      = 0;
		foreach ( $files as $file ) {
			if ( ( $count == 0 || ( $i < $count ) ) && substr( $file->post_mime_type,
					0,
					5 ) != "image"
			) {
				switch ( $style ) {
					case 'url':
						$output .= wp_get_attachment_url( $file->ID );
						break;

					case 'link':
						// complete file name
						$file_name = explode( "/", $file->guid );
						$file_name = $file_name[ count( $file_name ) - 1 ];
						$output .= '<a href="' . wp_get_attachment_url( $file->ID ) . '" class="query-file-link">' . $file_name . '</a>';
						break;

					case 'link_url':
						$output .= '<a href="' . wp_get_attachment_url( $file->ID ) . '" class="query-file-link">' . $file->guid . '</a>';
						break;
				}
			}
			$i ++;
		}

		return $output;
	}
}

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


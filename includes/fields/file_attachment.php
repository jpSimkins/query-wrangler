<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_file_attachment' );

/**
 * Add field to qw_fields
 *
 * @param $fields
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

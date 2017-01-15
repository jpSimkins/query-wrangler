<?php

// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_field_meta_value_new' );

/**
 * Add field to qw_fields
 *
 * @param $fields
 * @return array
 */
function qw_field_meta_value_new( $fields ) {
	$fields['custom_field'] = array(
		'title'            => __( 'Custom Field' ),
		'description'      => __( 'Select a custom field (meta value)' ),
		'output_callback'  => 'qw_display_post_meta_value_new',
		'output_arguments' => TRUE,
		'form_callback'    => 'qw_meta_value_new_form_callback',
		'content_options'  => TRUE,
		// need to dynamically fix this on the the form and during output
		'meta_key'         => '',
	);

	return $fields;
}

/**
 * Meta value field display handlers
 *
 * @return array
 */
function qw_all_meta_value_display_handlers()
{
	$displays = apply_filters( 'qw_meta_value_display_handlers', array() );
	$displays = qw_pre_process_handler_item_types( $displays );

	return $displays;
}

/**
 * Post Meta form settings
 *
 * @param $field
 */
function qw_meta_value_new_form_callback( $field ) {
	$display_handlers_options = array();
	$display_handlers = qw_all_meta_value_display_handlers();

	foreach ($display_handlers as $handler => $details ){
		$display_handlers_options[ $handler ] = $details['title'];
	}

	$image_styles = get_intermediate_image_sizes();
	$image_styles = array_combine( $image_styles, $image_styles );


	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $field['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'meta_key',
		'title' => __( 'Meta Key' ),
		'description' => __( "The custom field name / meta key that identifies the field." ),
		'value' => isset( $field['values']['meta_key'] ) ? $field['values']['meta_key'] : '',
		'class' => array( 'qw-js-title', 'qw-meta-value-key-autocomplete' ),
	) );

	print $form->render_field( array(
		'type' => 'number',
		'name' => 'meta_value_count',
		'title' => __( 'Count' ),
		'description' => __( "Number of the meta values to show. Use '0' for all values." ),
		'value' => isset( $field['values']['meta_value_count'] ) ? $field['values']['meta_value_count'] : 1,
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'meta_value_separator',
		'title' => __( 'Separator' ),
		'description' => __( 'How to separate the meta values (if more than 1).' ),
		'value' => isset( $field['values']['meta_value_separator'] ) ? $field['values']['meta_value_separator'] : '',
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'display_handler',
		'title' => __( 'Display Handler' ),
		'description' => __( 'Select the method fo displaying the meta value.
			To display the raw value, choose -none-.' ),
		'value' => isset( $field['values']['display_handler'] ) ? $field['values']['display_handler'] : '',
		'options' => $display_handlers_options,
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'checkbox',
		'name' => 'are_image_ids',
		'title' => __( 'Load Image IDs as Images' ),
		'description' => __( 'If the meta value returned from the display
			handler as an image ID, display the image HTML.' ),
		'value' => isset( $field['values']['are_image_ids'] ) ? $field['values']['are_image_ids'] : '',
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'image_display_style',
		'title' => __( 'Image Display Style' ),
		'description' => __( 'If the meta value is an Image ID, select the style for display.' ),
		'value' => isset( $field['values']['image_display_style'] ) ? $field['values']['image_display_style'] : '',
		'options' => $image_styles,
		'class' => array( 'qw-js-title' ),
	) );

	if ( function_exists( 'get_custom_field' ) ) {

		print $form->render_field( array(
			'type' => 'text',
			'name' => 'cctm_chaining',
			'title' => __( 'CCTM Output Filters' ),
			'description' => __( 'Include first colon. ex, ":filter1:filter2".
				Or to get image IDs from an image field, ":raw".' ) .
                 '<a target="_blank" href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/OutputFilters#Chaining">CCTM Filter Chaining</a>',
			'value' => isset( $field['values']['cctm_chaining'] ) ? $field['values']['cctm_chaining'] : '',
			'class' => array( 'qw-js-title' ),
		) );
	}
}

/**
 * Display the post meta field based on field settings
 *
 * @param $post
 * @param $field
 * @return string
 */
function qw_display_post_meta_value_new( $post, $field ) {
	// pick up after the old meta_value field
	// try to find the old meta_key
	if ( empty( $field['meta_key'] ) && strpos( $field['type'], 'meta_' ) === 0 )
	{
		$field['meta_key'] = substr( $field['type'], 5 );
	}

	$display_handlers    = qw_all_meta_value_display_handlers();
	$display_handler_key = isset( $field['display_handler'] ) ? $field['display_handler'] : 'none';
	$handler             = isset( $display_handlers[ $display_handler_key ] ) ? $display_handlers[ $display_handler_key ] : $display_handlers['none'];

	$count       = isset( $field['meta_value_count'] ) ? $field['meta_value_count'] : 1;
	$separator   = isset( $field['meta_value_separator'] ) ? $field['meta_value_separator'] : '';
	$meta_values = array();
	$values      = array();

	if ( function_exists( $handler['callback'] ) ) {
		$meta_values = call_user_func( $handler['callback'], $post, $field );

		// ensure we're working with an array
		if ( ! is_array( $meta_values ) ) {
			$meta_values = array( $meta_values );
		}
	}

	// handle count limit
	if ( $count <= 0 || count( $meta_values ) <= $count )
	{
		$values = $meta_values;
	}
	else {
		$i = 0;
		foreach ( $meta_values as $k => $v ) {
			if ( $i < $count ) {
				$values[] = $v;
			}
			$i ++;
		}
	}

	// image ids
	if ( !empty( $field['are_image_ids'] ) ) {
		$image_ids = $values;
		$values    = array();
		foreach ( $image_ids as $image_id ) {
			$values[] = wp_get_attachment_image( $image_id, $field['image_display_style'] );
		}
	}

	return implode( $separator, $values );
}

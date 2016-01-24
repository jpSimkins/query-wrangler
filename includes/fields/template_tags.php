<?php

// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_template_tags_fields' );

/*
 * All Fields and Settings
 *
 * Template

  $fields['hook_key'] = array(
    // title displayed to query-wrangler user
    'title' => 'File Attachment',

    // description on the field form
    'description' => 'Just a useful description of this field'

    // optional) callback for outputting a field, must return the results
    'output_callback' => 'qw_theme_file',

    // (optional) where or not to pass $post and $field into the output_callback
    //    useful for custom functions
    'output_arguments' => true,

    // (optional) callback function for field forms
    'form_callback' => 'qw_form_file_attachment',
  );

 */
function qw_template_tags_fields( $fields ) {
	$fields['post_title'] = array(
		'title'           => __( 'Post Title' ),
		'description'     => __( 'The title of a post.' ),
		'output_callback' => 'get_the_title',
	);
	$fields['post_content'] = array(
		'title'           => __( 'Post Content' ),
		'description'     => __( 'The full content body of a post.' ),
		'output_callback' => 'get_the_content',
		'content_options' => TRUE,
	);
	$fields['post_excerpt'] = array(
		'title'           => __( 'Post Excerpt' ),
		'description'     => __( 'The excerpt of a post.' ),
		'output_callback' => 'get_the_excerpt',
		'content_options' => TRUE,
	);
	$fields['post_date'] = array(
		'title'           => __( 'Post Date' ),
		'description'     => __( 'Published date of a post.' ),
		'output_callback' => 'get_the_date',
	);
	$fields['permalink'] = array(
		'title'           => __( 'Permalink' ),
		'description'     => __( 'Pretty URL for a post.' ),
		'output_callback' => 'get_permalink',
	);

	return $fields;
}

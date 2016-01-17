<?php

// add default field styles to the filter
add_filter( 'qw_sort_options', 'qw_default_sort_options' );

/**
 * Default Sort Options
 *
 * @param $sort_options
 * @return array
 */
function qw_default_sort_options( $sort_options ) {

	$sort_options['author_id']      = array(
		'title'       => __( 'Author' ),
		'description' => __( 'The content author ID.' ),
		'type'        => 'author',
	);
	$sort_options['comment_count']  = array(
		'title'       => __( 'Comment Count' ),
		'description' => __( 'Total number of comments on a piece of content.' ),
	);
	$sort_options['menu_order']     = array(
		'title'       => __( 'Menu Order (for Page post_types)' ),
		'description' => __( 'Menu Order of a Page.' ),
	);
	$sort_options['meta_value']     = array(
		'title'       => __( 'Meta value' ),
		'description' => __( "Note that a 'meta_key=keyname' filter must also be present in the query. Good for sorting words, but not numbers." ),
	);
	$sort_options['meta_value_num'] = array(
		'title'       => __( 'Meta value number' ),
		'description' => __( "Order by numeric meta value. Also note that a 'meta_key' filter must be present in the query. This value allows for numerical sorting as noted above in 'meta_value'." ),
	);
	$sort_options['none']           = array(
		'title'         => __( 'None' ),
		'description'   => __( 'No sort order.' ),
		'order_options' => array(
			'none' => 'None',
		)
	);
	$sort_options['post__in']       = array(
		'title'         => __( 'Post__in order' ),
		'description'   => __( 'Preserve post ID order given in the post__in array.' ),
		'order_options' => FALSE,
	);
	$sort_options['post_date']      = array(
		'title'       => __( 'Date' ),
		'description' => __( 'The posted date of content.' ),
		'type'        => 'date',
	);
	$sort_options['post_ID']        = array(
		'title'       => __( 'Post ID' ),
		'description' => __( 'The ID of the content.' ),
		'type'        => 'ID',
	);
	$sort_options['post_modified']  = array(
		'title'       => __( 'Date Modified' ),
		'description' => __( 'Date content was last modified.' ),
		'type'        => 'modified',
	);
	$sort_options['post_parent']    = array(
		'title'       => __( 'Parent' ),
		'description' => __( 'The parent post for content.' ),
		'type'        => 'parent',
	);
	$sort_options['post_title']     = array(
		'title'       => __( 'Title' ),
		'description' => __( 'The title of the content.' ),
		'type'        => 'title',
	);
	$sort_options['rand']           = array(
		'title'       => __( 'Random' ),
		'description' => __( 'Random order.' ),
	);

	return $sort_options;
}

/*
 * Default sort options 'order' options form
 */
function qw_form_default_sort_order_options( $sort ) {
	if ( ! empty( $sort['order_options'] ) ) {

		$form = new QW_Form_Fields( array(
				'form_field_prefix' => $sort['form_prefix'],
		) );

		print $form->render_field( array(
				'type' => 'select',
				'name' => 'order_value',
				'title' => __( 'Order by ' ) . $sort['title'],
				'description' => __( 'Select how to order the results.' ),
				'value' => $sort['values']['order_value'],
				'options' => $sort['order_options'],
				'class' => array( 'qw-js-title' ),
		) );
	}
}

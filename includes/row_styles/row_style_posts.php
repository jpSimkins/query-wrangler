<?php

add_filter( 'qw_row_styles', 'qw_row_style_posts', 0 );

/**
 * Very simple Row style post title and content
 *
 * @param $row_styles
 *
 * @return mixed
 */
function qw_row_style_posts( $row_styles )
{
	$row_styles['posts']  = array(
		'title'             => __( 'Posts' ),
		'settings_callback' => 'qw_row_style_posts_settings',
		'settings_key'      => 'post_settings',
		'make_rows_callback'=> 'qw_row_style_posts_make_rows',
	);

	return $row_styles;
}

/**
 * Settings fields for the Posts row style
 *
 * @param $row_style
 * @param $display
 */
function qw_row_style_posts_settings( $row_style, $display )
{
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => QW_FORM_PREFIX . "[display][{$row_style['settings_key']}]",
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'size',
		'description' => __( 'Select the amount of the post to be shown.' ),
		'value' => isset( $row_style['values']['size'] ) ? $row_style['values']['size'] : '',
		'options' => array(
			'complete' => __( 'Complete Post' ),
			'excerpt' => __( 'Excerpt' ),
		),
		'class' => array( 'qw-js-title' ),
	) );
}

/**
 * Render the rows of this row_style as an array of HTML
 *
 * @param $qw_query
 * @param $options
 *
 * @return array
 */
function qw_row_style_posts_make_rows( &$qw_query, $options )
{
	$groups          = array();
	$i               = 0;
	$current_post_id = get_the_ID();
	$last_row = $qw_query->post_count - 1;

	while ( $qw_query->have_posts() ) {
		$qw_query->the_post();
		$template_args = array(
			'template' => 'query-' . $options['display']['post_settings']['size'],
			'slug'     => $options['meta']['slug'],
			'style'    => $options['display']['post_settings']['size'],
		);

		$row = array(
			'row_classes' => qw_row_classes( $i, $last_row ),
		);
		$field_classes = array( 'query-post-wrapper' );

		// add class for active menu trail
		if ( is_singular() && get_the_ID() === $current_post_id ) {
			$field_classes[] = 'active-item';
		}

		$row['fields'][ $i ]['classes'] = implode( " ", $field_classes );
		$row['fields'][ $i ]['output']  = theme( 'query_display_rows', $template_args );
		$row['fields'][ $i ]['content'] = $row['fields'][ $i ]['output'];

		// can't really group posts row style
		$groups[ $i ][ $i ] = $row;
		$i ++;
	}

	$rows = qw_make_groups_rows( $groups );

	return $rows;
}
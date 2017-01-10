<?php

add_filter( 'qw_row_styles', 'qw_row_style_template_part', 0 );

/**
 * Row style that leverages WordPress get_template_part() function for rendering
 * query rows
 *
 * @param $row_styles
 *
 * @return array
 */
function qw_row_style_template_part( $row_styles )
{
	$row_styles['template_part'] = array(
		'title'             => __( 'Template Part' ),
		'settings_callback' => 'qw_row_style_template_part_settings',
		'settings_key'      => 'template_part_settings',
		'make_rows_callback'=> 'qw_row_style_template_part_make_rows',
	);

	return $row_styles;
}

/**
 * Additional settings for this row_style
 *
 * @param $row_style
 * @param $display
 */
function qw_row_style_template_part_settings( $row_style, $display )
{
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => QW_FORM_PREFIX . "[display][{$row_style['settings_key']}]",
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'path',
		'title' => __( 'Path' ),
		'value' => isset( $row_style['values']['path'] ) ? $row_style['values']['path'] : '',
		'class' => array( 'qw-js-title' ),
	) );
	print $form->render_field( array(
		'type' => 'text',
		'name' => 'name',
		'title' => __( 'Name' ),
		'value' => isset( $row_style['values']['name'] ) ? $row_style['values']['name'] : '',
		'class' => array( 'qw-js-title' ),
	) );
}

/**
 * Render the rows for this row_style as an array of HTML
 *
 * @param $wp_query
 * @param $options
 *
 * @return array
 */
function qw_row_style_template_part_make_rows( $wp_query, $options )
{
	$groups          = array();
	$i               = 0;
	$current_post_id = get_the_ID();
	$last_row = $wp_query->post_count - 1;

	while ( $wp_query->have_posts() ) {
		$wp_query->the_post();
		$path = $options['display']['template_part_settings']['path'];
		$name = $options['display']['template_part_settings']['name'];

		$row = array(
			'row_classes' => qw_row_classes( $i, $last_row ),
		);
		$field_classes = array( 'query-post-wrapper' );

		// add class for active menu trail
		if ( is_singular() && get_the_ID() === $current_post_id ) {
			$field_classes[] = 'active-item';
		}

		ob_start();
		get_template_part( $path, $name );
		$output = ob_get_clean();

		$row['fields'][ $i ]['classes'] = implode( " ", $field_classes );
		$row['fields'][ $i ]['output'] = $output;
		$row['fields'][ $i ]['content'] = $row['fields'][ $i ]['output'];

		// can't really group posts row style
		$groups[ $i ][ $i ] = $row;
		$i ++;
	}

	$rows = qw_make_groups_rows( $groups );

	return $rows;
}
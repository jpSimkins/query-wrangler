<?php

add_filter( 'qw_styles', 'qw_style_ordered_list' );

/**
 * @param $styles
 *
 * @return mixed
 */
function qw_style_ordered_list( $styles )
{
	$styles['ordered_list'] = array(
		'title'        => __( 'Ordered List' ),
		'template'     => 'query-ordered_list',
		'default_path' => __DIR__ . '/templates',
	);

	return $styles;
}
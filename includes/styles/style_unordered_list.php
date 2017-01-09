<?php

add_filter( 'qw_styles', 'qw_style_unordered_list' );

/**
 * @param $styles
 *
 * @return mixed
 */
function qw_style_unordered_list( $styles )
{
	$styles['unordered_list'] = array(
		'title'        => __( 'Unordered List' ),
		'template'     => 'query-unordered_list',
		'default_path' => __DIR__ . '/templates',
	);

	return $styles;
}
<?php

add_filter( 'qw_styles', 'qw_style_table' );

/**
 * @param $styles
 *
 * @return mixed
 */
function qw_style_table( $styles )
{
	$styles['table'] = array(
		'title'        => __( 'Table' ),
		'template'     => 'query-table',
		'default_path' => __DIR__ . '/templates',
	);

	return $styles;
}
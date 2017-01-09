<?php

add_filter( 'qw_styles', 'qw_style_unformatted' );

/**
 * @param $styles
 *
 * @return mixed
 */
function qw_style_unformatted( $styles )
{
	$styles['unformatted'] = array(
		'title'        => __( 'Unformatted' ),
		'template'     => 'query-unformatted',
		'default_path' => __DIR__ . '/templates',
	);

	return $styles;
}
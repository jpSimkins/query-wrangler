<?php

add_filter( 'qw_pager_types', 'qw_pager_type_numbers' );

/**
 * Page numbers
 *
 * @param $pager_types
 *
 * @return array
 */
function qw_pager_type_numbers( $pager_types )
{
	$pager_types['numbers'] = array(
		'title'    => __( 'Page Numbers' ),
		'callback' => 'qw_pager_type_numbers_callback',
	);

	return $pager_types;
}

/**
 * Pager with page numbers
 *
 * @param array $pager Query options for pager
 * @param object $wp_query Object
 *
 * @return string HTML for pager
 */
function qw_pager_type_numbers_callback( $pager, $wp_query ) {
	$big          = intval( $wp_query->found_posts . '000' );
	$args         = array(
		'base'    => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
		'format'  => '?paged=%#%',
		'current' => max( 1, qw_get_page_number( $wp_query ) ),
		'total'   => $wp_query->max_num_pages
	);
	$pager_themed = paginate_links( $args );

	return $pager_themed;
}

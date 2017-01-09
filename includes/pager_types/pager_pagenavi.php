<?php

add_filter( 'qw_pager_types', 'qw_pager_type_pagenavi' );

function qw_pager_type_pagenavi( $pager_types )
{
	// WP PageNavi Plugin
	if ( function_exists( 'wp_pagenavi' ) ) {
		$pager_types['pagenavi'] = array(
			'title'    => __( 'PageNavi' ),
			'callback' => 'qw_pager_type_pagenavi_callback'
		);
	}

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
function qw_pager_type_pagenavi_callback( $pager, $wp_query )
{

	if ( function_exists( 'wp_pagenavi' ) ) {
		return wp_pagenavi( array(
			'query' => $wp_query,
			'echo'  => FALSE,
		) );
	}

	// function args
//	array(
//		'before' => '',
//		'after' => '',
//		'wrapper_tag' => 'div',
//		'wrapper_class' => 'wp-pagenavi',
//		'options' => array(),
//		'query' => $GLOBALS['wp_query'],
//		'type' => 'posts',
//		'echo' => true
//	);

	// options defaults
//	array(
//		'pages_text'    => __( 'Page %CURRENT_PAGE% of %TOTAL_PAGES%', 'wp-pagenavi' ),
//		'current_text'  => '%PAGE_NUMBER%',
//		'page_text'     => '%PAGE_NUMBER%',
//		'first_text'    => __( '&laquo; First', 'wp-pagenavi' ),
//		'last_text'     => __( 'Last &raquo;', 'wp-pagenavi' ),
//		'prev_text'     => __( '&laquo;', 'wp-pagenavi' ),
//		'next_text'     => __( '&raquo;', 'wp-pagenavi' ),
//		'dotleft_text'  => __( '...', 'wp-pagenavi' ),
//		'dotright_text' => __( '...', 'wp-pagenavi' ),
//		'num_pages' => 5,
//		'num_larger_page_numbers' => 3,
//		'larger_page_numbers_multiple' => 10,
//		'always_show' => false,
//		'use_pagenavi_css' => true,
//		'style' => 1,
//	);

}

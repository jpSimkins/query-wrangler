<?php

add_filter( 'qw_pager_types', 'qw_pager_type_default' );

/**
 * Default pager type
 *
 * @param $pager_types
 *
 * @return array
 */
function qw_pager_type_default( $pager_types )
{
	$pager_types['default'] = array(
		'title'    => __( 'Default' ),
		'callback' => 'qw_pager_type_default_callback'
	);

	return $pager_types;
}

/**
 * Default pager implementation
 * Simple "Prev page", "Next page" style
 *
 * @param $pager
 * @param $wp_query
 *
 * @return string
 */
function qw_pager_type_default_callback( $pager, &$wp_query )
{
	$template = "<div class='%s'><a href='%s'>%s</a></div>";

	// help figure out the current page
	$exposed_path_array = explode( '?', $_SERVER['REQUEST_URI'] );
	$path_array         = explode( '/page/', $exposed_path_array[0] );

	$exposed_path = NULL;
	if ( isset( $exposed_path_array[1] ) ) {
		$exposed_path = $exposed_path_array[1];
	}

	$pager_themed      = '';
	$pager['next']     = ( $pager['next'] ) ? $pager['next'] : 'Next Page &raquo;';
	$pager['previous'] = ( $pager['previous'] ) ? $pager['previous'] : '&laquo; Previous Page';

	if ( $page = qw_get_page_number( $wp_query ) ) {
		$path = rtrim( $path_array[0], '/' );

		$wpurl = get_bloginfo( 'wpurl' );

		// previous link with page number
		if ( $page >= 3 ) {
			$url = $wpurl . $path . '/page/' . ( $page - 1 );
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}

			$pager_themed .= sprintf( $template, 'query-prevpage', $url, $pager['previous'] );
		}
		// previous link with no page number
		else if ( $page == 2 ) {
			$url = $wpurl . $path;
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}

			$pager_themed .= sprintf( $template, 'query-prevpage', $url, $pager['previous'] );
		}

		// next link
		if ( ( $page + 1 ) <= $wp_query->max_num_pages ) {
			$url = $wpurl . $path . '/page/' . ( $page + 1 );
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}

			$pager_themed .= sprintf( $template, 'query-nextpage', $url, $pager['next'] );
		}

		return $pager_themed;
	}
}

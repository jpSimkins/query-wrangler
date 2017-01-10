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
		'callback' => 'qw_pager_type_default_callback',
		'settings_callback' => 'qw_pager_type_default_settings_callback',
		'settings_key' => 'default_settings',
	);

	return $pager_types;
}

/**
 * Additional settings for this pager type
 *
 * @param $pager
 * @param $display
 */
function qw_pager_type_default_settings_callback( $pager, $display )
{
	// default settings values
	$values = array(
		'previous' => '&laquo; Previous Page',
		'next' => 'Next Page &raquo;',
	);

	if ( !empty( $pager['values'] ) ) {
		$values = array_replace( $values, $pager['values'] );
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $pager['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'previous',
		'title' => __( 'Previous Page Label' ),
		'value' => $values['previous'],
		'description' => __( 'Modify the text for the "previous" page link.' ),
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'next',
		'title' => __( 'Next Page Label' ),
		'value' => $values['next'],
		'description' => __( 'Modify the text for the next page link.' ),
	) );
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

	if ( $page = qw_get_page_number( $wp_query ) ) {
		$path = rtrim( $path_array[0], '/' );

		$wpurl = get_bloginfo( 'wpurl' );

		// previous link with page number
		if ( $page >= 3 ) {
			$url = $wpurl . $path . '/page/' . ( $page - 1 );
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}

			$pager_themed .= sprintf( $template, 'query-prevpage', $url, $pager['values']['previous'] );
		}
		// previous link with no page number
		else if ( $page == 2 ) {
			$url = $wpurl . $path;
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}

			$pager_themed .= sprintf( $template, 'query-prevpage', $url, $pager['values']['previous'] );
		}

		// next link
		if ( ( $page + 1 ) <= $wp_query->max_num_pages ) {
			$url = $wpurl . $path . '/page/' . ( $page + 1 );
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}

			$pager_themed .= sprintf( $template, 'query-nextpage', $url, $pager['values']['next'] );
		}

		return $pager_themed;
	}
}

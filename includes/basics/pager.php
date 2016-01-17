<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_pager' );

// add default pager types
add_filter( 'qw_pager_types', 'qw_default_pager_types', 0 );

/*
 * Basic Settings
 */
function qw_basic_settings_pager( $basics ) {
	$basics['pager'] = array(
		'title'         => __( 'Pager' ),
		'description'   => __( 'Select which type of pager to use.' ),
		'option_type'   => 'display',
		'form_callback' => 'qw_basic_pager_form',
		'weight'        => 0,
	);

	return $basics;
}

/*
 * Setup pager types
 */
function qw_default_pager_types( $pagers ) {
	$pagers['default'] = array(
		'title'    => __( 'Default' ),
		'callback' => 'qw_theme_pager_default'
	);
	$pagers['numbers'] = array(
		'title'    => __( 'Page Numbers' ),
		'callback' => 'qw_theme_pager_numbers'
	);

	// WP PageNavi Plugin
	if ( function_exists( 'wp_pagenavi' ) ) {
		$pagers['pagenavi'] = array(
			'title'    => __( 'PageNavi' ),
			'callback' => 'wp_pagenavi'
		);
	}

	return $pagers;
}

/**
 * @param $item
 * @param $display
 */
function qw_basic_pager_form( $item, $display ) {
	$pager_types = array();
	foreach( qw_all_pager_types() as $key => $details ){
		$pager_types[ $key ] = $details['title'];
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'checkbox',
		'name_prefix' => '[page][pager]',
		'name' => 'active',
		'title' => __( 'Use Pagination' ),
		'value' => isset( $display['page']['pager']['active'] ) ? $display['page']['pager']['active'] : false,
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name_prefix' => '[page][pager]',
		'name' => 'type',
		'title' => __( 'Pager Type' ),
		'value' => isset( $display['page']['pager']['type'] ) ? $display['page']['pager']['type'] : '',
		'options' => $pager_types,
		'class' => array( 'qw-js-title' ),
	) );

	?>
	<p class="description"><?php _e( 'Use the following options to change the Default Pager labels.' ); ?></p>
	<?php

	print $form->render_field( array(
		'type' => 'text',
		'name_prefix' => '[page][pager]',
		'name' => 'previous',
		'title' => __( 'Previous Page Label' ),
		'value' => isset( $display['page']['pager']['previous'] ) ? $display['page']['pager']['previous'] : '',
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name_prefix' => '[page][pager]',
		'name' => 'next',
		'title' => __( 'Next Page Label' ),
		'value' => isset( $display['page']['pager']['next'] ) ? $display['page']['pager']['next'] : '',
		'class' => array( 'qw-js-title' ),
	) );

	/*

	print $form->render_field( array(
		'type' => 'checkbox',
		'name_prefix' => '[page][pager]',
		'name' => '',
		'title' => __( 'Use pager key' ),
		'description' => __( 'Use this if you need multiple paginating queries on a single page.' ),
		'value' => isset( $display['page']['pager']['use_pager_key'] ) ? $display['page']['pager']['use_pager_key']: 0,
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name_prefix' => '[page][pager]',
		'name' => 'pager_key',
		'title' => __( 'Pager Key' ),
		'description' => __( 'Pager key should a unique string of lowercase characters with underscores. No spaces.' ),
		'value' => isset( $display['page']['pager']['pager_key'] ) ? $display['page']['pager']['pager_key'] : '',
		'class' => array( 'qw-js-title' ),
	) );
	// */
}

/*
 * Custom Pager function
 *
 * @param array $pager Query pager details
 * @param object $qw_query Object
 * @return HTML processed pager
 */
function qw_make_pager( $pager, &$qw_query ) {
	$pager_themed = '';
	$pagers       = qw_all_pager_types();

	//set callback if function exists
	if ( function_exists( $pagers[ $pager['type'] ]['callback'] ) ) {
		$callback = $pagers[ $pager['type'] ]['callback'];
	} else {
		$callback = $pagers['default']['callback'];
	}

	// execute callback
	$pager_themed = call_user_func_array( $callback,
		array( $pager, $qw_query ) );

	return $pager_themed;
}

/*
 * Custom Default Pager
 *
 * @param array $pager Query options for pager
 * @param object $qw_query Object
 */
function qw_theme_pager_default( $pager, &$qw_query ) {
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

	if ( $page = qw_get_page_number( $qw_query ) ) {
		$path = rtrim( $path_array[0], '/' );

		$wpurl = get_bloginfo( 'wpurl' );

		// previous link with page number
		if ( $page >= 3 ) {
			$url = $wpurl . $path . '/page/' . ( $page - 1 );
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}
			$pager_themed .= '<div class="query-prevpage">
                        <a href="' . $url . '">' . $pager['previous'] . '</a>
                      </div>';
		} // previous link with no page number
		else if ( $page == 2 ) {
			$url = $wpurl . $path;
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}
			$pager_themed .= '<div class="query-prevpage">
                        <a href="' . $url . '">' . $pager['previous'] . '</a>
                      </div>';
		}

		// next link
		if ( ( $page + 1 ) <= $qw_query->max_num_pages ) {
			$url = $wpurl . $path . '/page/' . ( $page + 1 );
			if ( $exposed_path ) {
				$url .= '?' . $exposed_path;
			}
			$pager_themed .= '<div class="query-nextpage">
                        <a href="' . $url . '">' . $pager['next'] . '</a>
                      </div>';
		}

		return $pager_themed;
	}
}

/*
 * Default Pager with page numbers
 *
 * @param array $pager Query options for pager
 * @param object $qw_query Object
 *
 * @return string HTML for pager
 */
function qw_theme_pager_numbers( $pager, $qw_query ) {
	$big          = intval( $qw_query->found_posts . '000' );
	$args         = array(
		'base'    => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
		'format'  => '?paged=%#%',
		'current' => max( 1, qw_get_page_number( $qw_query ) ),
		'total'   => $qw_query->max_num_pages
	);
	$pager_themed = paginate_links( $args );

	return $pager_themed;
}

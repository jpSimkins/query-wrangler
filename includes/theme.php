<?php

/**
 * Template Wrangler templates
 *
 * @param $templates array Passed from the filter hook from WP
 *
 * @return array All template arrays filtered so far by Wordpress' filter hook
 */
function qw_templates( $templates ) {

	// display queries style wrapper
	$templates['query_display_wrapper'] = array(
		'files'        => array(
			'query-wrapper-[slug].php',
			'query-wrapper.php',
			'templates/query-wrapper.php',
		),
		'default_path' => QW_PLUGIN_DIR . '/templates',
		'arguments'    => array(
			'slug'    => '',
			'options' => array(),
		)
	);
	// full and field styles
	$templates['query_display_rows'] = array(
		'files'        => array(
			'[template]-[slug].php',
			'[template].php',
			'templates/[template].php',
		),
		'default_path' => QW_PLUGIN_DIR,
		'arguments'    => array(
			'template' => 'query-unformatted',
			'slug'     => 'not-found',
			'style'    => 'unformatted',
			'rows'     => array(),
		)
	);

	return $templates;
}

// tw hook
add_filter( 'tw_templates', 'qw_templates' );

/*
 * Preprocess query_display_rows to allow field styles to define their own default path
 */
function theme_query_display_rows_preprocess( $template ) {
	// make sure we know what style to use
	if ( isset( $template['arguments']['style'] ) ) {
		// get the specific style
		$all_styles = qw_all_styles();

		// set this template's default path to the style's default path
		if ( isset( $all_styles[ $template['arguments']['style'] ] ) ) {
			$style                    = $all_styles[ $template['arguments']['style'] ];
			$template['default_path'] = $style['default_path'];
		}

		//if(isset($all_styles[$template['preprocess_callback']])){
		//  $template['preprocess_callback'] = $all_styles[$template['preprocess_callback']];
		//}
	}

	return $template;
}

/*
 * Preprocess query_display_syle to allow field styles to define their own default path
 */
function theme_query_display_style_preprocess( $template ) {
	$all_styles = qw_all_styles();
	// make sure we know what style to use
	if ( isset( $all_styles[ $template['arguments']['style'] ] ) ) {
		// get the specific style
		$style = $all_styles[ $template['arguments']['style'] ];
		// set this template's default path to the style's default path
		if ( ! empty( $style['default_path'] ) ) {
			$template['default_path'] = $style['default_path'];
		}
	}

	return $template;
}

/**
 * Template the entire query
 *
 * @param object $wp_query WordPress query object
 * @param array $options the query options
 *
 * @return string HTML for themed/templated query
 */
function qw_template_query( &$wp_query, $options ) {
	$results_count = count( $wp_query->posts );
	$options['meta']['results_count'] = $results_count;

	// start building theme arguments
	$wrapper_args = array(
		'slug'    => $options['meta']['slug'],
		'options' => $options,
	);

	// look for empty results
	if ( $results_count > 0 ) {
		$all_styles = qw_all_styles();

		$style = $all_styles[ $options['display']['style'] ];
		$style['settings'] = array();

		if ( isset( $style['settings_key'], $options['display'][ $style['settings_key'] ] ) ) {
			$style['settings'] = $options['display'][ $style['settings_key'] ];
		}

		// setup row template arguments
		$template_args = array(
			'template' => 'query-' . $style['hook_key'],
			'slug'     => $options['meta']['slug'],
			'style'    => $style['hook_key'],
			'options'  => $options,
			'style_settings' => $style['settings'],
		);

		$row_styles = qw_all_row_styles();
		$row_style = $row_styles[ $options['display']['row_style'] ];

		if ( is_callable( $row_style['make_rows_callback'] ) ) {
			$template_args['rows'] = call_user_func( $row_style['make_rows_callback'], $wp_query, $options );
		}

		// template the query rows
		$wrapper_args['content'] = theme( 'query_display_rows', $template_args );
	}
	// empty results
	else {
		// no pagination
		$options['meta']['pagination'] = FALSE;
		// show empty text
		$wrapper_args['content'] = '<div class="query-empty">' . $options['meta']['empty'] . '</div>';
	}

	$wrapper_classes   = array();
	$wrapper_classes[] = 'query';
	$wrapper_classes[] = 'query-' . $options['meta']['slug'] . '-wrapper';
	$wrapper_classes[] = $options['display']['wrapper-classes'];

	$wrapper_args['wrapper_classes'] = implode( " ", $wrapper_classes );

	// header
	if ( $options['meta']['header'] != '' ) {
		$wrapper_args['header'] = $options['meta']['header'];
	}
	// footer
	if ( $options['meta']['footer'] != '' ) {
		$wrapper_args['footer'] = $options['meta']['footer'];
	}

	// pagination
	if ( $options['meta']['pagination'] && isset( $options['display']['page']['pager']['active'] ) ) {
		$pager_classes   = array();
		$pager_classes[] = 'query-pager';
		$pager_classes[] = 'pager-' . $options['display']['page']['pager']['type'];

		$wrapper_args['pager_classes'] = implode( " ", $pager_classes );
		// pager
		$wrapper_args['pager'] = qw_make_pager( $options['display']['page']['pager']['type'], $wp_query, $options['display'] );
	}

	// exposed filters
	$exposed = qw_generate_exposed_handlers( $options );
	if ( ! empty( $exposed ) ) {
		$wrapper_args['exposed'] = $exposed;
	}

	// template with wrapper
	return theme( 'query_display_wrapper', $wrapper_args );
}

/**
 * Convert multi-dimensional groups of rows into single-dimension of rows
 *
 * @param $groups
 * @param $group_by_field_name
 *
 * @return array
 */
function qw_make_groups_rows( $groups, $group_by_field_name = NULL ) {
	$rows = array();

	if ( ! empty( $groups ) ) {
		foreach ( $groups as $group ) {
			$first_row = reset( $group );

			// group row
			if ( $group_by_field_name && isset( $first_row['fields'][ $group_by_field_name ] ) ) {

				// create the row that acts as the group header
				$rows[] = array(
					'row_classes' => 'query-group-row',
					'fields'      => array(
						$group_by_field_name => array(
							'classes' => 'query-group-row-field',
							'output'  => $first_row['fields'][ $group_by_field_name ]['content']
						),
					),
				);
			}

			foreach ( $group as $row ) {
				$rows[] = $row;
			}
		}
	}

	return $rows;
}

/*
 * Make theme row classes
 */
function qw_row_classes( $i, $last_row ) {
	$row_classes   = array( 'query-row' );
	$row_classes[] = ( $i % 2 ) ? 'query-row-odd' : 'query-row-even';
	$row_classes[] = 'query-row-' . $i;

	if ( $i === 0 ){
		$row_classes[] = 'query-row-first';
	}
	else if ( $i === $last_row ){
		$row_classes[] = 'query-row-last';
	}

	return implode( " ", $row_classes );
}


/*
 * Turn a list of images into html
 *
 * @param $post
 * @param $field
 */
function qw_theme_featured_image( $post, $field ) {
	$style = $field['image_display_style'];
	if ( has_post_thumbnail( $post->ID ) ) {
		$image_id = get_post_thumbnail_id( $post->ID );

		return wp_get_attachment_image( $image_id, $style );
	}
}


/**
 * Get and theme attached post files
 *
 * @param $post
 * @param $field
 * @return string
 */
function qw_theme_file( $post, $field ) {
	$style = ( $field['file_display_style'] ) ? $field['file_display_style'] : 'link';
	$count = ( $field['file_display_count'] ) ? $field['file_display_count'] : 0;

	$files = qw_get_post_files( $post->ID );
	if ( is_array( $files ) ) {
		$output = array();
		$i      = 0;
		foreach ( $files as $file ) {
			if ( ( $count == 0 || ( $i < $count ) ) && substr( $file->post_mime_type,
							0,
							5 ) != "image"
			) {
				switch ( $style ) {
					case 'url':
						$output[] = wp_get_attachment_url( $file->ID );
						break;

					case 'link':
						// complete file name
						$file_name = explode( "/", $file->guid );
						$file_name = $file_name[ count( $file_name ) - 1 ];
						$output[] = '<a href="' . wp_get_attachment_url( $file->ID ) . '" class="query-file-link">' . $file_name . '</a>';
						break;

					case 'link_url':
						$output[] = '<a href="' . wp_get_attachment_url( $file->ID ) . '" class="query-file-link">' . $file->guid . '</a>';
						break;
				}
			}
			$i ++;
		}

		return "<span class='qw-file-attachment'>".implode( "</span><span class='qw-file-attachment'>", $output ) ."</span>";
	}
}

/**
 * Turn a list of images into html
 *
 * @param $post
 * @param $field
 *
 * @return null|string
 */
function qw_theme_image( $post, $field ) {
	$style             = $field['image_display_style'];
	$count             = $field['image_display_count'];
	$featured_image_id = isset( $field['featured_image'] ) ? get_post_thumbnail_id( $post->ID ) : NULL;
	$images            = qw_get_post_images( $post->ID );

	if ( is_array( $images ) ) {
		$output = '';
		$i      = 0;
		foreach ( $images as $image ) {
			if ( $featured_image_id ) {
				if ( $image->ID == $featured_image_id ) {
					$output .= wp_get_attachment_image( $image->ID, $style );
				}

			} else {
				if ( $count == 0 || ( $i < $count ) ) {
					$output .= wp_get_attachment_image( $image->ID, $style );
				}
			}
			$i ++;
		}

		return $output;
	}
}

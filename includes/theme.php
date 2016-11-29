<?php
/*
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
		'default_path' => QW_PLUGIN_DIR,
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

/*
 * Template the entire query
 *
 * @param object
 *   $qw_query Wordpress query object
 * @param array
 *   $options the query options
 *
 * @return string HTML for themed/templated query
 */
function qw_template_query( &$qw_query, $options ) {
	$results_count                    = count( $qw_query->posts );
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

		// setup row template arguments
		$template_args = array(
			'template' => 'query-' . $style['hook_key'],
			'slug'     => $options['meta']['slug'],
			'style'    => $style['hook_key'],
			'options'  => $options,
		);

		if ( isset( $options['display'][ $style['settings_key'] ] ) ) {
			$template_args['style_settings'] = $options['display'][ $style['settings_key'] ];
		}

		// the content of the widget is the result of the query
		if ( $options['display']['row_style'] == "posts" ) {
			$template_args['rows'] = qw_make_posts_rows( $qw_query, $options );
		}
		// setup row template arguments
		else if ( $options['display']['row_style'] == "fields" ) {
			$template_args['rows'] = qw_make_fields_rows( $qw_query, $options );
		}
		// template_part rows
		else if ( $options['display']['row_style'] == "template_part" ) {
			$template_args['rows'] = qw_make_template_part_rows( $qw_query, $options );
		}

		// template the query rows
		$wrapper_args['content'] = theme( 'query_display_rows',
			$template_args );
	} // empty results
	else {
		// no pagination
		$options['meta']['pagination'] = FALSE;
		// show empty text
		$wrapper_args['content'] = '<div class="query-empty">' . $options['meta']['empty'] . '</div>';
	}

	$wrapper_classes                 = array();
	$wrapper_classes[]               = 'query';
	$wrapper_classes[]               = 'query-' . $options['meta']['slug'] . '-wrapper';
	$wrapper_classes[]               = $options['display']['wrapper-classes'];
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
		$pager_classes                 = array();
		$pager_classes[]               = 'query-pager';
		$pager_classes[]               = 'pager-' . $options['display']['page']['pager']['type'];
		$wrapper_args['pager_classes'] = implode( " ", $pager_classes );
		// pager
		$wrapper_args['pager'] = qw_make_pager( $options['display']['page']['pager'],
			$qw_query );
	}

	// exposed filters
	$exposed = qw_generate_exposed_handlers( $options );
	if ( ! empty( $exposed ) ) {
		$wrapper_args['exposed'] = $exposed;
	}

	// template with wrapper
	return theme( 'query_display_wrapper', $wrapper_args );
}

/*
 *
 */
function qw_make_posts_rows( &$qw_query, $options ) {
	$groups          = array();
	$i               = 0;
	$current_post_id = get_the_ID();
	$last_row = $qw_query->post_count - 1;

	while ( $qw_query->have_posts() ) {
		$qw_query->the_post();
		$template_args = array(
			'template' => 'query-' . $options['display']['post_settings']['size'],
			'slug'     => $options['meta']['slug'],
			'style'    => $options['display']['post_settings']['size'],
		);

		$row           = array(
			'row_classes' => qw_row_classes( $i, $last_row ),
		);
		$field_classes = array( 'query-post-wrapper' );

		// add class for active menu trail
		if ( is_singular() && get_the_ID() === $current_post_id ) {
			$field_classes[] = 'active-item';
		}

		$row['fields'][ $i ]['classes'] = implode( " ", $field_classes );
		$row['fields'][ $i ]['output']  = theme( 'query_display_rows',
			$template_args );
		$row['fields'][ $i ]['content'] = $row['fields'][ $i ]['output'];

		// can't really group posts row style
		$groups[ $i ][ $i ] = $row;
		$i ++;
	}

	$rows = qw_make_groups_rows( $groups );

	return $rows;
}

/*
 *
 */
function qw_make_template_part_rows( &$qw_query, $options ) {
	$groups          = array();
	$i               = 0;
	$current_post_id = get_the_ID();
	$last_row = $qw_query->post_count - 1;

	while ( $qw_query->have_posts() ) {
		$qw_query->the_post();
		$path = $options['display']['template_part_settings']['path'];
		$name = $options['display']['template_part_settings']['name'];

		$row = array(
			'row_classes' => qw_row_classes( $i, $last_row ),
		);
		$field_classes = array( 'query-post-wrapper' );

		// add class for active menu trail
		if ( is_singular() && get_the_ID() === $current_post_id ) {
			$field_classes[] = 'active-item';
		}

		ob_start();
			get_template_part( $path, $name );
		$output = ob_get_clean();

		$row['fields'][ $i ]['classes'] = implode( " ", $field_classes );
		$row['fields'][ $i ]['output'] = $output;
		$row['fields'][ $i ]['content'] = $row['fields'][ $i ]['output'];

		// can't really group posts row style
		$groups[ $i ][ $i ] = $row;
		$i ++;
	}

	$rows = qw_make_groups_rows( $groups );

	return $rows;
}

/*
 * Build array of fields and rows for templating
 *
 * @param object $new_query WP_Query object generated
 * @param array $display Query display data
 * @return array Executed query rows
 */
function qw_make_fields_rows( &$qw_query, $options ) {
	$display         = $options['display'];
	$all_fields      = qw_all_fields();
	$groups          = array();
	$tokens          = array();
	$current_post_id = get_the_ID();

	// the query needs fields
	if ( empty( $display['field_settings']['fields'] ) || ! is_array( $display['field_settings']['fields'] ) ) {
		return array();
	}

	// sort according to weights
	uasort( $display['field_settings']['fields'], 'qw_cmp' );

	// look for selected group by field
	$group_by_field_name = NULL;
	if ( isset( $display['field_settings']['group_by_field'] ) ) {
		$group_by_field_name = $display['field_settings']['group_by_field'];
	}

	// loop through each post
	$last_row = $qw_query->post_count - 1;
	$i = 0;
	while ( $qw_query->have_posts() ) {
		$qw_query->the_post();
		//
		$this_post = $qw_query->post;
		$row       = array(
			'row_classes' => qw_row_classes( $i, $last_row ),
		);

		// loop through each field
		foreach ( $display['field_settings']['fields'] as $field_name => $field_settings ) {
			if ( ! isset( $field_settings['empty_field_content_enabled'] ) ) {
				$field_settings['empty_field_content_enabled'] = FALSE;
				$field_settings['empty_field_content']         = '';
			}

			// field open
			$field_classes   = array( 'query-field' );
			$field_classes[] = 'query-field-' . $field_settings['name'];

			// add class for active menu trail
			if ( is_singular() && get_the_ID() === $current_post_id ) {
				$field_classes[] = 'active-item';
			}

			// add additional classes defined in the field settings
			if ( isset( $field_settings['classes'] ) && ! empty( $field_settings['classes'] ) ) {
				$field_classes[] = $field_settings['classes'];
			}

			$row['fields'][ $field_name ]['output']  = '';
			$row['fields'][ $field_name ]['classes'] = implode( " ",
				$field_classes );

			// get field details from all fields list
			$hook_key       = qw_get_hook_key( $all_fields, $field_settings );
			$field_defaults = $all_fields[ $hook_key ];

			// merge default data with values
			$field = array_merge( $field_defaults, $field_settings );

			// look for callback
			if ( isset( $field_defaults['output_callback'] ) && is_callable( $field_defaults['output_callback'] ) ) {
				// callbacks with token arguments
				if ( isset( $field_defaults['output_arguments'] ) ) {
					$row['fields'][ $field_name ]['output'] .= call_user_func( $field_defaults['output_callback'], $this_post, $field, $tokens );
				}
				// normal callback w/o arguments
				else {
					$row['fields'][ $field_name ]['output'] .= call_user_func( $field_defaults['output_callback'] );
				}
			} // use field itself
			else {
				$row['fields'][ $field_name ]['output'] .= $this_post->{$field_settings['type']};
			}

			// remember if any value was found
			$row['is_empty'] = empty( $row['fields'][ $field_name ]['output'] );

			if ( $row['is_empty'] &&
			     $field_settings['empty_field_content_enabled'] &&
			     $field_settings['empty_field_content']
			) {
				$row['fields'][ $field_name ]['output'] = $field_settings['empty_field_content'];
				$row['is_empty']                        = FALSE;
			}

			// add token for replace
			$tokens[ '{{' . $field_name . '}}' ] = $row['fields'][ $field_name ]['output'];

			// look for rewrite output
			if ( !empty( $field_settings['rewrite_output'] ) ) {
				// replace tokens with results
				$field_settings['custom_output']        = str_replace( array_keys( $tokens ),
					array_values( $tokens ),
					$field_settings['custom_output'] );
				$row['fields'][ $field_name ]['output'] = $field_settings['custom_output'];
			}

			// apply link to field
			if ( !empty( $field_settings['link'] ) ) {
				$row['fields'][ $field_name ]['output'] = '<a class="query-field-link" href="' . get_permalink() . '">' . $row['fields'][ $field_name ]['output'] . '</a>';
			}

			// get default field label for tables
			$row['fields'][ $field_name ]['label'] = isset( $field_settings['label'] ) ? $field_settings['label'] : '';

			// apply labels to full style fields
			if ( !empty( $field_settings['has_label'] ) &&
			     //$display['type'] != 'full' &&
			     $display['style'] != 'table'
			) {
				$row['fields'][ $field_name ]['output'] = '<label class="query-label">' . $field_settings['label'] . '</label> ' . $row['fields'][ $field_name ]['output'];
			}

			// the_content filter
			if ( !empty( $field_settings['apply_the_content'] ) ) {
				$row['fields'][ $field_name ]['output'] = apply_filters( 'the_content', $row['fields'][ $field_name ]['output'] );
			}
			else {
				// apply shortcodes to field output
				$row['fields'][ $field_name ]['output'] = do_shortcode( $row['fields'][ $field_name ]['output'] );
			}

			// save a copy of the field output in case it is excluded, but we need it later
			$row['fields'][ $field_name ]['content'] = $row['fields'][ $field_name ]['output'];

			// hide if empty
			$row['hide'] = ( !empty( $field_settings['hide_if_empty'] ) && $row['is_empty'] );

			// after all operations, remove if excluded
			if ( !empty( $field_settings['exclude_display'] ) || $row['hide'] ) {
				unset( $row['fields'][ $field_name ]['output'] );
			}
		}

		// default group by data
		$group_hash = md5( $i );

		// if set, hash the output of the group_by_field
		if ( $group_by_field_name && isset( $row['fields'][ $group_by_field_name ] ) ) {
			$group_hash = md5( $row['fields'][ $group_by_field_name ]['content'] );
		}

		$groups[ $group_hash ][ $i ] = $row;

		// increment row
		$i ++;
	}

	$rows = qw_make_groups_rows( $groups, $group_by_field_name );

	return $rows;
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
	if ( is_callable( $pagers[ $pager['type'] ]['callback'] ) ) {
		$callback = $pagers[ $pager['type'] ]['callback'];
	} else {
		$callback = $pagers['default']['callback'];
	}

	// execute callback
	$pager_themed = call_user_func( $callback, $pager, $qw_query );

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

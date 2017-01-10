<?php

// template wrangler hook
add_filter( 'tw_templates', 'qw_templates' );

add_filter( 'tw_pre_process_template', 'tw_pre_process_template_display_style', 0 );

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

/**
 * Filter implements - tw_pre_process_template from template_wrangler
 *
 * Process query_display_rows to allow display styles to define their own
 * default path
 *
 * @param $template
 *
 * @return array
 */
function tw_pre_process_template_display_style( $template ) {
	// make sure we know what style to use
	if ( !empty( $template['arguments']['style'] ) )
	{
		// get the specific style
		$all_styles = qw_all_styles();

		// set this template's default path to the style's default path
		if ( !empty( $all_styles[ $template['arguments']['style'] ] ) )
		{
			$style = $all_styles[ $template['arguments']['style'] ];
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
	$options['meta']['results_count'] = count( $wp_query->posts );

	/*
	 * Template arguments are delivered to the appropriate "style" template
	 */
	$template_args = array(
		'slug'     => $options['meta']['slug'],
		'options'  => $options,
		'rows'     => array(),
	);

	// allow items to manage their own template arguments
	$template_args = apply_filters( 'qw_template_query_template_args', $template_args, $wp_query, $options );

	/*
	 * Wrapper arguments are delivered to query-wrapper template
	 */
	$wrapper_args = array(
		'slug'    => $options['meta']['slug'],
		'options' => $options,
		'content' => theme( 'query_display_rows', $template_args ),
		'classes' => array(
			'query',
			"query-{$options['meta']['slug']}-wrapper",
			$options['display']['wrapper-classes'],
		),
	);

	$wrapper_args = apply_filters( 'qw_template_query_wrapper_args', $wrapper_args, $wp_query, $options );

	$wrapper_args['wrapper_classes'] = implode( " ", $wrapper_args['classes'] );

	// exposed filters
	$exposed = qw_generate_exposed_handlers( $options );
	if ( ! empty( $exposed ) ) {
		$wrapper_args['exposed'] = $exposed;
	}

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

/**
 * Make theme row classes
 *
 * @param $i
 * @param $last_row
 *
 * @return string
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

<?php

add_filter( 'qw_row_styles', 'qw_row_style_fields', 0 );

/**
 * Row style that renders each field attached to a query
 *
 * @param $row_styles
 *
 * @return array
 */
function qw_row_style_fields( $row_styles )
{
	$row_styles['fields'] = array(
		'title'             => __( 'Fields' ),
		'settings_callback' => 'qw_row_style_fields_settings',
		'settings_key'      => 'field_settings',
		'make_rows_callback'=> 'qw_row_style_fields_make_rows',
	);

	return $row_styles;
}

/**
 * Additional settings for form this row_style
 *
 * @param $row_style
 * @param $options
 * @param $handler_item_type
 */
function qw_row_style_fields_settings( $row_style, $options, $handler_item_type )
{
	$manager = new QW_Handler_Manager();
	$all_fields = $manager->get('field')->handler_item_types();

	$query_fields = !empty( $options['field'] ) ? $options['field'] : array();

	$group_by_options = array(
		'__none__' => __( '- None -' ),
	);
	foreach( $query_fields as $field_name => $field ){
		$group_by_options[ $field_name ] = $all_fields[ $field['hook_key'] ]['title'] . ' - ' . $field_name;
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => "{$handler_item_type['form_prefix']}[{$row_style['settings_key']}]",
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'group_by_field',
		'title' => __( 'Group by field' ),
		'value' => !empty( $row_style['values']['group_by_field'] ) ? $row_style['values']['group_by_field'] : '',
		'options' => $group_by_options,
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'checkbox',
		'name' => 'strip_group_by_field',
		'title' => __( 'Strip tags in Group by field' ),
		'value' => !empty( $row_style['values']['strip_group_by_field'] ) ? $row_style['values']['strip_group_by_field'] : '',
		'options' => $group_by_options,
		'class' => array( 'qw-js-title' ),
	) );
}


/**
 * Build array of fields and rows for templating
 *
 * @param $wp_query
 * @param $options
 *
 * @return array Executed query rows
 */
function qw_row_style_fields_make_rows( $wp_query, $options )
{
	$manager = new QW_Handler_Manager();
	$all_fields = $manager->get('field')->handler_item_types();
	$groups          = array();
	$tokens          = array();
	$current_post_id = get_the_ID();

	// the query needs fields
	if ( empty( $options['field'] ) || ! is_array( $options['field'] ) ) {
		return array();
	}

	// sort according to weights
	uasort( $options['field'], 'qw_cmp' );

	// look for selected group by field
	$group_by_field_name = NULL;
	if ( isset( $options['basic']['row_style']['field_settings']['group_by_field'] ) ) {
		$group_by_field_name = $options['basic']['row_style']['field_settings']['group_by_field'];
	}

	// loop through each post
	$last_row = $wp_query->post_count - 1;
	$i = 0;
	while ( $wp_query->have_posts() ) {
		$wp_query->the_post();
		//
		$this_post = $wp_query->post;
		$row       = array(
			'row_classes' => qw_row_classes( $i, $last_row ),
		);

		// loop through each field
		foreach ( $options['field'] as $field_name => $field_settings ) {
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

			// add token for initial replacement
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
			     $options['style'] != 'table' )
			{
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

			// update the token for replacement by later fields
			$tokens[ '{{' . $field_name . '}}' ] = $row['fields'][ $field_name ]['output'];

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
		if ( $group_by_field_name && isset( $row['fields'][ $group_by_field_name ] ) )
		{
			// strip tags from group by field
			if ( !empty( $options['field_settings']['strip_group_by_field'] ) ) {
				$row['fields'][ $group_by_field_name ]['content'] = strip_tags( $row['fields'][ $group_by_field_name ]['content'] );
			}
			$group_by_field_content = $row['fields'][ $group_by_field_name ]['content'];
			$group_hash = md5( $group_by_field_content );
		}

		$groups[ $group_hash ][ $i ] = $row;

		// increment row
		$i ++;
	}

	$rows = qw_make_groups_rows( $groups, $group_by_field_name );

	return $rows;
}

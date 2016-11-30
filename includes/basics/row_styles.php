<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_row_style' );

// add default field styles to the filter
add_filter( 'qw_row_styles', 'qw_default_row_styles', 0 );

// add row complete (post rows) styles
add_filter( 'qw_row_complete_styles', 'qw_default_row_complete_styles', 0 );

/*
 * Basic Settings
 */
function qw_basic_settings_row_style( $basics ) {
	$basics['display_row_style'] = array(
		'title'         => __( 'Row Style' ),
		'description'   => __( 'How should each post in this query be presented?' ),
		'option_type'   => 'display',
		'form_callback' => 'qw_basic_display_row_style_form',
		'weight'        => 0,
		'required'      => true,
	);

	return $basics;
}

/**
 * Default Row Styles
 *
 * @param $row_styles
 * @return mixed
 */
function qw_default_row_styles( $row_styles ) {
	$row_styles['posts']  = array(
		'title'             => __( 'Posts' ),
		'settings_callback' => 'qw_row_style_posts_settings',
		'settings_key'      => 'post',
	);
	$row_styles['fields'] = array(
		'title'             => __( 'Fields' ),
		'settings_callback' => 'qw_row_style_fields_settings',
		'settings_key'      => 'field',
	);
	$row_styles['template_part'] = array(
		'title'             => __( 'Template Part' ),
		'settings_callback' => 'qw_row_style_template_part_settings',
		'settings_key'      => 'template_part',
	);

	return $row_styles;
}


/**
 * Default Row 'Posts' Styles
 *
 * @param $row_complete_styles
 * @return array
 */
function qw_default_row_complete_styles( $row_complete_styles ) {
	$row_complete_styles['complete'] = array(
		'title' => __( 'Complete Post' ),
	);
	$row_complete_styles['excerpt']  = array(
		'title' => __( 'Excerpt' ),
	);

	return $row_complete_styles;
}

/**
 * @param $item
 * @param $display
 */
function qw_basic_display_row_style_form( $item, $display ) {
	$row_styles = array();
	foreach ( qw_all_row_styles() as $key => $details ) {
		$row_styles[ $key ] = $details['title'];
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'row_style',
		'description' => $item['description'],
		'value' => isset( $display['row_style'] ) ? $display['row_style'] : '',
		'options' => $row_styles,
		'class' => array( 'qw-js-title', 'qw-select-group-toggle' ),
	) );
	?>

	<!-- style settings -->
	<p class="description"><?php _e( 'Some Row Styles have additional settings.' ); ?></p>
	<div id="row-style-settings">
		<?php
		foreach ( qw_all_row_styles() as $type => $row_style ) {
			if ( isset( $row_style['settings_callback'] ) && is_callable( $row_style['settings_callback'] ) ) {
				$row_style['values'] = ( isset( $row_style['settings_key'] ) && isset( $display[ $row_style['settings_key'] . '_settings' ] ) ) ? $display[ $row_style['settings_key'] . '_settings' ] : array();
				?>
				<div id="tab-row-style-settings-<?php print $row_style['hook_key']; ?>"
				     class="qw-query-content qw-select-group-item qw-select-group-value-<?php print $type; ?>">
					<h3><?php print $row_style['title']; ?> <?php _e( 'Settings' ); ?></h3>

					<div class="qw-setting-group-inner">
						<?php print call_user_func( $row_style['settings_callback'], $row_style, $display ); ?>
					</div>
				</div>
			<?php
			}
		}
		?>
	</div>
<?php
}

/**
 * @param $row_style
 * @param $display
 */
function qw_row_style_posts_settings( $row_style, $display ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => QW_FORM_PREFIX . '[display][post_settings]',
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'size',
		'description' => __( 'Select the amount of the post to be shown.' ),
		'value' => isset( $row_style['values']['size'] ) ? $row_style['values']['size'] : '',
		'options' => array(
			'complete' => __( 'Complete Post' ),
			'excerpt' => __( 'Excerpt' ),
		),
		'class' => array( 'qw-js-title' ),
	) );
}

/**
 * @param $row_style
 * @param $display
 */
function qw_row_style_fields_settings( $row_style, $display ) {
	$query_fields = isset( $display['field_settings']['fields'] ) ? $display['field_settings']['fields'] : array();
	$all_fields   = qw_all_fields();

	$group_by_options = array(
		'__none__' => __( '- None -' ),
	);
	foreach( $query_fields as $field_name => $field ){
		$group_by_options[ $field_name ] = $all_fields[ $field['hook_key'] ]['title'] . ' - ' . $field_name;
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => QW_FORM_PREFIX . '[display][field_settings]',
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'group_by_field',
		'title' => __( 'Group by field' ),
		'value' => isset( $row_style['values']['group_by_field'] ) ? $row_style['values']['group_by_field'] : '',
		'options' => $group_by_options,
		'class' => array( 'qw-js-title' ),
	) );
}

/**
 * @param $row_style
 * @param $display
 */
function qw_row_style_template_part_settings( $row_style, $display ) {
	$form = new QW_Form_Fields( array(
		'form_field_prefix' => QW_FORM_PREFIX . '[display][template_part_settings]',
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'path',
		'title' => __( 'Path' ),
		'value' => isset( $row_style['values']['path'] ) ? $row_style['values']['path'] : '',
		'class' => array( 'qw-js-title' ),
	) );
	print $form->render_field( array(
		'type' => 'text',
		'name' => 'name',
		'title' => __( 'Name' ),
		'value' => isset( $row_style['values']['name'] ) ? $row_style['values']['name'] : '',
		'class' => array( 'qw-js-title' ),
	) );
}

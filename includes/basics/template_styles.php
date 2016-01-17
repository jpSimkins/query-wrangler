<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_style' );
// add default template styles to the hook
add_filter( 'qw_styles', 'qw_template_styles_default' );

/*
 * Styles with settings
 */
function qw_basic_settings_style( $basics ) {
	$basics['style'] = array(
		'title'         => __( 'Template Style' ),
		'description'   => __( 'How this query should be styled' ),
		'option_type'   => 'display',
		'form_callback' => 'qw_basic_display_style_form',
		'weight'        => 0,
	);

	return $basics;
}

/**
 * All Field Styles and settings
 *
 * @return array Field Styles
 */
function qw_template_styles_default( $styles ) {
	$styles['unformatted']    = array(
		'title'        => __( 'Unformatted' ),
		'template'     => 'query-unformatted',
		'default_path' => QW_PLUGIN_DIR, // do not include last slash
	);
	$styles['unordered_list'] = array(
		'title'        => __( 'Unordered List' ),
		'template'     => 'query-unordered_list',
		'default_path' => QW_PLUGIN_DIR, // do not include last slash
	);
	$styles['ordered_list']   = array(
		'title'        => __( 'Ordered List' ),
		'template'     => 'query-ordered_list',
		'default_path' => QW_PLUGIN_DIR, // do not include last slash
	);
	$styles['table']          = array(
		'title'        => __( 'Table' ),
		'template'     => 'query-table',
		'default_path' => QW_PLUGIN_DIR, // do not include last slash
	);

	return $styles;
}

/**
 * @param $item
 * @param $display
 */
function qw_basic_display_style_form( $item, $display ) {
	$styles = array();
	foreach ( qw_all_styles() as $key => $details ) {
		$styles[ $key ] = $details['title'];
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $item['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'style',
		'description' => $item['description'],
		'value' => isset( $display['style'] ) ? $display['style'] : '',
		'options' => $styles,
		'class' => array( 'qw-js-title' ),
	) );
	?>

	<!-- style settings -->
	<div id="display-style-settings">
		<?php
		foreach ( $styles as $type => $style ) {
			if ( isset( $style['settings_callback'] ) && function_exists( $style['settings_callback'] ) ) {
				$style['values'] = $display[ $style['settings_key'] ];
				?>
				<div id="tab-style-settings-<?php print $style['hook_key']; ?>" class="qw-query-content">
					<span class="qw-setting-header"><?php print $style['title']; ?> <?php _e( 'Settings' ); ?></span>

					<div class="qw-setting-group">
						<?php print $style['settings_callback']( $style ); ?>
					</div>
				</div>
			<?php
			}
		}
		?>
	</div>
	<?php
}
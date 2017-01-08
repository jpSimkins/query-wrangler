<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_style' );

/*
 * Styles with settings
 */
function qw_basic_settings_style( $basics ) {
	$basics['style'] = array(
		'title'         => __( 'Wrapper Style' ),
		'description'   => __( 'How this query should be styled' ),
		'option_type'   => 'display',
		'form_callback' => 'qw_basic_display_style_form',
		'weight'        => 2,
		'required'      => true,
	);

	return $basics;
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

	print $form->render_field( array(
		'title' => __( 'Wrapper Classes' ),
		'description' => __( 'The CSS class names will be added to the query. This enables you to use specific CSS code for each query. You may define multiples classes separated by spaces.' ),
		'type' => 'text',
		'name' => 'wrapper-classes',
		'value' => isset( $display['wrapper-classes'] ) ? $display['wrapper-classes'] : '',
		'class' => array( 'qw-text-long', 'qw-js-title' ),
	) );
	?>

	<!-- style settings -->
	<div id="display-style-settings">
		<?php
		foreach ( $styles as $type => $style ) {
			if ( isset( $style['settings_callback'] ) && is_callable( $style['settings_callback'] ) ) {
				$style['values'] = $display[ $style['settings_key'] ];
				?>
				<div id="tab-style-settings-<?php print $style['hook_key']; ?>" class="qw-query-content">
					<span class="qw-setting-header"><?php print $style['title']; ?> <?php _e( 'Settings' ); ?></span>

					<div class="qw-setting-group">
						<?php print call_user_func( $style['settings_callback'], $style ); ?>
					</div>
				</div>
			<?php
			}
		}
		?>
	</div>
	<?php
}
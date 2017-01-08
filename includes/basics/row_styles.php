<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_basic_settings_row_style' );

/*
 * Basic Settings
 */
function qw_basic_settings_row_style( $basics ) {
	$basics['display_row_style'] = array(
		'title'         => __( 'Row Style' ),
		'description'   => __( 'How should each post in this query be presented?' ),
		'option_type'   => 'display',
		'form_callback' => 'qw_basic_display_row_style_form',
		'weight'        => 4,
		'required'      => true,
	);

	return $basics;
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

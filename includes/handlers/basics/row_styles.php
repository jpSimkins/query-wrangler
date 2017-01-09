<?php

add_filter( 'qw_basics', 'qw_basic_settings_row_style' );

/**
 * Basic Settings
 *
 * @param $basics
 *
 * @return array
 */
function qw_basic_settings_row_style( $basics )
{
	$basics['display_row_style'] = array(
		'title'         => __( 'Show' ),
		'description'   => __( 'How should each row in this query be presented?' ),
		'form_callback' => 'qw_basic_display_row_style_form',
		'weight'        => 3,
		'required'      => true,
	);

	return $basics;
}

/**
 * Callback to display row_styles selection form
 *
 * @param $item
 * @param $display
 */
function qw_basic_display_row_style_form( $item, $display )
{
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
	<div id="row-style-settings">
		<?php
		foreach ( qw_all_row_styles() as $row_style )
		{
			if ( isset( $row_style['settings_callback'] ) && is_callable( $row_style['settings_callback'] ) )
			{
				// get the current values saved to this query
				$row_style['values'] = array();

				if ( isset( $row_style['settings_key'] ) &&
				     isset( $display[ $row_style['settings_key'] ] ) )
				{
					$row_style['values'] =  $display[ $row_style['settings_key'] ];
				}
				?>
				<div id="tab-row-style-settings-<?php print $row_style['hook_key']; ?>"
				     class="qw-query-content qw-select-group-item qw-select-group-value-<?php print $row_style['hook_key']; ?>">
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

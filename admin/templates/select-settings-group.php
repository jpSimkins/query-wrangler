<?php
/**
 * $display - query display data
 * 
 * $items - array of associative arrays with the following values
 * 
 * item array(
 *      'settings_callback' => '',
 *      'settings_key' => '',
*      'hook_key' => '',
 *      'title' => '',
 * )
 */
?>
<div id="row-style-settings">
	<?php
	foreach ( $settings_group_options as $option_settings )
	{
		if ( isset( $option_settings['settings_callback'] ) && is_callable( $option_settings['settings_callback'] ) )
		{
			// get the current values saved to this query
			$option_settings['values'] = array();

			if ( isset( $option_settings['settings_key'] ) &&
			     isset( $display[ $option_settings['settings_key'] ] ) )
			{
				$option_settings['values'] =  $display[ $option_settings['settings_key'] ];
			}
			?>
			<div id="tab-row-style-settings-<?php print $option_settings['hook_key']; ?>"
			     class="qw-query-content qw-select-group-item qw-select-group-value-<?php print $option_settings['hook_key']; ?>">
				<h3><?php print $option_settings['title']; ?> <?php _e( 'Settings' ); ?></h3>

				<div class="qw-setting-group-inner">
					<?php print call_user_func( $option_settings['settings_callback'], $option_settings, $query_data, $handler_item_type ); ?>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>
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
	foreach ( $items as $item )
	{
		if ( isset( $item['settings_callback'] ) && is_callable( $item['settings_callback'] ) )
		{
			// get the current values saved to this query
			$item['values'] = array();

			if ( isset( $item['settings_key'] ) &&
			     isset( $display[ $item['settings_key'] ] ) )
			{
				$item['values'] =  $display[ $item['settings_key'] ];
			}
			?>
			<div id="tab-row-style-settings-<?php print $item['hook_key']; ?>"
			     class="qw-query-content qw-select-group-item qw-select-group-value-<?php print $item['hook_key']; ?>">
				<h3><?php print $item['title']; ?> <?php _e( 'Settings' ); ?></h3>

				<div class="qw-setting-group-inner">
					<?php print call_user_func( $item['settings_callback'], $item, $display ); ?>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>
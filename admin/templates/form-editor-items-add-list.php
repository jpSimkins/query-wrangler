<?php if ( !defined('QW_PLUGIN_DIR') ) exit; ?>

<!-- all <?php print $handler_type; ?>s -->
<div id="qw-display-add-<?php print $handler_type; ?>" class="qw-hidden"
     data-handler-type="<?php print $handler_type; ?>">
	<p class="description"><?php print $description; ?></p>

	<div class="qw-checkboxes">
		<?php
		// loop through sorts
		foreach ( $all_item_types as $hook_key => $item_type ) {
			// required items will already be on the page
			if ( !empty( $item_type['required'] ) ) {
				continue;
			}
			?>
			<label class="qw-sort-checkbox">
				<input type="checkbox"
				       value="<?php print $item_type['type']; ?>"/>
				<input class="qw-handler-hook_key"
				       type="hidden"
				       value="<?php print $item_type['hook_key']; ?>"/>
				<?php print $item_type['title']; ?>
			</label>
			<p class="description"><?php print $item_type['description']; ?></p>
			<?php
		}
		?>
	</div>
</div>

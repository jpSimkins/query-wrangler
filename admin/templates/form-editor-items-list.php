<?php if ( !defined('QW_PLUGIN_DIR') ) exit; ?>

<div id="qw-query-<?php print $handler_type; ?>" class="qw-query-admin-options">

	<?php if ( !empty( $add_rearrange ) ) : ?>
	<div class="qw-query-add-titles">
        <span class="qw-rearrange-title">
          <?php _e( 'Rearrange' ); ?>
        </span>
        <span class="qw-add-title" data-handler-type="<?php print $handler_type; ?>"
              data-form-id="qw-display-add-<?php print $handler_type; ?>"
              <?php if ( !empty( $limit_per_type ) ) { ?>
	              data-limit-per-type="<?php print $limit_per_type; ?>"
              <?php } ?>
        >
          <?php _e( 'Add' ); ?>
        </span>
	</div>
	<?php endif; ?>

	<h4><?php print $title; ?></h4>

	<div class="qw-clear-gone"><!-- ie hack -->&nbsp;</div>
	<div id="query-<?php print $handler_type; ?>" class="qw-handler-items">
		<?php
		foreach ( $items as $item )
		{
			if ( isset( $item['query_display_types'] ) && !in_array( $query_type, $item['query_display_types'] ) ){
				continue;
			}
			?>
			<div class="qw-handler-item">
				<div class="qw-handler-item-title"><?php print $item['title']; ?></div>
				<div class="qw-handler-item-form <?php if ( empty( $item['required'] ) ) { print "can-remove"; } ?>">
					<?php print $item['wrapper_form']; ?>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>
<!-- /<?php print $handler_type; ?>s -->
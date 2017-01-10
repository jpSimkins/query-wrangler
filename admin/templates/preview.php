<?php
$data = qw_get_edit_preview_data();
?>
<div id="preview-query"><?php echo $data['preview']; ?></div>
<div id="query-details">
	<div class="group">
		<div class="qw-setting-header"><?php _e( 'Query Settings' ); ?></div>
		<div id="qw-show-options-target">
			<!-- args -->
			<?php echo $data['options']; ?>
		</div>
	</div>
	<div class="group">
		<div class="qw-setting-header"><?php _e( 'PHP WP_Query' ); ?></div>
		<div id="qw-show-php_wpquery-target">
			<!-- php wp_query -->
			<?php echo $data['php_wpquery']; ?>
		</div>
	</div>
	<div class="group">
		<div class="qw-setting-header"><?php _e( 'Resulting WP_Query Object' ); ?></div>
		<div id="qw-show-wpquery-target">
			<!-- WP_Query -->
			<?php echo $data['wpquery']; ?>
		</div>
	</div>
	<div class="group">
		<div class="qw-setting-header"><?php _e( 'Template Suggestions' ); ?></div>
		<div id="qw-show-templates-target">
			<!-- templates -->
			<?php echo $data['templates']; ?>
		</div>
	</div>
</div>

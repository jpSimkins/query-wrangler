<?php

$args = qw_get_editor_args();
$query_id = $args['query_id'];
$query_name = $args['query_name'];
$shortcode = $args['shortcode'];

$editor = theme( 'query_editor', $args );
$live_preview = QW_Settings::get_instance()->get('live_preview');

?>
<div class="wrap">
	<h2><?php _e( 'Edit query ' ); ?> <em><?php print $query_name; ?></em></h2>
	<div class="admin-content">
		<form id="qw-edit-query-form"
		      action="<?php print admin_url( "admin.php?page=query-wrangler&action=update&edit=$query_id&noheader=true" ); ?>"
		      method='post'
		      data-query-id="<?php print $query_id; ?>"
		      data-ajax-url="<?php print admin_url( 'admin-ajax.php' ); ?>">

			<div id="qw-query-action-buttons">
				<div id="query-actions">
					<input type="submit" class="button-primary" value="<?php _e('Save'); ?>"> | <a href="<?php print admin_url( "admin.php?page=query-wrangler&export=$query_id" ); ?>"><?php _e( 'Export' ); ?></a>
				</div>
			</div>

			<div class="description"><?php echo $shortcode; ?></div>

			<div id="message" class="updated qw-changes">
				<p><strong>*</strong><?php _e( 'Changes have been made that need to be saved.' ); ?></p>
			</div>
			<div class="qw-clear-gone"><!-- ie hack -->&nbsp;</div>

			<?php print $editor; ?>
		</form>

		<!-- Preview -->
		<div id="query-preview" class="qw-query-option">
			<div id="query-preview-controls" class="query-preview-inactive">
				<label>
					<input id="live-preview"
						type="checkbox"
						<?php checked( $live_preview ); ?> />
					Live Preview
				</label>
				<button id="get-preview" class="button">Preview</button>
			</div>

			<h4 id="preview-title">
				<?php _e( 'Preview Query' ); ?>
				<small><?php _e( 'This preview does not include your theme CSS stylesheet.' ); ?></small>
			</h4>

			<div id="query-preview-target"><?php _e('Preview has not loaded.'); ?></div>
		</div>
	</div>
</div>

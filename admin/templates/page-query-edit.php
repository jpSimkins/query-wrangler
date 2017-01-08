<?php if ( !defined('QW_PLUGIN_DIR') ) exit; ?>

<div class="wrap">
	<h2><?php print esc_html( get_admin_page_title() ); ?> <em><?php print $query_name; ?></em></h2>
	<div class="admin-content">
		<form id="qw-edit-query-form" action="<?php print $form_action; ?>" method='post'
		      data-query-id="<?php print $query_id; ?>"
		      data-ajax-url="<?php print admin_url( 'admin-ajax.php' ); ?>">
			<div id="qw-query-action-buttons">
				<div id="query-actions">
					<input type="submit" class="button-primary" value="<?php _e('Save'); ?>"> | <a href="<?php print admin_url( "admin.php?page=query-wrangler.export&query_id=$query_id" ); ?>"><?php _e( 'Export' ); ?></a>
				</div>
			</div>
			<div class="description">
				<code>[<?php echo $shortcode; ?> slug="<?php print $query_slug; ?>"]</code>
				-or-
				<code>[<?php echo $shortcode; ?> id="<?php print $query_id; ?>"]</code>
			</div>
			<div class="update-nag qw-changes">
				<strong>*</strong> <?php _e( 'Changes have been made that need to be saved.' ); ?>
			</div>
			<div class="qw-clear-gone"><!-- ie hack -->&nbsp;</div>

			<div id="qw-query-admin-options-wrap">
				<!-- left column -->
				<div class="qw-query-admin-column">
					<?php
					print qw_admin_template( 'form-editor-items-list',
						array(
							'handler_type' => 'basic',
							'title' => __( 'Basic Settings' ),
							'items' => $basics,
							'query_type' => $query_type,
						));
					?>
				</div>
				<!-- /column -->
				<!-- middle column -->
				<div class="qw-query-admin-column">
					<?php
					if ( $query_type == 'override' ) {
						print qw_admin_template( 'form-editor-items-list',
							array(
								'handler_type' => 'override',
								'title' => __( 'Overrides' ),
								'items' => $overrides,
								'limit_per_type' => 1,
								'add_rearrange' => true,
								'query_type' => $query_type,
							));
					}

					print qw_admin_template( 'form-editor-items-list',
						array(
							'handler_type' => 'field',
							'title' => __( 'Fields' ),
							'items' => $fields,
							'add_rearrange' => true,
							'query_type' => $query_type,
						));
					?>
				</div>
				<!-- /column -->
				<!-- right column -->
				<div class="qw-query-admin-column">
					<?php
					print qw_admin_template( 'form-editor-items-list',
						array(
							'handler_type' => 'sort',
							'title' => __( 'Order By' ),
							'items' => $sorts,
							'add_rearrange' => true,
							'query_type' => $query_type,
						));

					print qw_admin_template( 'form-editor-items-list',
						array(
							'handler_type' => 'filter',
							'title' => __( 'Filters' ),
							'items' => $filters,
							'add_rearrange' => true,
							'query_type' => $query_type,
						));
					?>
				</div>
				<div class="qw-clear-gone"><!-- ie hack -->&nbsp;</div>
			</div>
			<!-- ------- "add new items" forms --------- -->
			<div id="qw-options-forms">
				<?php
				print qw_admin_template( 'form-editor-items-add-list',
					array(
						'handler_type' => 'sort',
						'description' => __( 'Select options for sorting the query results.' ),
						'all_item_types' => $all_sorts,
						'query_type' => $query_type,
					));

				print qw_admin_template( 'form-editor-items-add-list',
					array(
						'handler_type' => 'field',
						'description' => __( 'Select Fields to add to this query\'s output.' ),
						'all_item_types' => $all_fields,
						'query_type' => $query_type,
					));

				print qw_admin_template( 'form-editor-items-add-list',
					array(
						'handler_type' => 'filter',
						'description' => __( 'Select filters to affect the query\'s results.' ),
						'all_item_types' => $all_filters,
						'query_type' => $query_type,
					));

				print qw_admin_template( 'form-editor-items-add-list',
					array(
						'handler_type' => 'override',
						'description' => __( 'Select overrides to add to this query. Limit 1 per type.' ),
						'all_item_types' => $all_overrides,
						'query_type' => $query_type,
					));
				?>
			</div><!-- / "add new items" forms -->
		</form>

		<!-- Preview -->
		<div id="query-preview" class="qw-query-option">
			<div id="query-preview-controls" class="query-preview-inactive">
				<label>
					<input id="live-preview" type="checkbox" <?php checked( $live_preview ); ?> />
					<?php _e( 'Live Preview' ); ?>
				</label>
				<button id="get-preview" class="button"><?php _e( 'Preview' ); ?></button>
			</div>

			<h4 id="preview-title">
				<?php _e( 'Preview Query' ); ?>
				<small><?php _e( 'This preview does not include your theme CSS stylesheet.' ); ?></small>
			</h4>

			<div id="query-preview-target"><?php _e('Preview has not loaded.'); ?></div>
		</div>
	</div>
</div>

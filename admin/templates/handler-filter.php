<?php

$form = new QW_Form_Fields( array(
		'form_field_prefix' => $filter['form_prefix'],
) );

?>
<!-- <?php print $filter['name']; ?> -->
<div id="qw-filter-<?php print $filter['name']; ?>" class="qw-filter qw-sortable-item qw-item-form">
	<?php if ( empty( $filter['required'] ) ) : ?>
		<div class="qw-remove button"> Remove </div>
	<?php endif; ?>
	<div class="qw-weight-container">
		<?php
		print $form->render_field( array(
				'type'  => 'number',
				'name'  => 'weight',
				'title' => __( 'Weight' ),
				'value' => $filter['weight'],
				'class' => array( 'qw-weight' )
		) );
		?>
	</div>
	<p class="description"><?php print $filter['description']; ?></p>

	<?php

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'type',
			'value' => $filter['type'],
			'class' => array( 'qw-field-type' ),
	) );

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'hook_key',
			'value' => $filter['hook_key'],
			'class' => array( 'qw-field-hook_key' ),
	) );

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'name',
			'value' => $filter['name'],
			'class' => array( 'qw-field-name', 'qw-js-title' ),
	) );

	if ( $filter['form'] )
	{ ?>
		<div class="qw-filter-form">
			<?php print $filter['form']; ?>
		</div>
		<?php
	}

	// exposed form and settings
	if ( !empty( $filter['exposed_form'] ) )
	{ ?>
		<div class="qw-options-group">
			<div class="qw-options-group-title">
				<div class="qw-setting">
					<?php

					print $form->render_field( array(
							'type' => 'checkbox',
							'name' => 'is_exposed',
							'title' => __( 'Expose Filter' ),
							'help' => __( 'Exposing a filter allows a site guest to alter the query results with a form. If you expose this filter, the values above will act as the default values of the filter.' ),
							'value' => (int) !empty( $filter['values']['is_exposed'] ),
					) );
					?>
				</div>
			</div>

			<div class="qw-options-group-content qw-field-options-hidden">
				<?php

				print $form->render_field( array(
						'type' => 'checkbox',
						'name' => 'exposed_limit_values',
						'title' => __( 'Limit Values' ),
						'help' => __( 'If checked, only the values above will be available to the exposed filter.' ),
						'value' => (int) !empty( $filter['values']['exposed_limit_values'] ),
				) );

				print $form->render_field( array(
						'type' => 'checkbox',
						'name' => 'exposed_default_values',
						'title' => __( 'Default Values' ),
						'help' => __( 'If checked, the values above will be the default values of the exposed filter.' ),
						'value' => (int) !empty( $filter['values']['exposed_default_values'] ),
				) );

				print $form->render_field( array(
						'type' => 'checkbox',
						'name' => 'exposed_label',
						'title' => __( 'Exposed Label' ),
						'help' => __( 'Label for the exposed form item.' ),
						'value' => (int) !empty( $filter['values']['exposed_label'] ),
				) );

				print $form->render_field( array(
						'type' => 'checkbox',
						'name' => 'exposed_desc',
						'title' => __( 'Exposed Description' ),
						'help' => __( 'Useful for providing help text to a user.' ),
						'value' => isset( $filter['values']['exposed_desc'] ) ? $filter['values']['exposed_desc'] : '',
				) );

				print $form->render_field( array(
						'type' => 'checkbox',
						'name' => 'exposed_key',
						'title' => __( 'Exposed Key' ),
						'help' => __( 'URL ($_GET) key for the filter. Useful for multiple forms on a single page.' ),
						'value' => isset( $filter['values']['exposed_key'] ) ? $filter['values']['exposed_key'] : '',
				) );

				if ( isset( $filter['exposed_settings_form'] ) && is_callable( $filter['exposed_settings_form'] ) )
				{ ?>
					<div class="qw-exposed-settings-form">
						<?php
						ob_start();
						call_user_func( $filter['exposed_settings_form'], $filter );
						print ob_get_clean(); ?>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}
	?>
</div>

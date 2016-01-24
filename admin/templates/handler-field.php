<?php

$form = new QW_Form_Fields( array(
		'form_field_prefix' => $field['form_prefix'],
) );

?>
<!-- <?php echo $field['name']; ?> -->
<div id="qw-field-<?php print $field['name']; ?>" class="qw-field qw-sortable-item qw-item-form">
	<div class="qw-remove button"> Remove </div>
	<div class="qw-weight-container">
		<?php
		print $form->render_field( array(
				'type'  => 'number',
				'name'  => 'weight',
				'title' => __( 'Weight' ),
				'value' => $field['weight'],
				'class' => array( 'qw-weight' )
		) );
		?>
	</div>
	<p class="description"><?php print $field['description']; ?></p>

	<?php

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'type',
			'value' => $field['type'],
			'class' => array( 'qw-field-type' ),
	) );

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'hook_key',
			'value' => $field['hook_key'],
			'class' => array( 'qw-field-hook_key' ),
	) );

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'name',
			'value' => $field['name'],
			'class' => array( 'qw-field-name', 'qw-js-title' ),
	) );
	?>

	<?php if ( isset( $field['form'] ) ) { ?>
		<div class="qw-field-form qw-setting">
			<?php print $field['form']; ?>
		</div>
	<?php } ?>

	<div class='qw-field-options'>
		<?php

		print $form->render_field( array(
				'type'  => 'checkbox',
				'name'  => 'exclude_display',
				'title' => __( 'Exclude this field from display' ),
				'value' => isset( $field['values']['exclude_display'] ) ? $field['values']['exclude_display'] : 0,
		) );

		print $form->render_field( array(
				'type'  => 'checkbox',
				'name'  => 'link',
				'title' => __( 'Link this field to the post' ),
				'value' => isset( $field['values']['link'] ) ? $field['values']['link'] : FALSE,
		) );

		print $form->render_field( array(
				'type'  => 'checkbox',
				'name'  => 'has_label',
				'title' => __( 'Create a Label for this field' ),
				'value' => isset( $field['values']['has_label'] ) ? $field['values']['has_label'] : FALSE,
		) );

		print $form->render_field( array(
				'type'  => 'text',
				'name'  => 'label',
				'title' => __( 'Label Text' ),
				'value' => isset( $field['values']['label'] ) ? $field['values']['label'] : '',
		) );

		if ( isset( $field['content_options'] ) && $field['content_options'] ) {

			print $form->render_field( array(
					'type'  => 'checkbox',
					'name'  => 'apply_the_content',
					'title' => __( 'Apply "the_content" filter to this field' ),
					'value' => isset( $field['values']['apply_the_content'] ) ? $field['values']['apply_the_content'] : FALSE,
			) );
		}

		print $form->render_field( array(
				'type'  => 'checkbox',
				'name'  => 'hide_if_empty',
				'title' => __( 'Hide field if empty' ),
				'value' => isset( $field['values']['hide_if_empty'] ) ? $field['values']['hide_if_empty'] : FALSE,
		) );

		?>
		<!-- rewrite output -->
		<div class="qw-options-group qw-field-row">
			<div class="qw-options-group-title">
				<?php
				print $form->render_field( array(
						'type'  => 'checkbox',
						'name'  => 'rewrite_output',
						'title' => __( 'Rewrite the output of this field' ),
						'value' => isset( $field['values']['rewrite_output'] ) ? $field['values']['rewrite_output'] : FALSE,
				) );
				?>
			</div>
			<div class="qw-options-group-content qw-field-options-hidden">
				<?php

				print $form->render_field( array(
						'type'  => 'textarea',
						'name'  => 'custom_output',
						'value' => isset( $field['values']['custom_output'] ) ? $field['values']['custom_output'] : '',
						'class' => array( 'qw-field-textarea' )
				) );

				print $form->render_field( array(
						'type' => 'item_list',
						'name' => 'tokens',
						'items' => $tokens,
						'description' => __( 'Available replacement tokens. These tokens will be replaced with the processed results of their fields.' ),
				) );
				?>
			</div>
		</div>

		<?php

		print $form->render_field( array(
				'type' => 'text',
				'name' => 'classes',
				'title' => __( 'Additional Classes' ),
				'description' => __( 'Additional CSS classes to add to the field during output. Separate multiple classes with spaces.' ),
				'value' => isset( $field['values']['classes'] ) ? $field['values']['classes'] : '',
		) );
		?>
	</div>

	<!-- enable empty field content -->
	<div class="qw-options-group qw-field-row">
		<div class="qw-options-group-title">
			<?php
			print $form->render_field( array(
					'type'  => 'checkbox',
					'name'  => 'empty_field_content_enabled',
					'title' => __( 'Rewrite empty result text' ),
					'value' => isset( $field['values']['empty_field_content_enabled'] ) ? $field['values']['empty_field_content_enabled'] : FALSE,
			) );
			?>
		</div>
		<div class="qw-options-group-content qw-field-options-hidden">
			<?php

			print $form->render_field( array(
					'type'  => 'textarea',
					'name'  => 'empty_field_content',
					'description' => __( 'Field settings will apply to this content.' ),
					'value' => isset( $field['values']['empty_field_content'] ) ? $field['values']['empty_field_content'] : '',
					'class' => array( 'qw-field-textarea' )
			) );

			print $form->render_field( array(
					'type' => 'item_list',
					'name' => 'tokens',
					'items' => $tokens,
					'description' => __( 'Available replacement tokens. These tokens will be replaced with the processed results of their fields.' ),
			) );
			?>
		</div>
	</div>
</div>
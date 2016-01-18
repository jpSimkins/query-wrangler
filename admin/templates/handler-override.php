<?php

$form = new QW_Form_Fields( array(
		'form_field_prefix' => $override['form_prefix'],
) );

?>
<!-- <?php print $override['name']; ?> -->
<div id="qw-override-<?php print $override['name']; ?>" class="qw-override qw-sortable-item qw-item-form">
	<div class="qw-remove button"> Remove </div>
	<div class="qw-weight-container">
		<?php
		print $form->render_field( array(
				'type'  => 'number',
				'name'  => 'weight',
				'title' => __( 'Weight' ),
				'value' => $override['weight'],
				'class' => array( 'qw-weight' )
		) );
		?>
	</div>
	<p class="description"><?php print $override['description']; ?></p>

	<?php

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'type',
			'value' => $override['type'],
			'class' => array( 'qw-field-type' ),
	) );

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'hook_key',
			'value' => $override['hook_key'],
			'class' => array( 'qw-field-hook_key' ),
	) );

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'name',
			'value' => $override['name'],
			'class' => array( 'qw-field-name', 'qw-js-title' ),
	) );

	if ( $override['form'] ) {
		print $override['form'];
	}

	?>
</div>

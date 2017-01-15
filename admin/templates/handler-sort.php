<?php

$form = new QW_Form_Fields( array(
		'form_field_prefix' => $sort['form_prefix'],
) );

?>
<!-- <?php print $sort['name']; ?> -->
<div id="qw-sort-<?php print $sort['name']; ?>" class="qw-sort qw-sortable-item qw-item-form">
	<div class="qw-remove button"> Remove </div>
	<div class="qw-weight-container">
		<?php
		print $form->render_field( array(
				'type'  => 'number',
				'name'  => 'weight',
				'title' => __( 'Weight' ),
				'value' => $sort['weight'],
				'class' => array( 'qw-weight' )
		) );
		?>
	</div>
	<p class="description"><?php print $sort['description']; ?></p>

	<?php

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'hook_key',
			'value' => $sort['hook_key'],
			'class' => array( 'qw-field-hook_key' ),
	) );

	print $form->render_field( array(
			'type'  => 'hidden',
			'name'  => 'name',
			'value' => $sort['name'],
			'class' => array( 'qw-field-name', 'qw-js-title' ),
	) );

	if ( $sort['form'] ) {
		print $sort['form'];
	}

	?>
</div>

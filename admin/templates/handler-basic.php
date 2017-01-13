<?php

$form = new QW_Form_Fields( array(
	'form_field_prefix' => $basic['form_prefix'],
) );

?>
<!-- <?php echo $basic['name']; ?> -->
<div id="qw-field-<?php print $basic['name']; ?>" class="qw-field qw-sortable-item qw-item-form">
	<p class="description"><?php print $basic['description']; ?></p>

	<?php
	print $form->render_field( array(
		'type'  => 'hidden',
		'name'  => 'hook_key',
		'value' => $basic['hook_key'],
		'class' => array( 'qw-field-hook_key' ),
	) );
	?>

	<?php if ( isset( $basic['form'] ) ) { ?>
		<div class="qw-field-form qw-setting">
			<?php print $basic['form']; ?>
		</div>
	<?php } ?>
</div>
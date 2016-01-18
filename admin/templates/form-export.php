<?php

$export_id = absint( $_GET['export'] );
$qw_query = qw_get_query( $export_id );
$form = new QW_Form_Fields();

?>
<div class="wrap">
	<h2><?php print __( 'Export query' ); ?> <em><?php print esc_html( $qw_query->name ); ?></em></h2>

	<div class="admin-content">
		<?php

		print $form->render_field( array(
				'type' => 'textarea',
				'name' => 'export-query',
				'id' => 'export-query',
				'value' => qw_query_export( $export_id ),
		) );
		?>
	</div>
</div>

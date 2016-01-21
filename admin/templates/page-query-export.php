<?php if ( !defined('QW_PLUGIN_DIR') ) exit; ?>

<div class="wrap">
	<h2><?php print esc_html( get_admin_page_title() ); ?> <em><?php print esc_html( $query_name ); ?></em></h2>

	<div class="admin-content">
		<?php

		print $form->render_field( array(
				'type' => 'textarea',
				'name' => 'export-query',
				'id' => 'export-query',
				'value' => $exported_query,
		) );
		?>
	</div>
</div>

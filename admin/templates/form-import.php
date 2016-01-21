<?php if ( !defined('QW_PLUGIN_DIR') ) exit; ?>

<div class="wrap">
	<h2><?php print esc_html( get_admin_page_title() ); ?></h2>

	<div id="qw-import" class="postbox admin-content">
		<div class="inside">
			<?php

			print $form->open();

			print $form->render_field( array(
					'type' => 'text',
					'name' => 'name',
					'id' => 'name',
					'title' => __( 'Query Name' ),
					'description' => __( 'Enter the name to use for this query if it is different from the source query. Leave blank to use the name of the query.' ),
					'value' => '',
			) );
			print $form->render_field( array(
					'type' => 'textarea',
					'name' => 'query',
					'id' => 'import-query',
					'title' => __( 'Query Code' ),
					'description' => __( 'Paste query code here.' ),
					'value' => '',
			) );

			print $form->render_field( array(
					'type' => 'hidden',
					'name' => 'action',
					'value' => 'import',
			) );

			print $form->render_field( array(
					'type' => 'submit',
					'name' => 'import-submit',
					'value' => __( 'Import' ),
					'class' => array( 'button-primary' )
			) );

			print $form->close();
			?>
		</div>
	</div>
</div>


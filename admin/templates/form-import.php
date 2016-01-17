<?php
$form = new QW_Form_Fields( array(
	'action' => admin_url('admin.php') . '?page=query-wrangler&action=import&noheader=true',
	'form_field_prefix' => 'qw-import',
) );

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
		'id' => 'query',
		'title' => __( 'Query Code' ),
		'description' => __( 'Paste query code here.' ),
		'value' => '',
		'class' => array( 'qw-textarea' ),
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



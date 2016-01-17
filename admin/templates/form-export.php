<?php

$form = new QW_Form_Fields();

print $form->render_field( array(
	'type' => 'textarea',
	'name' => 'export-query',
	'id' => 'export-query',
	'value' => qw_query_export( $query_id ),
) );




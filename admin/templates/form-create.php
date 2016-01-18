<div>
	<p>
		Choose the name and the type of your query.
	</p>
</div>

<div id="qw-create">
	<?php

	$form = new QW_Form_Fields( array(
		'action' => admin_url('admin.php') . '?page=query-wrangler&action=create&noheader=true',
		'form_field_prefix' => 'qw-create',
	) );

	print $form->open();

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'name',
		'title' => __( 'Query Name' ),
		'description' => __( 'Query name is a way for you, the admin, to identify the query easily.' ),
		'value' => '',
		'class' => array( '' ),
	) );

	print $form->render_field( array(
		'type' => 'select',
		'name' => 'type',
		'title' => __( 'Query Type' ),
		'description' => __( 'Query type determines how the query works within WordPress. View descriptions on the right.' ),
		'value' => '',
		'options' => array(
			'widget' => __( 'Widget & Shortcode' ),
			'override' => __( 'Override' ),
		),
		'class' => array( '' ),
	) );

	print $form->render_field( array(
		'type' => 'submit',
		'name' => 'create-query',
		'value' => __( 'Create' ),
		'class' => array( 'button-primary' ),
	) );

	print $form->close();
	?>
</div>

<div id="qw-create-description">
	<div>
		<h3><?php _e( 'Widget & Shortcode Queries' ); ?></h3>
		<p><?php _e( 'The Query Wrangler comes with a reusable WordPress Widget that an be places in sidebars. When you create a query of the this type, that query becomes selectable in the Widget settings.' ); ?></p>
	</div>
	<div>
		<h3><?php _e( 'Override Queries' ); ?></h3>
		<p><?php _e( 'An Override Query allows you to alter the way existing Wordpress pages such as Category and Tag pages display.' ); ?></p>
		<p><strong><?php _e( 'This feature is still in beta development. It has only been tested with permalinks set to /%category%/%postname%/' ); ?></strong></p>
	</div>
	<div style="color: #888;">
		<h3 style="color: #888; text-decoration: line-through;"><?php _e( 'Page Queries' ); ?></h3>
		<p><?php _e( 'Deprecated and disabled. For using Queries as a page, create a normal WP Page and place the query shortcode on it.' ); ?></p>
	</div>
</div>
<?php if ( !defined('QW_PLUGIN_DIR') ) exit; ?>

<div class="wrap">
	<h2><?php print esc_html( get_admin_page_title() ); ?></h2>

	<div class="admin-content">
		<?php

		print $form->open();

		print $form->render_field( array(
				'type' => 'checkbox',
				'name' => 'widget_theme_compat',
				'title' => __( 'Widget Theme Compatibility' ),
				'description' => __( 'Use if you have trouble with the way Query Wrangler Widgets appear in your sidebar.' ),
				'value' => $settings->get( 'widget_theme_compat' ),
		) );

		print $form->render_field( array(
				'type' => 'checkbox',
				'name' => 'live_preview',
				'title' => __( 'Live Preview' ),
				'description' => __( 'Default setting for live preview during query editing.' ),
				'value' => $settings->get( 'live_preview' ),
		) );

		print $form->render_field( array(
				'type' => 'checkbox',
				'name' => 'show_silent_meta',
				'title' => __( 'Show Silent Meta fields' ),
				'description' => __( 'Show custom meta fields that are normally hidden.' ),
				'value' => $settings->get( 'show_silent_meta' ),
		) );

		print $form->render_field( array(
				'type' => 'checkbox',
				'name' => 'shortcode_compat',
				'title' => __( 'Shortcode compatibility' ),
				'description' => __( 'Change the shortcode keyword from <code>query</code> to <code>qw_query</code>, to avoid conflicts with other plugins.' ),
				'value' => $settings->get( 'shortcode_compat' ),
		) );

		print $form->render_field( array(
				'type' => 'select',
				'name' => 'meta_value_field_handler',
				'title' => __( 'Meta Value field handler' ),
				'description' => __( 'Choose the way meta_value fields are handled.' ),
				'help' => __( 'Default - each meta_key is treated as a unique field in the UI.' ) . '<br>'
				          . __( 'New -  a generic "Custom field" is available in the UI, and you must provide it the meta key.' ),
				'value' => $settings->get( 'meta_value_field_handler' ),
				'options' => array(
						0 => __( 'Default handler' ),
						1 => __( 'New handler (beta)' ),
				),
		) );

		print $form->render_field( array(
				'type' => 'submit',
				'name' => 'save-settings',
				'title' => ' ',
				'value' => __( 'Save Settings' ),
				'class' => array( 'button-primary' )
		) );

		print $form->close();

		?>
	</div>
</div>


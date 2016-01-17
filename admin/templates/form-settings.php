<?php

$settings = QW_Settings::get_instance();

$form = new QW_Form_Fields( array(
	'action' => admin_url('admin.php') . '?page=query-wrangler&action=save_settings&noheader=true',
	'form_field_prefix' => 'qw-settings',
	'id' => 'qw-edit-settings',
	'form_style' => 'settings_table',
) );

print $form->open();

print $form->render_field( array(
		'type' => 'select',
		'name' => 'edit_theme',
		'title' => __( 'Editor Theme' ),
		'description' => __( 'Choose the Query Wrangler editor theme.' ),
		'value' => $settings->get( 'edit_theme', QW_DEFAULT_THEME ),
		'options' => $editor_options,
) );

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
		'options' => $meta_value_field_options,
) );

print $form->render_field( array(
		'type' => 'submit',
		'name' => 'save-settings',
		'title' => ' ',
		'value' => __( 'Save Settings' ),
		'class' => array( 'button-primary' )
) );

print $form->close();

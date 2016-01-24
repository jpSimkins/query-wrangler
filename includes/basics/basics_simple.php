<?php
// hook into qw_basics
add_filter( 'qw_basics', 'qw_simple_basic_settings' );

/**
 * Simple basic settings don't require much work beyond a few form fields
 *
 * @param $basics
 * @return mixed
 */
function qw_simple_basic_settings( $basics ) {
	$basics['display_title'] = array(
		'title'         => __( 'Display Title' ),
		'description'   => __( 'The title above the query page or widget' ),
		'option_type'   => 'display',
		'weight'        => 0,
		'form_fields' => array(
			'display_title' => array(
				'type' => 'text',
				'name' => 'title',
				'class' => array( 'qw-text-long', 'qw-js-title' ),
			)
		),
	);
	$basics['empty'] = array(
		'title'         => __( 'Empty Text' ),
		'description'   => __( 'The content placed here will appear if the query has no results.' ),
		'option_type'   => 'display',
		'weight'        => 0,
		'form_fields' => array(
			'empty' => array(
				'type' => 'textarea',
				'name' => 'empty',
				'class' => array( 'qw-field-textarea', 'qw-js-title' ),
			)
		),
	);
	$basics['footer'] = array(
		'title'         => __( 'Footer' ),
		'description'   => __( 'The content placed here will appear below the resulting query.' ),
		'option_type'   => 'display',
		'weight'        => 0,
		'form_fields' => array(
			'footer' => array(
				'type' => 'textarea',
				'name' => 'footer',
				'class' => array( 'qw-field-textarea', 'qw-js-title' ),
			)
		),
	);
	$basics['header'] = array(
		'title'         => __( 'Header' ),
		'description'   => __( 'The content placed here will appear above the resulting query.' ),
		'option_type'   => 'display',
		'weight'        => 0,
		'form_fields' => array(
			array(
				'type' => 'textarea',
				'name' => 'header',
				'class' => array( 'qw-field-textarea', 'qw-js-title' ),
			)
		)
	);
	$basics['wrapper_classes'] = array(
		'title'         => __( 'Wrapper Classes' ),
		'description'   => __( 'The CSS class names will be added to the query. This enables you to use specific CSS code for each query. You may define multiples classes separated by spaces.' ),
		'option_type'   => 'display',
		'weight'        => 0,
		'form_fields' => array(
			'wrapper_classes' => array(
				'type' => 'text',
				'name' => 'wrapper-classes',
				'class' => array( 'qw-text-long', 'qw-js-title' ),
			)
		)
	);
	$basics['page_path'] = array(
		'title'               => __( 'Page path' ),
		'description'         => __( 'The path or permalink you want this page to use. Avoid using spaces and capitalization for best results.' ),
		'option_type'         => 'display',
		'query_display_types' => array( 'page', ),
		'weight'              => 0,
		'form_fields' => array(
			'page_path' => array(
				'type' => 'text',
				'name_prefix' => '[page]',
				'name' => 'path',
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	$page_templates = array(
		'__none__'  => __( 'None - Allow theme to determine template' ),
		'index.php' => __( 'Default - index.php' ),
	);
	$page_templates = array_replace( $page_templates, array_flip( get_page_templates() ) );

	$basics['page_template'] = array(
		'title'               => __( 'Page Template' ),
		'description'         => __( 'Select which page template should wrap this query page.' ),
		'option_type'         => 'display',
		'form_callback'       => 'qw_basic_page_template_form',
		'query_display_types' => array( 'page', 'override' ),
		'weight'              => 0,
		'form_fields' => array(
			'page_template' => array(
				'type' => 'select',
				'name_prefix' => '[page]',
				'name' => 'template-file',
				'options' => $page_templates,
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $basics;
}

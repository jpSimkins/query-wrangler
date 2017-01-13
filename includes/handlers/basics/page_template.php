<?php

add_filter( 'qw_basics', 'qw_basic_page_template' );

/**
 *
 *
 * @param $basics
 * @return mixed
 */
function qw_basic_page_template( $basics )
{
	$page_templates = array(
		'__none__'  => __( 'None - Allow theme to determine template' ),
		'index.php' => __( 'Default - index.php' ),
	);
	$page_templates = array_replace( $page_templates, array_flip( get_page_templates() ) );

	$basics['page_template'] = array(
		'title'               => __( 'Page Template' ),
		'description'         => __( 'Select which page template should wrap this query page.' ),
		'form_callback'       => 'qw_basic_page_template_form',
		'query_display_types' => array( 'page', 'override' ),
		'weight'              => 11,
		'required'      => true,
		'form_fields' => array(
			'page_template' => array(
				'type' => 'select',
				'name' => 'template-file',
				'options' => $page_templates,
				'class' => array( 'qw-js-title' ),
			)
		)
	);

	return $basics;
}


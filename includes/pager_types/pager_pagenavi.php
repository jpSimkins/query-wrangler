<?php

add_filter( 'qw_pager_types', 'qw_pager_type_pagenavi' );

/**
 * WP_Pagenavi pager type
 *
 * @param $pager_types
 *
 * @return array
 */
function qw_pager_type_pagenavi( $pager_types )
{
	// WP PageNavi Plugin
	if ( function_exists( 'wp_pagenavi' ) ) {
		$pager_types['pagenavi'] = array(
			'title'    => __( 'PageNavi' ),
			'callback' => 'qw_pager_type_pagenavi_callback',
			'settings_callback' => 'qw_pager_type_pagenavi_settings_callback',
			'settings_key' => 'pagenavi_settings',
		);
	}

	return $pager_types;
}

/**
 * Additional settings for this pager type
 *
 * @param $pager
 * @param $display
 */
function qw_pager_type_pagenavi_settings_callback( $pager, $display )
{
	// default settings values
	$values = array(
		'pages_text'    => __( 'Page %CURRENT_PAGE% of %TOTAL_PAGES%', 'wp-pagenavi' ),
		'current_text'  => '%PAGE_NUMBER%',
		'page_text'     => '%PAGE_NUMBER%',
		'first_text'    => __( '&laquo; First', 'wp-pagenavi' ),
		'last_text'     => __( 'Last &raquo;', 'wp-pagenavi' ),
		'prev_text'     => __( '&laquo;', 'wp-pagenavi' ),
		'next_text'     => __( '&raquo;', 'wp-pagenavi' ),
		/*
		'dotleft_text'  => __( '...', 'wp-pagenavi' ),
		'dotright_text' => __( '...', 'wp-pagenavi' ),
		'num_pages' => 5,
		'num_larger_page_numbers' => 3,
		'larger_page_numbers_multiple' => 10,
		'always_show' => false,
		'use_pagenavi_css' => true,
		'style' => 1,
		*/
	);

	if ( !empty( $pager['values'] ) ) {
		$values = array_replace( $values, $pager['values'] );
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => QW_FORM_PREFIX . '[display][pager][pagenavi_settings]',
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'pages_text',
		'title' => __( 'Pages text' ),
		'value' => $values['pages_text'],
	) );
	print $form->render_field( array(
		'type' => 'text',
		'name' => 'current_text',
		'title' => __( 'Current text' ),
		'value' => $values['current_text'],
	) );
	print $form->render_field( array(
		'type' => 'text',
		'name' => 'page_text',
		'title' => __( 'Page text' ),
		'value' => $values['page_text'],
	) );
	print $form->render_field( array(
		'type' => 'text',
		'name' => 'first_text',
		'title' => __( 'First text' ),
		'value' => $values['first_text'],
	) );
	print $form->render_field( array(
		'type' => 'text',
		'name' => 'last_text',
		'title' => __( 'Last text' ),
		'value' => $values['last_text'],
	) );
	print $form->render_field( array(
		'type' => 'text',
		'name' => 'next_text',
		'title' => __( 'Next text' ),
		'value' => $values['next_text'],
	) );
	print $form->render_field( array(
		'type' => 'text',
		'name' => 'prev_text',
		'title' => __( 'Previous text' ),
		'value' => $values['prev_text'],
	) );
}

/**
 * Pager with page numbers
 *
 * @param $pager
 * @param object $wp_query Object
 *
 * @return string HTML for pager
 */
function qw_pager_type_pagenavi_callback( $pager, $wp_query )
{

	if ( function_exists( 'wp_pagenavi' ) ) {
		return wp_pagenavi( array(
			'query' => $wp_query,
			'echo'  => FALSE,
			'options' => $pager['values'],
		) );
	}
}

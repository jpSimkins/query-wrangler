<?php
/*

******************************************************************

Contributors:      daggerhart
Plugin Name:       Query Wrangler
Plugin URI:        http://daggerhart.com
Description:       Query Wrangler provides an intuitive interface for creating complex WP queries as pages or widgets. Based on Drupal Views.
Author:            Jonathan Daggerhart
Author URI:        http://daggerhart.com
Version:           1.6.0

******************************************************************

Copyright 2010  Jonathan Daggerhart  (email : jonathan@daggerhart.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

******************************************************************

*/

// some useful definitions
define( 'QW_VERSION',     '1.6.0' );
define( 'QW_DB_VERSION',  '2000' );
define( 'QW_PLUGIN_DIR',  dirname( __FILE__ ) );
define( 'QW_PLUGIN_URL',  plugins_url( '', __FILE__ ) );
define( 'QW_FORM_PREFIX', 'qw-query-options' );


class Query_Wrangler {

	private $handlers;
	private $settings;

	/**
	 * Query_Wrangler constructor.
	 */
	function __construct(){
		// include Template Wrangler
		if ( ! function_exists( 'theme' ) ) {
			include_once QW_PLUGIN_DIR . '/template-wrangler.php';
		}
		include_once QW_PLUGIN_DIR . '/includes/php-polyfill.php';
		include_once QW_PLUGIN_DIR . '/includes/functions.php';
		include_once QW_PLUGIN_DIR . '/includes/hooks.php';
		include_once QW_PLUGIN_DIR . '/includes/pages.php';
		include_once QW_PLUGIN_DIR . '/includes/query.php';
		include_once QW_PLUGIN_DIR . '/includes/query-db.php';
		include_once QW_PLUGIN_DIR . '/includes/theme.php';
		include_once QW_PLUGIN_DIR . '/includes/exposed.php';

		spl_autoload_register( array( $this, 'autoload' ) );



		$last_db_version = get_option( 'qw_db_version', '2000' );
		$update = new QW_Update( $last_db_version, QW_DB_VERSION );
		if ( $update->needed() ){
			$update->perform_updates();
		}
	}

	/**
	 * Instantiate and hook into WordPress
	 */
	static public function register(){
		$plugin = new self();

		add_action( 'widgets_init', array( $plugin, 'widgets_init' ) );
		add_action( 'init', array( $plugin, 'init' ) );
	}

	/**
	 * Register WordPress widgets
	 */
	function widgets_init(){
		register_widget( 'Query_Wrangler_Widget' );
	}

	/**
	 * Initialize
	 */
	function init(){
		$this->load_common();

		if ( is_admin() ){
			$this->load_admin();
		}
	}

	/**
	 * Load files common to both the frontend and admin pages
	 */
	function load_common(){
		$includes = array(
			'includes/handlers/basics' => array(
				'basics_simple',
				'empty',
				'footer',
				'header',
				'pager',
				'row_styles',
				'styles',
			),
			'includes/handlers/fields' => array(
				'template_tags',
				'post_properties',
				'post_author',
				'post_author_avatar',
				'file_attachment',
				'image_attachment',
				'featured_image',
				'callback_field',
				'taxonomy_terms',

			),
			'includes/handlers/filters' => array(
				'filters_simple',
				'author',
				'callback',
				'post_types',
				'post_id',
				'meta_query',
				'tags',
				'categories',
				'post_parent',
				'taxonomies',
				'taxonomy_relation',
				'search',
			),
			'includes/handlers/sorts' => array(
				'default_sorts'
			),
			'includes/handlers/overrides' => array(
				'categories',
				'post_type_archive',
				'tags',
				'taxonomies',
			),
			'includes/row_styles' => array(
				'row_style_posts',
				'row_style_fields',
				'row_style_template_part',
			),
			'includes/pager_types' => array(
				'pager_default',
				'pager_numbers',
				'pager_pagenavi',
			),
			'includes/styles' => array(
				'style_unformatted',
				'style_table',
				'style_ordered_list',
				'style_unordered_list',
			),
			'includes/meta_value_displays' => array(
				'raw',
				'acf_default',
				'cctm_default',
			),
		);

		/*
		 * Meta value field changes depending on a setting
		 */
		$this->settings = QW_Settings::get_instance();

		if ( $this->settings->get( 'meta_value_field_handler', 0 ) ) {
			$includes['/includes/handlers/fields'][] = 'meta_value_new';
		}
		else {
			$includes['/includes/handlers/fields'][] = 'meta_value';
		}

		foreach( $includes as $folder => $files ){
			foreach( $files as $file ){
				include_once QW_PLUGIN_DIR . "/{$folder}/{$file}.php";
			}
		}

		QW_Override::register();
		QW_Shortcodes::register( $this->settings );

		$this->handlers = QW_Handlers::get_instance();
	}

	/**
	 * Load admin files
	 */
	function load_admin(){
		global $wpdb;
		include_once QW_PLUGIN_DIR . '/admin/ajax.php';

		QW_Admin_Pages::register( $this->settings, $wpdb );

	}

	// QW_Admin_Pages
	// class-qw-admin-pages.php
	function autoload($class) {
		$dirs = array(
			QW_PLUGIN_DIR . '/includes/',
			QW_PLUGIN_DIR . '/admin/'
		);

		$filename = 'class-'.strtolower( str_replace( '_', '-', $class) ). '.php';

		foreach ( $dirs as $dir ){
			if ( file_exists( $dir . $filename ) ) {
				require  $dir . $filename;
				break;
			}
		}
	}
}

Query_Wrangler::register();

/*===================================== DB TABLES =========================================*/
/*
 * Activation hooks for database tables
 */
function qw_query_wrangler_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . "query_wrangler";
	$sql        = "CREATE TABLE " . $table_name . " (
id mediumint(9) NOT NULL AUTO_INCREMENT,
name varchar(255) NOT NULL,
slug varchar(255) NOT NULL,
type varchar(16) NOT NULL,
path varchar(255),
data text NOT NULL,
UNIQUE KEY id (id)
);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

register_activation_hook( __FILE__, 'qw_query_wrangler_table' );


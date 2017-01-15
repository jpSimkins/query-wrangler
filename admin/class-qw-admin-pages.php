<?php

class QW_Admin_Pages {
	/**
	 * QW_Settings object
	 *
	 * @var QW_Settings
	 */
	protected $settings;

	/**
	 * WPDB object
	 *
	 * @var WPDB
	 */
	protected $wpdb;

	/**
	 * Admin page hooks created by this class
	 *
	 * @var array
	 */
	public $pages = array();

	public $base_uri = 'admin.php?page=query-wrangler';
	public $base_url;

	function __construct( $settings, $wpdb ){
		$this->settings = $settings;
		$this->wpdb = $wpdb;

		$this->db_table = $wpdb->prefix . 'query_wrangler';
		$this->base_url = admin_url( $this->base_uri );
	}

	/**
	 * Register class with WordPress
	 *
	 * @param $settings
	 */
	static public function register( $settings, $wpdb ){
		$plugin = new self( $settings, $wpdb );

		add_action( 'admin_menu', array( $plugin, 'admin_menu' ), 9999 );
		add_action( 'admin_enqueue_scripts', array( $plugin, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Provide menu items and gather their hook names
	 */
	function admin_menu(){
		global $menu;
		// get the first available menu placement around 30, trivial, I know
		$menu_placement = 1000;
		for ( $i = 30; $i < 100; $i ++ ) {
			if ( ! isset( $menu[ $i ] ) ) {
				$menu_placement = $i;
				break;
			}
		}

		$this->pages['list'] = add_menu_page( __( 'Query Wrangler' ),
				__( 'Query Wrangler' ),
				'manage_options',
				'query-wrangler',
				array( $this, 'list_page' ),
				'',
				$menu_placement );

		// hidden with css
		$this->pages['actions'] = add_submenu_page( 'query-wrangler',
				__( 'Actions Router' ),
				__( 'Actions Router' ),
				'manage_options',
				'query-wrangler.actions',
				array( $this, 'actions_router' ) );

		// hidden with css
		$this->pages['edit'] = add_submenu_page( 'query-wrangler',
				__( 'Edit Query' ),
				__( 'Edit Query' ),
				'manage_options',
				'query-wrangler.edit',
				array( $this, 'edit_page' ) );

		// hidden with css
		$this->pages['export'] = add_submenu_page( 'query-wrangler',
				__( 'Export' ),
				__( 'Export' ),
				'manage_options',
				'query-wrangler.export',
				array( $this, 'export_page' ) );

		$this->pages['create'] = add_submenu_page( 'query-wrangler',
				__( 'Create New Query' ),
				__( 'Add New' ),
				'manage_options',
				'query-wrangler.create',
				array( $this, 'create_page' ) );

		$this->pages['import'] = add_submenu_page( 'query-wrangler',
				__( 'Import' ),
				__( 'Import' ),
				'manage_options',
				'query-wrangler.import',
				array( $this, 'import_page' ) );

		$this->pages['settings'] = add_submenu_page( 'query-wrangler',
				__( 'Settings' ),
				__( 'Settings' ),
				'manage_options',
				'query-wrangler.settings',
				array( $this, 'settings_page' ) );
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @param $hook
	 */
	function admin_enqueue_scripts( $hook ){
		// global styles
		wp_enqueue_style( 'qw-global',
				QW_PLUGIN_URL . '/admin/css/global.css',
				array(),
				QW_VERSION);

		if ( in_array( $hook, $this->pages ) ){
			$flip = array_flip( $this->pages );

			// qw generic styles
			wp_enqueue_style( 'qw-admin',
					QW_PLUGIN_URL . '/admin/css/query-wrangler.css',
					array(),
					QW_VERSION);

			// list page
			if ( $flip[ $hook ] == 'list' ){
				wp_enqueue_script( 'qw-admin-list-js',
						plugins_url( '/admin/js/query-wrangler-list.js', dirname( __FILE__ ) ),
						array(),
						QW_VERSION,
						TRUE );
			}

			// edit page
			if ( $flip[ $hook ] == 'edit' ) {
				$dir = QW_PLUGIN_URL . '/admin';

				// jquery ui rom cdn
				// @todo - non-cdn version? anything available in wp core?
				global $wp_scripts;
				$ui = $wp_scripts->query( 'jquery-ui-core' );
				wp_enqueue_style( 'jquery-ui-smoothness',
						"//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css",
						FALSE,
						NULL );

				wp_enqueue_style( 'qw-admin-edit',
						"{$dir}/css/query-wrangler-views.css",
						array(),
						QW_VERSION );

				// js
				wp_enqueue_script( 'qw-unserialize-form',
						"{$dir}/js/jquery.unserialize-form.js",
						array(),
						QW_VERSION,
						TRUE );

				wp_enqueue_script( 'qw-admin-js',
						"$dir/js/query-wrangler.js",
						array(
							'jquery',
							'jquery-ui-core',
							'jquery-ui-accordion',
							'jquery-ui-autocomplete',
							'jquery-ui-dialog',
							'jquery-ui-sortable',
							'qw-unserialize-form',
						),
						QW_VERSION,
						TRUE );

				wp_enqueue_script( 'qw-edit-theme-views',
						"{$dir}/js/query-wrangler-views.js",
						array( 'qw-admin-js' ),
						QW_VERSION,
						TRUE );
			}
		}
	}

	/**
	 * Routing for admin page actions
	 */
	function actions_router(){
		$query_id = $this->get_current_query_id();
		$action = !empty( $_GET['action'] ) ? $_GET['action'] : null;

		if ( $action ) {
			switch ( $action ) {
				case 'update':
					if ( $query_id && !empty( $_POST[ QW_FORM_PREFIX ] ) ){
						$this->update_query( $query_id, $_POST[ QW_FORM_PREFIX ] );
						$this->redirect( 'query-wrangler.edit', $query_id );
					}
					break;

				case 'delete':
					if ( $query_id ) {
						$this->delete_query( $query_id );
						$this->redirect();
					}
					break;

				case 'create':
					if ( !empty( $_POST[ QW_FORM_PREFIX ] ) ) {
						$new_query_id = $this->create_query( $_POST[ QW_FORM_PREFIX ] );
						$this->redirect( 'query-wrangler.edit', $new_query_id );
					}
					break;

				case 'import':
					if ( !empty( $_POST[ QW_FORM_PREFIX ] ) ) {
						$new_query_id = $this->import_query( $_POST[ QW_FORM_PREFIX ] );
						$this->redirect( 'query-wrangler.edit', $new_query_id );
					}
					break;

				case 'save_settings':
					if ( !empty( $_POST[ QW_FORM_PREFIX ] ) ) {
						$this->settings_save( $_POST[ QW_FORM_PREFIX ] );
						$this->redirect( 'query-wrangler.settings' );
					}
					break;
			}
		}

		exit;
	}

	/**
	 * List - Page
	 */
	function list_page() {
		include_once QW_PLUGIN_DIR . '/admin/templates/page-query-list.php';

		$list_table = new Query_Wrangler_List_Table( $this );
		$list_table->do_the_deal();
	}

	/**
	 * Delete - delete a query
	 *
	 * @param $query_id
	 */
	function delete_query( $query_id ){
		if ( !is_numeric( $query_id ) ) return;

		$this->wpdb->delete( $this->db_table, array( 'id' => absint( $query_id ) ) );

		/**
		 * Hook `qw_delete_query` allows for additional operations on delete
		 */
		do_action( 'qw_delete_query', $query_id );

		// @todo - move this somewhere that subscribes to the action
		$table = $this->wpdb->prefix . "query_override_terms";
		$this->wpdb->delete( $table, array( 'query_id' => $query_id ) );
	}

	/**
	 * Settings - Page
	 */
	function settings_page() {
		$form = new QW_Form_Fields( array(
			'action' => $this->base_url . '.actions&noheader=true&action=save_settings',
			'form_field_prefix' => QW_FORM_PREFIX,
			'id' => 'qw-edit-settings',
			'form_style' => 'settings_table',
		) );

		print qw_admin_template( 'page-settings', array(
			'form' => $form,
			'settings' => $this->settings,
		) );
	}

	/**
	 * Settings - Save
	 *
	 * @param $new
	 */
	function settings_save( $new ){
		$settings = $this->settings;
		$settings->set( 'widget_theme_compat', (int) !empty( $new['widget_theme_compat'] ) );
		$settings->set( 'live_preview',        (int) !empty( $new['live_preview'] ) );
		$settings->set( 'show_silent_meta',    (int) !empty( $new['show_silent_meta'] ) );
		$settings->set( 'shortcode_compat',    (int) !empty( $new['shortcode_compat'] ) );
		$settings->set( 'meta_value_field_handler', absint( $new['meta_value_field_handler'] ) );
		$settings->save();
	}

	/**
	 * Create - Page
	 */
	function create_page() {

		$form = new QW_Form_Fields( array(
			'action' => $this->base_url . '.actions&noheader=true&action=create',
			'form_field_prefix' => QW_FORM_PREFIX,
		) );

		print qw_admin_template( 'page-query-create', array(
			'form' => $form,
		) );
	}

	/**
	 * Create - Insert new query
	 *
	 * @param $new
	 *
	 * @return int New Query ID
	 * @internal param $post - $_POST data
	 */
	function create_query( $new ) {
		$values = array(
			'name' => sanitize_text_field( $new['name'] ),
			'slug' => sanitize_title( $new['name'] ),
			'type' => sanitize_text_field( $new['type'] ),
			'path' => NULL,
			'data' => qw_serialize( qw_default_query_data() ),
		);

		$this->wpdb->insert( $this->db_table, $values );

		return $this->wpdb->insert_id;
	}

	/**
	 * Export - Page
	 */
	function export_page() {
		$query_id = $this->get_current_query_id();

		if ( $query_id ) {
			$qw_query = qw_get_query( $query_id );

			print qw_admin_template( 'page-query-export', array(
				'form' => new QW_Form_Fields(),
				'query_name' => $qw_query->name,
				'exported_query' => $this->export_query( $qw_query->id )
			) );
		}
	}

	/**
	 * Export - Get query data as php code
	 *
	 * @param $query_id - the query's id number
	 * @return string
	 */
	function export_query( $query_id ) {
		$query = qw_get_query( $query_id );
		$row = ( array ) $query->row;
		unset( $row['id'] );

		// unserialize the stored data
		$row['data'] = qw_unserialize( $row['data'] );
		$row['data'] = $this->escape_export( $row['data'] );
		$export = wp_json_encode( $row, JSON_PRETTY_PRINT );

		return $export;
	}

	/**
	 * Helper to handle HTMl inside of json export
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	function escape_export( $data ){
		if ( isset( $data['display']['field_settings']['fields'] ) ) {
			$fields = &$data['display']['field_settings']['fields'];

			foreach( $fields as $field_name => $field ) {
				$fields[ $field_name ]['custom_output'] = htmlspecialchars( $field['custom_output'], ENT_QUOTES, 'UTF-8', false );
				$fields[ $field_name ]['empty_field_content'] = htmlspecialchars( $field['empty_field_content'], ENT_QUOTES, 'UTF-8', false );
			}
		}

		return $data;
	}

	/**
	 * Import - Page
	 */
	function import_page() {
		$form = new QW_Form_Fields( array(
			'action' => $this->base_url . '.actions&noheader=true&action=import',
			'form_field_prefix' => QW_FORM_PREFIX,
		) );

		print qw_admin_template( 'page-query-import', array(
			'form' => $form,
		) );
	}

	/**
	 * Import - query into the database
	 *
	 * @param $import
	 *
	 * @return int
	 * @internal param $post
	 */
	function import_query( $import ) {
		$query = null;

		if ( !empty( $import['query'] ) ){
			$import['query'] = stripslashes( $import['query'] );
			$query = json_decode( $import['query'], TRUE );
			$query['data'] = $this->decode_import( $query['data'] );

		}

		if ( !empty( $query ) ) {
			$query['name'] = ! empty( $import['name'] ) ? sanitize_text_field( $import['name'] ) : 'No Name';
			$query['slug'] = sanitize_title( $query['name'] );
			$query['data'] = qw_serialize( $query['data'] );

			$this->wpdb->insert( $this->db_table, $query );

			return $this->wpdb->insert_id;
		}
	}

	/**
	 * Helper to handle HTMl inside of json import
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	function decode_import( $data ){
		if ( isset( $data['display']['field_settings']['fields'] ) ) {
			$fields = &$data['display']['field_settings']['fields'];

			foreach( $fields as $field_name => $field ) {
				$fields[ $field_name ]['custom_output'] = htmlspecialchars_decode( $field['custom_output'] );
				$fields[ $field_name ]['empty_field_content'] = htmlspecialchars_decode( $field['empty_field_content'] );
			}
		}

		return $data;
	}

	/**
	 * Edit - Page
	 */
	function edit_page(){
		$query_id = $this->get_current_query_id();
		if ( !$query_id ) return;

		$qw_query = qw_get_query( $query_id );
		if ( !$qw_query ) return;

		$args = $this->edit_page_args( $qw_query );
		$args['live_preview'] = $this->settings->get('live_preview');

		print qw_admin_template( 'page-query-edit', $args );
	}

	/**
	 * Edit - Arguments
	 *
	 * @param $qw_query
	 *
	 * @return array
	 */
	function edit_page_args( $qw_query ) {
		$options = $qw_query->row->data;
		//$display = isset( $options['display'] ) ? array_map( 'stripslashes_deep', $options['display'] ) : array();

		// preprocess existing handlers
		$handlers = qw_get_query_handlers( $options );
		$handlers = $this->make_handler_wrapper_forms( $handlers );
d($handlers);
d($options);
//		$basics = qw_all_basic_settings();
//		$basics = $this->make_basics_wrapper_forms( $basics, $options );

		// start building edit page data
		$editor_args = array(
			'form_action'         => admin_url( "admin.php?page=query-wrangler.actions&action=update&query_id={$qw_query->id}&noheader=true" ),

			'query_id'            => $qw_query->id,
			'query_slug'          => $qw_query->slug,
			'query_name'          => $qw_query->name,
			'query_type'          => $qw_query->type,
			'shortcode'           => $this->settings->get('shortcode_compat') ? 'qw_query' : 'query',
			'options'             => $options,
			//'args'                => $options['args'],
			//'display'             => $display,
			//'basics'              => $basics,
			'handlers'            => $handlers,
		);

		$editor_args = apply_filters( 'qw_edit_page_args', $editor_args );

		return $editor_args;
	}

	/**
	 * Update - existing query
	 *
	 * @param $query_id
	 * @param $options
	 */
	function update_query( $query_id, $options ) {
		$qw_query = qw_get_query( $query_id );
		if ( ! $qw_query ){
			return;
		}

		/**
		 * Hook `qw_pre_save` allows alterations before saving
		 */
		$options = apply_filters( 'qw_pre_save', $options, $query_id );
		$options = array_map( 'stripslashes_deep', $options );

		$data = array(
			'data' => qw_serialize( $options )
		);

		// update for pages
		if ( $qw_query->type == 'page' ) {
			$data['path'] = !empty( $options['display']['page']['path'] ) ? ltrim( $options['display']['page']['path'], '/' ) : '';
		}

		$this->wpdb->update( $this->db_table, $data, array( 'id' => $query_id ) );
	}

	/**
	 *
	 *
	 * @param $handlers
	 * @return mixed
	 */
	function make_handler_wrapper_forms( $handlers ){
		$tokens = array();

		foreach( $handlers as $handler_type => $handler ){
			if ( empty( $handler['items'] ) ) {
				continue;
			}

			foreach( $handler['items'] as $k => $item ){
				$args = array(
					$handler_type => $item,
				);

				// fields need token
				if ( $handler_type == 'field' ){
					$tokens[ $item['name'] ] = '{{' . $item['name'] . '}}';
					$args += array(
						'tokens' => $tokens,
					);
				}

				$handlers[ $handler_type ]['items'][ $k ]['wrapper_form'] = qw_admin_template( 'handler-'.$handler_type, $args );
			}
		}

		return $handlers;
	}

	/**
	 * Prepare all the 'basic' forms
	 *
	 * @param $basics
	 * @param $options
	 * @return mixed
	 */
	function make_basics_wrapper_forms( $basics, $options ){
		foreach( $basics as $basic_key => $basic ) {
			ob_start();
			if ( !empty( $basic['form_fields'] ) ) {

				$form = new QW_Form_Fields( array(
					'form_field_prefix' => $basic['form_prefix'],
				) );

				foreach( $basic['form_fields'] as $field_key => $field ){
					$default_value = !empty( $field['default_value'] ) ? $field['default_value'] : '';

					// build the field name for our array_query
					$field_name = 'display';
					if ( !empty( $field['name_prefix'] ) ){
						$field_name.= $field['name_prefix'];
					}
					$field_name.= '['.$field['name'].']';

					// look for existing values
					$field['value'] = $form->get_field_value_from_data( $field_name, $options );
					if ( is_null( $field['value'] ) ){
						$field['value'] = $default_value;
					}

					// for single field basics, the field should inherit the
					// item's description
					if ( count( $basic['form_fields'] ) === 1){
						$field['description'] = $basic['description'];
					}

					// render the field
					print $form->render_field( $field );
				}

			}
			else if ( isset( $basic['form_callback'] ) && is_callable( $basic['form_callback'] ) ) {
				call_user_func( $basic['form_callback'], $basic, $options['display'] );
			}
			$basics[ $basic_key ]['wrapper_form'] = ob_get_clean();
		}

		return $basics;
	}

	/**
	 * Get the current query being edited
	 *
	 * @return int|false
	 */
	function get_current_query_id() {
		$screen = get_current_screen();
		$flip = array_flip( $this->pages );

		// ensure we're on one of our pages
		if ( !empty( $flip[ $screen->base ] ) && !empty( $_GET['query_id'] ) ) {
			return absint( $_GET['query_id'] );
		}

		return FALSE;
	}

	/**
	 * Redirect helper
	 *
	 * @param string $page
	 * @param null $query_id
	 */
	function redirect( $page = 'query-wrangler', $query_id = NULL ) {
		$url = admin_url( "admin.php?page={$page}" );

		if ( $query_id ) {
			$url .= "&query_id=" . $query_id;
		}

		wp_redirect( $url );
		exit();
	}
}

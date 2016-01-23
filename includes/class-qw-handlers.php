<?php

/**
 * Class QW_Handlers
 *
 * Handler items are components that can be added to a query.
 * Handler types are templates for handler items.
 * Handlers are the combined information about handler types and their handler
 *   items
 *
 * This class manages all of the above
 */
class QW_Handlers {

	/**
	 * Internal cache of all registered handler types
	 *
	 * @var array|mixed|void
	 */
	public $all_handlers = array();

	/**
	 * QW_Handlers constructor.
	 */
	private function __construct(){
		add_filter( 'qw_handlers', array( $this, 'default_handler_types' ) );

		$this->all_handlers = qw_all_handlers();
	}

	/**
	 * Singleton
	 *
	 * @return QW_Handlers
	 */
	static public function get_instance(){
		static $instance = null;
		if ( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Core QW handler types
	 *
	 * @param $handlers
	 * @return mixed
	 */
	function default_handler_types( $handlers ) {
		$handlers['field']    = array(
				'title'            => __( 'Field' ),
				'description'      => __( 'Select Fields to add to this query output.' ),
				'all_callback'     => 'qw_all_fields',
				'data_callback'    => array( $this, 'get_fields_data' ),
				'form_prefix'      => '[display][field_settings][fields]',
		);
		$handlers['sort']     = array(
				'title'            => __( 'Sort Option' ),
				'description'      => __( 'Select options for sorting the query results.' ),
				'all_callback'     => 'qw_all_sort_options',
				'data_callback'    =>  array( $this, 'get_sorts_data' ),
				'form_prefix'      => '[args][sorts]',
		);
		$handlers['filter']   = array(
				'title'            => __( 'Filter' ),
				'description'      => __( 'Select filters to affect the query results.' ),
				'all_callback'     => 'qw_all_filters',
				'data_callback'    => array( $this, 'get_filters_data' ),
				'form_prefix'      => '[args][filters]',
		);
		$handlers['override'] = array(
				'title'            => __( 'Override' ),
				'description'      => __( 'Select overrides to affect the query results based on the context of where the query is displayed.' ),
				'all_callback'     => 'qw_all_overrides',
				'data_callback'    => array( $this, 'get_overrides_data' ),
				'form_prefix'      => '[override]',
		);

		return $handlers;
	}

	/**
	 * Prepare a query's handler items for form rendering
	 *
	 * @param $options
	 * @return mixed|void
	 */
	function get_query_handlers( $options ){
		$handlers = $this->all_handlers;

		foreach ( $handlers as $handler_type => $handler ) {
			$items = array();

			// retrieve the handler items from the query array
			if ( is_callable( $handler['data_callback'] ) ) {
				$items = call_user_func( $handler['data_callback'], $options );
			}

			// preprocess existing handler items
			if ( !empty( $items ) ) {
				$items = $this->preprocess_handler_items( $handler_type, $items );
			}

			// sort according to weight
			if ( !empty( $items ) ) {
				uasort( $items, 'qw_cmp' );
			}

			$handlers[ $handler_type ]['items'] = $items;
		}

		return $handlers;
	}

	/**
	 * Prepare existing handler items for form rendering
	 *
	 * @param $handler_type
	 * @param $existing_items
	 *
	 * @return mixed
	 */
	function preprocess_handler_items( $handler_type, $existing_items ){
		$handler = $this->all_handlers[ $handler_type ];
		$all_item_types = $handler['all_items'];

		// generate the form name prefixes
		foreach ( $existing_items as $name => $values ) {
			// load sort type data
			$hook_key = qw_get_hook_key( $all_item_types, $values );

			if ( empty( $hook_key ) ) {
				$hook_key = ! empty( $values['hook_key'] ) ? $values['hook_key'] : $name;
			}

			$this_item = $all_item_types[ $hook_key ];

			// copy important details to top level of array
			$this_item['name']        = $name;
			$this_item['type']        = ! empty( $values['type'] ) ? $values['type'] : $name;
			$this_item['weight']      = ! empty( $values['weight'] ) ? $values['weight'] : 0;
			$this_item['hook_key']    = $hook_key;
			$this_item['form_prefix'] = QW_FORM_PREFIX . $handler['form_prefix'] . '[' . $name . ']';

			// values on their own for handler forms
			$this_item['values']      = $values;

			// this handler's form
			if ( isset( $this_item['form_callback'] ) && is_callable( $this_item['form_callback'] ) ) {
				ob_start();
				call_user_func( $this_item['form_callback'], $this_item );
				$this_item['form'] = ob_get_clean();
			}
			// automatic form fields
			else if ( !empty( $this_item['form_fields'] ) && is_array( $this_item['form_fields'] ) ) {
				$this_item['form'] = $this->make_item_form_fields( $this_item );
			}

			$existing_items[ $name ] = $this_item;
		}

		return $existing_items;
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	function make_item_form_fields( $item ){
		$form = new QW_Form_Fields( array(
			'form_field_prefix' => $item['form_prefix'],
		) );

		$output = '';

		foreach( $item['form_fields'] as $key => $form_field ){
			$default_value = !empty( $form_field['default_value'] ) ? $form_field['default_value'] : '';
			$form_field['value'] = !empty( $item['values'][ $form_field['name'] ] ) ? $item['values'][ $form_field['name'] ] : $default_value;
			$output.= $form->render_field( $form_field );
		}

		return $output;
	}

	/**
	 * Retrieve existing Field data from an array of query options
	 *
	 * @param $options
	 * @return array
	 */
	function get_fields_data( $options ) {
		$data = array();

		if ( !empty( $options['display']['field_settings']['fields'] ) ) {
			$data = $options['display']['field_settings']['fields'];
		}

		return $data;
	}

	/**
	 * Retrieve existing Sort data from an array of query options
	 *
	 * @param $options
	 * @return array
	 */
	function get_sorts_data( $options ) {
		$data = array();

		if ( !empty( $options['args']['sorts'] ) ) {
			$data = $options['args']['sorts'];
		}

		return $data;
	}

	/**
	 * Retrieve existing Filter data from an array of query options
	 *
	 * @param $options
	 * @return array
	 */
	function get_filters_data( $options ) {
		$data = array();

		if ( !empty( $options['args']['filters'] ) ) {
			$data = $options['args']['filters'];
		}

		return $data;
	}

	/**
	 * Retrieve existing Override data from an array of query options
	 *
	 * @param $options
	 * @return array
	 */
	function get_overrides_data( $options ) {
		$data = array();

		if ( !empty( $options['override'] ) ) {
			$data = $options['override'];
		}

		return $data;
	}
}

/**
 * temp fix
 *
 * @param $options
 *
 * @return mixed|void
 */
function qw_get_query_handlers( $options ){
	$handlers = QW_Handlers::get_instance();

	return $handlers->get_query_handlers( $options );
}

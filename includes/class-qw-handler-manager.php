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
class QW_Handler_Manager {

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
		$this->all_handlers = qw_all_handlers();
	}

	/**
	 * Singleton
	 *
	 * @return QW_Handler_Manager
	 */
	static public function get_instance(){
		static $instance = null;
		if ( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Prepare a query's handler items for form rendering
	 *
	 * @param $options
	 * @return mixed|void
	 */
	function get_query_handlers( $options ){
		// loop through all handlers and prepare existing items
		$handlers = $this->all_handlers;
		foreach ( $handlers as $handler_type => $handler ) {
			$items = array();

			// retrieve the handler items from the query array
			if ( !empty( $options[ $handler_type ] ) ){
				$items = $options[ $handler_type ];
			}
			// @todo deprecated callback
//			else if ( is_callable( $handler['data_callback'] ) ) {
//				$items = call_user_func( $handler['data_callback'], $options );
//			}

			// handle missing required item types
			$items = $this->enforce_required_items( $handler_type, $items );

			// preprocess existing handler items
			if ( !empty( $items ) ) {
				$items = $this->preprocess_handler_items( $handler_type, $items, $options );
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
	function preprocess_handler_items( $handler_type, $existing_items, $options ){
		$handler = $this->all_handlers[ $handler_type ];

		// loop through all existing items and prepare them for output
		foreach ( $existing_items as $name => $values )
		{
			$hook_key = qw_get_hook_key( $handler['all_items'], $values );

			if ( empty( $hook_key ) ) {
				$hook_key = ! empty( $values['hook_key'] ) ? $values['hook_key'] : $name;
			}

			// handler item type definition can enforce weight on the instance
			if ( isset( $handler['all_items'][ $hook_key ]['weight'] ) ){
				$values['weight'] = $handler['all_items'][ $hook_key ]['weight'];
			}

			// this_item is a combination of the default item and the saved item
			$this_item = array_replace(
				$handler['all_items'][ $hook_key ],
				array(
					'name' => $name,
					'type' => !empty( $values['type'] ) ? $values['type'] : $name,
					'weight' => !empty( $values['weight'] ) ? $values['weight'] : 0,
					'hook_key' => $hook_key,
					'form_prefix' => "{$handler['form_prefix']}[{$name}]",
					'values' => $values,
				)
			);

			// this handler's form
			if ( isset( $this_item['form_callback'] ) && is_callable( $this_item['form_callback'] ) ) {
				ob_start();
				call_user_func( $this_item['form_callback'], $this_item, $options );
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
	 * Ensure that required item types of a handler type exist
	 *
	 * @param $handler_type
	 * @param array $items
	 *
	 * @return array
	 */
	function enforce_required_items( $handler_type, $items = array() ){
		$required = $this->get_required_item_types( $handler_type );

		foreach( $required as $required_key => $required_item )
		{
			if ( empty( $items[ $required_key ] ) )
			{
				$items[ $required_key ] = array(
					'hook_key' => $required_item['hook_key'],
				);

				// not all handler types require recording of "type" and "name"
				if ( !empty( $required_item['type'] ) )
				{
					$items[ $required_key ]['name'] = $required_item['type'];
					$items[ $required_key ]['type'] = $required_item['type'];
				}
			}
		}

		return $items;
	}

	/**
	 * Get item types of a handler type that are required
	 *
	 * @param $handler_type
	 * @return array
	 */
	function get_required_item_types( $handler_type ){
		$handler = $this->all_handlers[ $handler_type ];
		$required = array();

		foreach( $handler['all_items'] as $item_type => $item ){
			if ( !empty( $item['required'] ) ){
				$required[ $item_type ] = $item;
			}
		}

		return $required;
	}

	/**
	 * Render the form_fields associated with a handler item type,
	 * for an existing item.
	 *
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
			$default_value = isset( $form_field['default_value'] ) ? $form_field['default_value'] : '';
			$form_field['value'] = isset( $item['values'][ $form_field['name'] ] ) ? $item['values'][ $form_field['name'] ] : $default_value;
			$output.= $form->render_field( $form_field );
		}

		return $output;
	}
}

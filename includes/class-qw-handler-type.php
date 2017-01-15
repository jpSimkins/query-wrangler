<?php

class QW_Handler_Type {

	public $hook_key = '';

	public $title = '';

	public $description = '';

	public $handler_item_types_filter = '';

	public $handler_item_types = array();

	function __construct( $hook_key, $title, $description, $all_items_filter ){
		$this->hook_key                  = $hook_key;
		$this->title                     = $title;
		$this->description               = $description;
		$this->handler_item_types_filter = $all_items_filter;
		$this->form_prefix               = QW_FORM_PREFIX . "[{$this->hook_key}]" ;
	}

	/**
	 * Get all handler_item types for this handler type
	 *
	 * @return array
	 */
	function handler_item_types()
	{
		if ( !empty( $this->handler_item_types ) ){
			return $this->handler_item_types;
		}

		$handler_item_types = apply_filters( $this->handler_item_types_filter, array() );
		$this->handler_item_types = $this->process_handler_item_types( $handler_item_types );

		return $this->handler_item_types;
	}

	/**
	 * Prepare each handler item type with required data about itself
	 *
	 * @param $handler_item_types
	 *
	 * @return array
	 */
	function process_handler_item_types( $handler_item_types )
	{
		foreach( $handler_item_types as $hook_key => $item ){
			$item['hook_key'] = $hook_key;
			$item['form_prefix'] = "{$this->form_prefix}[{$hook_key}]";

			$handler_item_types[ $hook_key ] = $item;
		}

		uasort( $handler_item_types, 'qw_cmp' );

		return $handler_item_types;
	}
	/**
	 * Prepare a query's handler items for form rendering
	 *
	 * @param $query_data
	 *
	 * @return array
	 */
	function process_query_handler_item_instances( $query_data )
	{
		// get all existing and required item instances
		$item_instances = $this->get_handler_item_instances( $query_data );
		$item_instances = $this->enforce_required_item_instances( $item_instances );

		// merge the instances with their original and create the form
		$item_instances = $this->pre_process_handler_item_instances( $item_instances );
		$item_instances = $this->process_handler_item_instance_forms( $item_instances, $query_data );

		// sort according to weight
		if ( !empty( $item_instances ) ) {
			uasort( $item_instances, 'qw_cmp' );
		}

		return $item_instances;
	}

	/**
	 * Retrieve handler items from the query's data array
	 *
	 * @param $query_data
	 *
	 * @return array
	 */
	function get_handler_item_instances( $query_data ){
		$item_instances = array();

		if ( !empty( $query_data[ $this->hook_key ] ) ){
			$item_instances = $query_data[ $this->hook_key ];
		}

		return $item_instances;
	}

	/**
	 * Ensure that required item types of a handler type exist.
	 * Create if missing.
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	function enforce_required_item_instances( $items = array() )
	{
		foreach( $this->handler_item_types() as $hook_key => $handler_item_type )
		{
			if ( !empty( $handler_item_type['required'] ) &&
			     empty( $items[ $hook_key ] ) )
			{
				$items[ $hook_key ] = array(
					'hook_key' => $hook_key,
					'name' => $hook_key,
				);
			}
		}

		return $items;
	}

	/**
	 * Prepare existing handler items for form rendering
	 *
	 * @param $item_instances
	 *
	 * @return array
	 */
	function pre_process_handler_item_instances( $item_instances )
	{
		$handler_item_types = $this->handler_item_types();

		// loop through all existing items and prepare them for output
		foreach ( $item_instances as $name => $values )
		{
			$handler_item_type = $handler_item_types[ $values['hook_key'] ];

			// handler item type definition can enforce weight on the instance
			if ( isset( $handler_item_type['weight'] ) ){
				$values['weight'] = $handler_item_types[ $values['hook_key'] ]['weight'];
			}

			// this_item is a combination of the default item and the saved item
			$new_instance = array_replace(
				$handler_item_type,
				array(
					'name' => $name,
					'weight' => !empty( $values['weight'] ) ? $values['weight'] : 0,
					'hook_key' => $values['hook_key'],
					'form_prefix' => "{$this->form_prefix}[{$name}]",
					'values' => $values,
				)
			);

			$item_instances[ $name ] = $new_instance;
		}

		return $item_instances;
	}

	/**
	 * Prepare existing handler items for form rendering
	 *
	 * @param $item_instances
	 * @param $query_data
	 *
	 * @return array
	 */
	function process_handler_item_instance_forms( $item_instances, $query_data )
	{
		// loop through all existing items and prepare them for output
		foreach ( $item_instances as $name => $item_instance )
		{
			$item_instance['form'] = '';

			// automatic form fields
			if ( !empty( $item_instance['form_fields'] ) )
			{
				$item_instance['form'] .= $this->render_handler_item_instance_form( $item_instance );
			}

			// custom form provided by a callback
			if ( !empty( $item_instance['form_callback'] ) &&
			     is_callable( $item_instance['form_callback'] ) )
			{
				ob_start();
				call_user_func( $item_instance['form_callback'], $item_instance, $query_data );
				$item_instance['form'] .= ob_get_clean();
			}

			$item_instances[ $name ] = $item_instance;
		}

		return $item_instances;
	}

	/**
	 * Render the form_fields associated with a handler item type,
	 * for an existing item.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	function render_handler_item_instance_form( $item )
	{
		$form = new QW_Form_Fields( array(
			'form_field_prefix' => $item['form_prefix'],
		) );

		$output = '';

		foreach( $item['form_fields'] as $key => $form_field )
		{
			$default_value = isset( $form_field['default_value'] ) ? $form_field['default_value'] : '';
			$form_field['value'] = isset( $item['values'][ $form_field['name'] ] ) ? $item['values'][ $form_field['name'] ] : $default_value;

			$output .= $form->render_field( $form_field );
		}

		return $output;
	}
}

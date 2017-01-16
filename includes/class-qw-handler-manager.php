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
	 * Keyed array of QW_Handler_Type instances
	 *
	 * Handlers are groups of items that can be added and removed from a query
	 * eg: filter, sort, field, basic, override
	 *
	 * @var array
	 */
	public $handlers = array();

	/**
	 * QW_Handlers constructor.
	 */
	function __construct()
	{
		$this->handlers = apply_filters( 'qw_handlers', array() );
	}

	/**
	 * Get a single handler type
	 *
	 * @param $handler_type_hook_key
	 *
	 * @return QW_Handler_Type
	 */
	function get( $handler_type_hook_key )
	{
		if ( !empty( $this->handlers[ $handler_type_hook_key ] ) ) {
			return $this->handlers[ $handler_type_hook_key ];
		}

		return NULL;
	}

	/**
	 * Prepare a query's handler items for form rendering
	 *
	 * @param $query_data
	 *
	 * @return array
	 */
	function get_handler_item_instances( $query_data )
	{
		// loop through all handlers and prepare existing items
		$handler_item_instances = array();

		foreach ( $this->handlers as $handler_type => $handler ) {
			$handler_item_instances[ $handler_type ]['items'] = $handler->process_query_handler_item_instances( $query_data );
		}

		return $handler_item_instances;
	}
}

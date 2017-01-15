<?php

/**
 * Class QW_Query
 */
class QW_Query {

	// row from db
	public $row;

	// columns from the db
	public $id, // unique id
		$name, // human readable title
		$slug, // unique machine-safe string
		$type, // ( widget | page | override )
		$path, // page route
		$data; // un-parsed query options

	// if TRUE, query id was not found in the db
	public $is_new = FALSE;

	public $is_preview = FALSE;

	// generated WP_Query, not the global wp_query
	public $wp_query;

	// processed values
	// $options are essentially a copy of the $data array
	public $options;

	// $args is an array of WP_Query() arguments, it is processed from
	// the $options array and get its values from
	// filters, sorts, overrides and some basics
	public $args;

	// final html output
	public $output;

	/**
	 * Get an existing query, or create a new empty query with default values
	 *
	 * @param null $id
	 */
	function __construct( $id = NULL ) {
		// load by query id
		if ( $id && $query = qw_get_query_by_id( $id ) ) {

			// retain original info
			$this->row = $query;

			// copy all the db row info
			$this->id   = $id;
			$this->name = $query->name;
			$this->slug = $query->slug;
			$this->type = $query->type;
			$this->path = $query->path;
			$this->data = $query->data;
		}
		else {
			// new query object with default values
			$this->is_new      = TRUE;
			$this->data        = qw_default_query_data();
			$this->row['data'] = $this->data;
		}
	}

	/**
	 * Execute the entire query process
	 *
	 * @param bool $reset_post_data
	 * @return mixed|string|void
	 */
	function execute( $reset_post_data = false ) {
		$this
			->process_options()
			->execute_query()
			->theme_query();

		if ( $reset_post_data ){
			$this->reset_postdata();
		}

		return $this;
	}

	/**
	 * Allow array of option values to replace existing qw_query options.
	 * -- Should be executed before process_options()
	 *
	 * @param $options_override
	 * @param bool $full_override
	 *
	 * @return $this
	 */
	function override_options( $options_override, $full_override = FALSE ) {
		if ( $full_override ) {
			$this->data = $options_override;
		}
		else {
			// combine data and options_override to get $options
			$this->data = array_replace_recursive( (array) $this->data, $options_override );
		}

		return $this;
	}

	/**
	 * Process the row->data array into options and args
	 *
	 * @return $this
	 */
	function process_options() {
		// get the query options
		if ( ! $this->options ) {
			$this->options = $this->data;

			// build query_details
			$this->options['meta'] = array(
				'id'         => $this->id,
				'slug'       => $this->slug,
				'name'       => $this->name,
				'type'       => $this->type,
			);
		}

		// allow preview to alter options in own way
		if ( $this->is_preview ){
			$this->options = apply_filters( 'qw_pre_preview', $this->options );
		}

		// get formatted query arguments
		if ( ! $this->args ) {
			$this->args = apply_filters('qw_generate_query_args', array(), $this->options );
		}

		return $this;
	}

	/**
	 * Create the WP_Query()
	 */
	function execute_query() {
		$this->args = apply_filters( 'qw_pre_query', $this->args, $this->options );

		// set the new query
		$this->wp_query = new WP_Query( $this->args );

		return $this;
	}

	/**
	 * Template the qw_query output
	 */
	function theme_query() {
		// pre_render hook
		$this->options = apply_filters( 'qw_pre_render', $this->options, $this->args );

		// get the themed content
		$this->output = qw_template_query( $this->wp_query, $this->options );

		return $this;
	}

	/**
	 * Simple wrapper for wp_reset_postdata()
	 */
	function reset_postdata() {
		wp_reset_postdata();

		return $this;
	}

	/**
	 * Add a new handler item to the query
	 *
	 * @param $handler_type
	 * @param $item_type
	 * @param $values
	 *
	 * @return $this
	 */
	function add_handler_item( $handler_type, $item_type, $values ) {
		$all_handlers = qw_all_handlers();

		if ( isset( $all_handlers[ $handler_type ]['all_items'][ $item_type ] ) ) {
			$handler      = $all_handlers[ $handler_type ];
			$handler_item = $all_handlers[ $handler_type ]['all_items'][ $item_type ];

			// get existing items on the query
			$existing_items = array();
			if ( !empty( $options[ $handler_type ] ) ){
				$existing_items = $options[ $handler_type ];
			}
			// @todo deprecated callback
			else if ( is_callable( $handler['data_callback'] ) ) {
				$existing_items = call_user_func( $handler['data_callback'], $this->data );
			}

			// determine the weight and name of the new item based on
			// items that already exist in the query->data
			$weight    = 0;
			$instances = 0;
			foreach ( $existing_items as $name => $existing_item ) {
				$weight += 1;

				if ( $existing_item['hook_key'] == $handler_item['hook_key'] ) {
					$instances += 1;
				}
			}

			// create our new item
			$new_item = array(
				'hook_key' => $handler_item['hook_key'],
				'weight'   => $weight,
				'name'     => ( $instances > 0 ) ? $handler_item['hook_key'] . '_' . $instances : $handler_item['hook_key'],
			);

			// merge in values
			$new_item = array_replace_recursive( $new_item, $values );

			// set new item in the query args
			$this->data[ $handler['hook_key'] ][ $new_item['name'] ] = $new_item;
		}

		return $this;
	}
}

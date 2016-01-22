<?php

class QW_Update {

	/**
	 * db version => callable map
	 *
	 * @var array
	 */
	private $updates = array();

	private $last_db_version;

	function __construct( $last_db_version, $code_db_version ) {
		$this->last_db_version = $last_db_version;
		$this->this_db_version = $code_db_version;

		$this->updates = array(
			'2000' => false,
			'2001' => array( $this, 'update_2001' ),
			'2002' => array( $this, 'update_2002' ),
			'2003' => array( $this, 'update_2003' ),
			'2004' => array( $this, 'update_2004' ),
		);
	}

	/**
	 *
	 * @return mixed
	 */
	function needed(){
		return version_compare( $this->this_db_version, $this->last_db_version, '>' );
	}

	/**
	 * @return array
	 */
	function get_needed_updates(){
		$keys  = array_keys( $this->updates );
		$start = array_search( $this->last_db_version, $keys );
		$end   = array_search( $this->this_db_version, $keys );

		// if start version is found, add one to skip itself
		$start = ( $start !== FALSE ) ? $start + 1 : 0;

		return array_slice( $this->updates, $start, $end, true );
	}

	/**
	 *
	 */
	function perform_updates(){
		$updates = $this->get_needed_updates();
		$completed = array();

		if ( !empty( $updates ) && is_array( $updates ) ) {
			foreach( $updates as $update_version => $update_callback ){
				if ( is_callable( $update_callback ) ){
					call_user_func( $update_callback );
				}
				$completed[] = $update_version;
			}
		}

		update_option( 'qw_db_version', $this->this_db_version );
	}

	/**
	 *
	 */
	function update_2001(){
		// update goes here
	}
}

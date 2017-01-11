<?php

/**
 * Get all "Basic" types registered w/ QW
 *
 * @return array
 */
function qw_all_basic_settings()
{
	$basics = apply_filters( 'qw_basics', array() );
	$basics = qw_set_hook_keys( $basics );

	uasort( $basics, 'qw_cmp' );

	return $basics;
}

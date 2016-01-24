<?php

/**
 * Simple template function for admin stuff
 *
 * @param $__template_name
 * @param array $__args
 *
 * @return string
 */
function qw_admin_template( $__template_name, $__args = array() ){
	$__template_file = QW_PLUGIN_DIR . "/admin/templates/{$__template_name}.php";

	if ( file_exists( $__template_file ) ){
		ob_start();
		extract( $__args );
		include $__template_file;
		return ob_get_clean();
	}

	return '';
}
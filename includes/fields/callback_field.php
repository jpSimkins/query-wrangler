<?php
// add default fields to the hook filter
add_filter( 'qw_fields', 'qw_callback_field' );

/**
 * Add field to qw_fields
 *
 * @param $fields
 * @return mixed
 */
function qw_callback_field( $fields ) {

	$fields['callback_field'] = array(
		'title'            => __( 'Callback' ),
		'description'      => __( 'Arbitrarily execute a function.' ),
		'form_callback'    => 'qw_callback_field_form',
		'output_callback'  => 'qw_execute_the_callback',
		'output_arguments' => TRUE,
	);

	return $fields;
}

/**
 * Execute callback function
 *
 * @param $post
 * @param $field
 * @param $tokens
 *
 * @return bool|mixed|string
 */
function qw_execute_the_callback( $post, $field, $tokens ) {
	$returned = FALSE;
	$echoed   = FALSE;

	ob_start();
	if ( isset( $field['custom_output_callback'] ) && is_callable( $field['custom_output_callback'] ) ) {
		if ( isset( $field['include_output_arguments'] ) ) {
			$returned = call_user_func( $field['custom_output_callback'], $post, $field, $tokens );
		}
		else if ( isset( $field['include_text_arguments'] ) ) {

			// unset empty
			$callback_params = $field['parameters'];
			foreach ( $callback_params as $k => $v ) {
				if ( empty( $v ) ) {
					unset( $callback_params[ $k ] );
				}
			}

			$returned = call_user_func_array( $field['custom_output_callback'], $callback_params );
		}
		else {
			$returned = $field['custom_output_callback']();
		}
	}
	$echoed = ob_get_clean();

	// some functions both return and echo a value
	// so make sure to only show 1 instance of the callback
	if ( $returned ) {
		return $returned;
	}

	if ( $echoed ) {
		return $echoed;
	}
}

/**
 * GUI form
 *
 * @param $field
 */
function qw_callback_field_form( $field ) {
	if ( ! isset( $field['values']['parameters'] ) ) {
		$field['values']['parameters'] = array( '', '', '' );
	}

	$form = new QW_Form_Fields( array(
		'form_field_prefix' => $field['form_prefix'],
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name' => 'custom_output_callback',
		'title' => __( 'Callback' ),
		'description' => __( 'Provide an existing function name. This function
			will be executed during the loop of this query.' ),
		'value' => isset( $field['values']['custom_output_callback'] ) ? $field['values']['custom_output_callback'] : '',
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'checkbox',
		'name' => 'include_output_arguments',
		'title' => __( 'Include additional information' ),
		'help' => __( 'If checked, the callback will be executed with
			the parameters $post, $field, and $tokens. The $post parameter is a
			WordPress $post object, and the $field parameter is the query
			wrangler field settings, and the $tokens parameter includes all the
			available token values.' ),
		'value' => !empty( $field['values']['include_output_arguments'] ) ? 1 : 0,
	) );

	print $form->render_field( array(
		'type' => 'checkbox',
		'name' => 'include_text_arguments',
		'title' => __( 'Include text parameters' ),
		'help' => __( 'If checked, the callback will be executed with
			the following fields as parameters. Do not check both of the above
			boxes. Choose the one appropriate for your needs.' ),
		'value' => !empty( $field['values']['include_text_arguments'] ) ? 1 : 0,
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name_prefix' => '[parameters]',
		'name' => '0',
		'title' => __( 'Parameter 0' ),
		'value' => $field['values']['parameters'][0],
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name_prefix' => '[parameters]',
		'name' => '1',
		'title' => __( 'Parameter 1' ),
		'value' => $field['values']['parameters'][1],
		'class' => array( 'qw-js-title' ),
	) );

	print $form->render_field( array(
		'type' => 'text',
		'name_prefix' => '[parameters]',
		'name' => '2',
		'title' => __( 'Parameter 2' ),
		'value' => $field['values']['parameters'][2],
		'class' => array( 'qw-js-title' ),
	) );
}


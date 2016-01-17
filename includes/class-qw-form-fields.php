<?php

class QW_Form_Fields {
	public $default_form_args = array(
		'id' => '',
		'class' => array(),
		'method' => 'POST',
		'action' => '',
		'attributes' => array(),
		'form_style' => 'flat',
	);

	public $form_args = array(
		'form_field_prefix' => '',
	);

	public $form_styles = array();
	public $field_types = array();

	public $default_field_args = array(
		'title' => '',
		'description' => '',
		'help' => '',
		'type' => 'text',
		'class' => array(),
		'value' => '',
		'name' => '',

		// top-lvl][mid-lvl][bottom-lvl
		'name_prefix' => '',

		// additional special attributes like size, rows, cols, etc
		'attributes' => array(),

		// only for some field types
		// options = array(),

		# generated automatically
		#'form_name' => '',
		#'id' => '',
	);

	public $fields = array();


	function __construct( $form_args = array() ){
		$this->form_args = array_replace( $this->default_form_args, $form_args );
		$this->register_default_field_types();
		$this->register_default_form_styles();
	}

	/**
	 *
	 */
	function register_default_form_styles(){
		$this->form_styles = array(
			'flat' => array(
				'form_open' => array( $this, 'form_open_flat' ),
				'form_close' => array( $this, 'form_close_flat' ),
				'field_wrapper' => array( $this, 'field_wrapper_flat' ),
			),
			'settings_table' => array(
				'form_open' => array( $this, 'form_open_settings_table' ),
				'form_close' => array( $this, 'form_close_settings_table' ),
				'field_wrapper' => array( $this, 'field_wrapper_settings_table' ),
			)
		);
	}

	/**
	 *
	 */
	function register_default_field_types(){
		$this->field_types = array_replace( $this->field_types, array(
			'text' => array( $this, 'template_input' ),
			'hidden' => array( $this, 'template_input' ),
			'number' => array( $this, 'template_input' ),
			'email' => array( $this, 'template_input' ),
			'checkbox' => array( $this, 'template_checkbox' ),
			'submit' => array( $this, 'template_input' ),
			'button' => array( $this, 'template_input' ),
			'textarea' => array( $this, 'template_textarea' ),
			'checkboxes' => array( $this, 'template_checkboxes' ),
			'select' => array( $this, 'template_select' ),
			'item_list' => array( $this, 'template_item_list' ),
		) );
	}

	/**
	 * @return array
	 */
	function get_form_style(){
		// default to flat style
		$style = $this->form_styles['flat'];

		if ( isset( $this->form_styles[ $this->form_args['form_style'] ] ) ){
			$style = $this->form_styles[ $this->form_args['form_style'] ];
		}

		return $style;
	}

	/**
	 * Merge default and set attributes for the html form element
	 *
	 * @return array
	 */
	function get_form_attributes(){
		$atts_keys=  array( 'id', 'action', 'method', 'class' );
		$attributes = array();

		foreach( $atts_keys as $key ){
			if ( !empty( $this->form_args[ $key ] ) ) {
				$attributes[ $key ] = $this->form_args[ $key ];
			}
		}

		if ( !empty( $this->form_args['attributes'] ) ) {
			$attributes = array_replace( $attributes, $this->form_args['attributes'] );
		}

		if ( !empty( $attributes['class'] ) ) {
			$attributes['class'] = implode( ' ', $attributes['class'] );
		}

		return $attributes;
	}

	/**
	 * Opening form html
	 *
	 * @return string
	 */
	function open(){
		$output = '<form ' . $this->attributes( $this->get_form_attributes() ). '>';

		$style = $this->get_form_style();

		if ( is_callable( $style['form_open'] ) ){
			$output.= call_user_func( $style['form_open'] );
		}

		return $output;
	}

	/**
	 * Closing form html
	 *
	 * @return string
	 */
	function close(){
		$output = '';

		$style = $this->get_form_style();

		if ( is_callable( $style['form_close'] ) ){
			$output.= call_user_func( $style['form_close'] );
		}

		$output.= '</form>';

		return $output;
	}

	/**
	 * Execute the filters and methods that render a field
	 *
	 * @param $field
	 *
	 * @return string
	 */
	function render_field( $field ){
		$field = $this->make_field( $field );
		$field_html = '';

		// template the field
		if ( isset( $this->field_types[ $field['type'] ] ) ){
			ob_start();
			call_user_func( $this->field_types[ $field['type'] ], $field );
			$field_html = ob_get_clean();
		}

		if ( empty( $field['title'] ) && empty( $field['description'] ) && empty( $field['help'] ) ) {
			return $field_html;
		}

		// template the wrapper
		$wrapper_html = $field_html;
		$style = $this->get_form_style();

		if ( is_callable( $style['field_wrapper'] ) ){
			ob_start();
			call_user_func( $style['field_wrapper'], $field, $field_html );
			$wrapper_html = ob_get_clean();
		}

		return $wrapper_html;
	}

	/**
	 * Preprocess field
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function make_field( $args = array() ){
		$field = array_replace( $this->default_field_args, $args );
		$field['name'] = sanitize_title( $args['name'] );

		// build the field's entire form name
		$field['form_name'] = '';
		if ( !empty( $this->form_args['form_field_prefix'] ) ){
			$field['form_name'].= $this->form_args['form_field_prefix'];
		}
		if ( !empty( $field['name_prefix'] ) ) {
			$field['form_name'].= $field['name_prefix'];
		}
		$field['form_name'].= '[' . $field['name'] . ']';

		// gather field classes
		if ( !is_array( $field['class'] ) ){
			$field['class'] = array( $field['class'] );
		}
		$field['class'][] = 'qw-field';
		$field['class'][] = 'qw-field-type-' . $field['type'];
		$field['class'] = implode( ' ', $field['class'] );

		if ( empty( $field['id'] ) ) {
			$field['id'] = 'edit--' . sanitize_title( $field['form_name'] );
		}
		return $field;
	}

	/**
	 * Simple conversion of an array to tml attributes string
	 *
	 * @param array
	 * @return string
	 */
	function attributes( $array = array() ){
		$html = '';

		foreach( $array as $key => $value ){
			if ( !empty( $value ) ) {
				$value = esc_attr( $value );
				$html .= " {$key}='{$value}'";
			}
		}

		return $html;
	}

	/**
	 * Single checkbox field has a hidden predecessor to provide a default value
	 *
	 * @param $field
	 */
	function template_checkbox( $field ){
		$hidden = array_replace( $field, array(
				'type' => 'hidden',
				'value' => 0,
				'id' => $field['id'] . '--hidden',
				'attributes' => array(),
				'class' => 'qw-field-hidden',
		));
		$this->template_input( $hidden );

		if ( isset( $field['value'] ) && $field['value'] ) {
			$field['attributes']['checked'] = 'checked';
		}
		$field['value'] = 'on';
		$this->template_input( $field );
	}

	/**
	 * Generic input field
	 *
	 * @param $field
	 */
	function template_input( $field ) {
		?>
		<input type="<?php echo esc_attr( $field['type'] ) ?>"
				name="<?php echo esc_attr( $field['form_name'] ); ?>"
				id="<?php echo esc_attr( $field['id'] ); ?>"
				class="<?php echo esc_attr( $field['class'] ); ?>"
				value="<?php echo esc_attr( $field['value'] ); ?>"
				<?php echo $this->attributes( $field['attributes'] ); ?>
		>
		<?php
	}

	/**
	 * Textarea
	 *
	 * @param $field
	 */
	function template_textarea( $field ) {
		?>
		<textarea name="<?php echo esc_attr( $field['form_name'] ); ?>"
				id="<?php echo esc_attr( $field['id'] ); ?>"
				class="<?php echo esc_attr( $field['class'] ); ?>"
				<?php echo $this->attributes( $field['attributes'] ); ?>
		><?php echo qw_textarea( $field['value'] ); ?></textarea>
		<?php
	}

	/**
	 * Group of checkboxes
	 *  - expects an array of values as $field['value']
	 *
	 * @param $field
	 */
	function template_checkboxes( $field ){
		$field['class'].= ' qw-checkboxes-item';
		$i = 0;
		foreach( $field['options'] as $value => $label ){
			?>
				<div class="qw-checkboxes-wrapper">
					<label for="<?php echo esc_attr( $field['id'] ); ?>--<?php echo $i; ?>">
						<input type="checkbox"
							name="<?php echo esc_attr( $field['form_name'] ); ?>[<?php echo esc_attr( $value ); ?>]"
							id="<?php echo esc_attr( $field['id'] ); ?>--<?php echo $i; ?>"
							class="<?php echo esc_attr( $field['class'] ); ?>"
							value="<?php echo esc_attr( $label ); ?>"
							<?php checked( isset( $field['value'][ $value ] ) ); ?>
		                >
						<?php echo $label; ?>
					</label>
				</div>
			<?php
			$i++;
		}
	}

	/**
	 * Select box
	 *  - expects an array of options as $field['options']
	 *
	 * @param $field
	 */
	function template_select( $field ){
		?>
		<select name="<?php echo esc_attr( $field['form_name'] ); ?>"
		       id="<?php echo esc_attr( $field['id'] ); ?>"
		       class="<?php echo esc_attr( $field['class'] ); ?>"
				<?php echo $this->attributes( $field['attributes'] ); ?> >
			<?php foreach( $field['options'] as $value => $option ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $field['value'] ); ?>><?php echo esc_html( $option ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Simple item list
	 *  - expects an array of items as $field['items']
	 *
	 * @param $field
	 */
	function template_item_list( $field ){
		?>
		<ul class="<?php echo esc_attr( $field['class'] ); ?>">
			<?php
			foreach ( $field['items'] as $item ) { ?>
				<li><?php print $item; ?></li>
				<?php
			}
			?>
		</ul>
		<?php
	}


	// **** styles ***** //


	/**
	 * Settings Table
	 *
	 * @param $field
	 * @param $field_html
	 */
	function field_wrapper_settings_table( $field, $field_html ){
		?>
		<tr  id="<?php echo esc_attr( $field['id'] ) ;?>--wrapper"
		     class="qw-field-wrapper">
			<th>
				<label for="<?php echo esc_attr( $field['id'] ); ?>" class="qw-field-label">
					<?php echo $field['title']; ?>
				</label>
			</th>
			<td>
				<?php echo $field_html; ?>

				<?php if ( !empty( $field['description'] ) ) : ?>
					<p class="description"><?php echo $field['description']; ?></p>
				<?php endif; ?>

				<?php if ( !empty($field['help']) ) : ?>
					<p class="description"><?php echo $field['help']; ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	function form_open_settings_table(){
		return '<table class="form-table">';
	}

	function form_close_settings_table(){
		return '</table>';
	}

	/**
	 * Flat form style
	 *
	 * @param $field
	 * @param $field_html
	 */
	function field_wrapper_flat( $field, $field_html ){
		?>
		<div id="<?php echo esc_attr( $field['id'] ) ;?>--wrapper"
		     class="qw-field-wrapper">
			<label for="<?php echo esc_attr( $field['id'] ); ?>" class="qw-field-label">
				<?php echo $field['title']; ?>
			</label>

			<?php if ( !empty( $field['description'] ) ) : ?>
				<p class="description"><?php echo $field['description']; ?></p>
			<?php endif; ?>

			<?php echo $field_html; ?>

			<?php if ( !empty($field['help']) ) : ?>
				<p class="description"><?php echo $field['help']; ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	function form_open_flat(){
		return '<div class="qw-form">';
	}

	function form_close_flat(){
		return '</div>';
	}
}


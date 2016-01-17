<?php

class QW_Form_Fields {
	public $default_form_args = array(
		'id' => '',
		'class' => array(),
		'method' => 'POST',
		'action' => '',
	);

	public $form_args = array(
		'form_field_prefix' => '',
	);

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
	}

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
	 * Execute the filters and methods that render a field
	 *
	 * @param $field
	 *
	 * @return string
	 */
	function render_field( $field ){
		$field = $this->make_field( $field );
		$field_html = '';

		if ( isset( $this->field_types[ $field['type'] ] ) ){
			ob_start();
			call_user_func( $this->field_types[ $field['type'] ], $field );
			$field_html = ob_get_clean();
		}

		if ( empty( $field['title'] ) && empty( $field['description'] ) && empty( $field['help'] ) ) {
			return $field_html;
		}

		ob_start();
		$this->render_flat_wrapper( $field, $field_html );
		$wrapper_html = ob_get_clean();

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
		if ( $this->form_args['form_field_prefix'] ){
			$field['form_name'].= $this->form_args['form_field_prefix'];
		}
		if ( $field['name_prefix'] ) {
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

		$field['id'] = 'edit--' . sanitize_title( $field['form_name'] );
		return $field;
	}

	/**
	 * @param array $array
	 *
	 * @return string
	 */
	function attributes( $array = array() ){
		$html = '';

		foreach( $array as $key => $value ){
			$value = esc_attr( $value );
			$html.= " {$key}='{$value}'";
		}

		return $html;
	}

	/**
	 * @param $field
	 * @param $field_html
	 */
	function render_flat_wrapper( $field, $field_html ){
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
		><?php echo esc_textarea( $field['value'] ); ?></textarea>
		<?php
	}

	function template_checkboxes( $field ){
		$i = 0;
		foreach( $field['options'] as $value => $option ){
			$checkbox = array_replace( $field, array(
				'type' => 'checkbox',
				'id' => $field['id'] . "--{$i}",
				'name' => $field['name'] . '[]',
			));

			if ( in_array( $value, $field['value'] ) ){
				$checkbox['attributes']['checked'] = 'checked';
			}

			$this->template_input( $checkbox );

			$i++;
		}
	}

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
}


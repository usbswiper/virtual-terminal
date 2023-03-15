<?php
/**
 * Check Usb_Swiper_Input_Fields class exists or not.
 */
if( !class_exists('Usb_Swiper_Input_Fields')) {

    /**
     * The Usb_Swiper_Input_Fields class is responsible for the all input fields html.
     *
     * @since 1.0.0
     */
	class Usb_Swiper_Input_Fields{

	    public $defaults;

		public function __construct() {

		    $this->defaults = array(
				'type' => 'text',
				'label' => '',
				'id' => '',
				'name' => '',
				'default' => '',
				'value' => '',
				'placeholder' => '',
				'class' => '',
				'wrapper' => true,
				'wrapper_class' => '',
				'required' => false,
				'disabled' => false,
				'checked' => false,
				'readonly' => false,
				'description' => false,
				'attributes' => array(),
				'options' => array(),
				'multiple' => false,
                'autocomplete' => 'off',
                'settings' => array(),
                'is_symbol' => false,
                'symbol' => '',
                'symbol_wrap_class' => '',
			);
		}

        /**
         * Parse the arguments.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return array
         */
		public function parse_args( $args ) {

			return wp_parse_args( $args, $this->defaults );
		}

        /**
         * Get the description field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function description( $args = array() ) {

			$description = !empty($args['description']) ? $args['description'] : '';

			if( empty($description)) {
				return '';
			}

			ob_start();

				echo '<p class="description">'.$description.'</p>';

			return ob_get_clean();
		}

        /**
         * Get the input field wrapper start html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return string
         */
		public function wrapper_start( $args = array()) {

			if( empty($args['wrapper'])) {
				return '';
			}

			$wrapper_class = !empty($args['wrapper_class']) ? $args['wrapper_class'] : '';

			return '<div class="input-field-wrap '.$wrapper_class.'">';
		}

        /**
         * Get the input field wrapper end html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return string
         */
		public function wrapper_end( $args = array()) {

			if( empty($args['wrapper'])) {
				return '';
			}

			return '</div>';
		}

        /**
         * Get the input field label html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return string
         */
		public function label( $args = array()) {

			if( empty($args['label'])) {
				return '';
			}

            return '<label for="'.$args['id'].'">'.$args['label'].'</label>';
		}

        /**
         * Get the text input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function text( $args = array() ) {

			$args = $this->parse_args($args);

			$placeholder = !empty($args['placeholder']) ? $args['placeholder'] : '';
			$autocomplete = !empty($args['autocomplete']) ? $args['autocomplete'] : '';

			$default = !empty($args['default']) ? $args['default'] : '';
			$value = !empty($args['value']) ? $args['value'] : $default;

			$required = !empty( $args['required'] ) ? 'required' : '';
			$disable = !empty( $args['disabled'] ) ? 'disabled' : '';
			$readonly = !empty( $args['readonly'] ) ? 'readonly' : '';

			$attributes = '';
			if( !empty($args['attributes']) && is_array($args['attributes'])) {
				foreach ($args['attributes'] as $key => $attribute ) {
					$attributes .= $key.'="'.$attribute.'"';
				}
			}

			$is_symbol = !empty( $args['is_symbol'] ) ? $args['is_symbol'] : '';
			$symbol = !empty( $args['symbol'] ) ? $args['symbol'] : '';
			$symbol_wrap_class = !empty( $args['symbol_wrap_class'] ) ? $args['symbol_wrap_class'] : '';

			ob_start();

			echo $this->wrapper_start($args);

			    echo  $this->label($args);

			    if( $is_symbol ) { ?>
			        <div class="sign <?php echo $symbol_wrap_class; ?>">
			            <div class="sign-symbol"><?php echo $symbol; ?></div>
			            <input autocomplete="<?php echo $autocomplete; ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $required.' '.$disable.' '.$readonly.' '.$attributes; ?> class="<?php echo !empty($args['class']) ? $args['class'] : ''; ?>" type="<?php echo !empty($args['type']) ? $args['type'] : ''; ?>" name="<?php echo !empty($args['name']) ? $args['name'] : ''; ?>" id="<?php echo !empty($args['id']) ? $args['id'] : ''; ?>" value="<?php echo $value; ?>">
                    </div>
			    <?php } else { ?>
                    <input autocomplete="<?php echo $autocomplete; ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $required.' '.$disable.' '.$readonly.' '.$attributes; ?> class="<?php echo !empty($args['class']) ? $args['class'] : ''; ?>" type="<?php echo !empty($args['type']) ? $args['type'] : ''; ?>" name="<?php echo !empty($args['name']) ? $args['name'] : ''; ?>" id="<?php echo !empty($args['id']) ? $args['id'] : ''; ?>" value="<?php echo $value; ?>">
                    <?php
                }
			    echo  $this->description($args);

			echo $this->wrapper_end($args);

			$html = ob_get_contents();
			ob_get_clean();

			return $html;
		}

        /**
         * Get the number input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function number( $args = array() ) {

			return $this->text($args);
		}

        /**
         * Get the email input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function email( $args = array() ) {

			return $this->text($args);
		}

        /**
         * Get the password input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function password( $args = array() ) {

			return $this->text($args);
		}

        /**
         * Get the search input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function search( $args = array() ) {

			return $this->text($args);
		}

        /**
         * Get the tel input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function tel( $args = array() ) {

			return $this->text($args);
		}

        /**
         * Get the color input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function color( $args = array() ) {

			return $this->text($args);
		}

        /**
         * Get the date input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function date( $args = array() ) {

			return $this->text($args);
		}

        /**
         * Get the url input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.s
         * @return false|string
         */
		public function url( $args = array() ) {

			return $this->text($args);
		}

        /**
         * Get the textarea input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function textarea( $args ) {

			$args = $this->parse_args($args);

			$placeholder = !empty($args['placeholder']) ? $args['placeholder'] : '';
			$autocomplete = !empty($args['autocomplete']) ? $args['autocomplete'] : '';

			$default = !empty($args['default']) ? $args['default'] : '';
			$value = !empty($args['value']) ? $args['value'] : $default;

			$required = !empty( $args['required'] ) ? 'required' : '';
			$disable = !empty( $args['disabled'] ) ? 'disabled' : '';
			$readonly = !empty( $args['readonly'] ) ? 'readonly' : '';
			$class = !empty( $args['class'] ) ? $args['class'] : '';

			$attributes = '';
			if( !empty($args['attributes']) && is_array($args['attributes'])) {
				foreach ($args['attributes'] as $key => $attribute ) {
					$attributes .= $key.'="'.$attribute.'"';
				}
			}

			ob_start();

			echo $this->wrapper_start($args);

			echo $this->label($args);

			?>
            <textarea <?php echo $required.' '.$disable.' '.$readonly.' '.$attributes; ?> autocomplete="<?php echo $autocomplete; ?>" placeholder="<?php echo $placeholder; ?>" class="<?php echo $class; ?>" name="<?php echo !empty($args['name']) ? $args['name'] : ''; ?>" id="<?php echo !empty($args['id']) ? $args['id'] : ''; ?>"><?php echo $value; ?></textarea>
			<?php

			echo $this->description($args);

			echo $this->wrapper_end($args);

			$html = ob_get_contents();
			ob_get_clean();

			return $html;
		}

        /**
         * Get the select input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function select( $args ) {

			$args = $this->parse_args($args);

			$autocomplete = !empty($args['autocomplete']) ? $args['autocomplete'] : '';

			$default = !empty($args['default']) ? $args['default'] : '';
			$value = !empty($args['value']) ? $args['value'] : $default;

			$required = !empty( $args['required'] ) ? 'required' : '';
			$disable = !empty( $args['disabled'] ) ? 'disabled' : '';
			$readonly = !empty( $args['readonly'] ) ? 'readonly' : '';
			$class = !empty( $args['class'] ) ? $args['class'] : '';
			$options = !empty( $args['options'] ) ? $args['options'] : '';

			$attributes = '';
			if( !empty($args['attributes']) && is_array($args['attributes'])) {
				foreach ($args['attributes'] as $key => $attribute ) {
					$attributes .= $key.'="'.$attribute.'"';
				}
			}

			$name = !empty($args['name']) ? $args['name'] : '';
			$id = !empty($args['id']) ? $args['id'] : '';

			ob_start();

			echo $this->wrapper_start($args);

			echo $this->label($args);

			?>
            <select class="<?php echo $class; ?>" <?php echo $required.' '.$disable.' '.$readonly.' '.$attributes; ?> autocomplete="<?php echo $autocomplete; ?>" name="<?php echo $name; ?>" id="<?php echo $id; ?>">
                <?php
                if( !empty($options) && is_array($options)) {
                    foreach ($options as $key => $option ) {
                        ?>
                        <option <?php selected($key, $value ); ?> value="<?php echo $key ?>"><?php echo $option; ?></option>
                        <?php
                    }
                }
                ?>
            </select>
			<?php

			echo $this->description($args);

			echo $this->wrapper_end($args);

			$html = ob_get_contents();
			ob_get_clean();

			return $html;
		}

        /**
         * Get the multiselect input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function multiselect( $args ) {

			$args = $this->parse_args($args);

			$autocomplete = !empty($args['autocomplete']) ? $args['autocomplete'] : '';

			$default = !empty($args['default']) ? $args['default'] : '';
			$value = !empty($args['value']) ? $args['value'] : $default;

			$required = !empty( $args['required'] ) ? 'required' : '';
			$disable = !empty( $args['disabled'] ) ? 'disabled' : '';
			$readonly = !empty( $args['readonly'] ) ? 'readonly' : '';
			$class = !empty( $args['class'] ) ? $args['class'] : '';
			$options = !empty( $args['options'] ) ? $args['options'] : '';

			$attributes = '';
			if( !empty($args['attributes']) && is_array($args['attributes'])) {
				foreach ($args['attributes'] as $key => $attribute ) {
					$attributes .= $key.'="'.$attribute.'"';
				}
			}

			$name = !empty($args['name']) ? $args['name'] : '';
			$id = !empty($args['id']) ? $args['id'] : '';

			ob_start();

			echo $this->wrapper_start($args);

			echo $this->label($args);

			?>
            <select multiple class="<?php echo $class; ?>" <?php echo $required.' '.$disable.' '.$readonly.' '.$attributes; ?> autocomplete="<?php echo $autocomplete; ?>" name="<?php echo $name; ?>[]" id="<?php echo $id; ?>">
				<?php
				if( !empty($options) && is_array($options)) {
					foreach ($options as $key => $option ) {

					    $selected = '';
						if( !empty($value) && in_array($key, $value)) {
						    $selected = 'selected';
						}
						?>
                        <option <?php echo $selected; ?> value="<?php echo $key ?>"><?php echo $option; ?></option>
						<?php
					}
				}
				?>
            </select>
			<?php

			echo $this->description($args);

			echo $this->wrapper_end($args);

			$html = ob_get_contents();
			ob_get_clean();

			return $html;
		}

        /**
         * Get the checkbox input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function checkbox( $args ) {

			$args = $this->parse_args($args);

			$autocomplete = !empty($args['autocomplete']) ? $args['autocomplete'] : '';
			$id = !empty($args['id']) ? $args['id'] : '';
			$label = !empty($args['label']) ? $args['label'] : '';
			$name = !empty($args['name']) ? $args['name'] : '';
			$description = !empty($args['description']) ? $args['description'] : '';

			$default = !empty($args['default']) ? $args['default'] : '';
			$value = !empty($args['value']) ? $args['value'] : $default;

			$required = !empty( $args['required'] ) ? 'required' : '';
			$disable = !empty( $args['disabled'] ) ? 'disabled' : '';
			$readonly = !empty( $args['readonly'] ) ? 'readonly' : '';
			$checked = !empty( $args['checked'] ) ? 'checked' : '';

			$attributes = '';
			if( !empty($args['attributes']) && is_array($args['attributes'])) {
				foreach ($args['attributes'] as $key => $attribute ) {
					$attributes .= $key.'="'.$attribute.'"';
				}
			}

			ob_start();

			echo $this->wrapper_start($args);

			?>
            <label for="<?php echo $id; ?>" class="checkbox-label"><?php echo $label; ?></label>
            <div class="checkmark-container">
                <input <?php echo $required.' '.$disable.' '.$readonly.' '.$checked.' '.$attributes; ?> autocomplete="<?php echo $autocomplete; ?>" type="checkbox" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo !empty($value) ? $value : "true"; ?>"><?php echo $description; ?>
                <span class="checkmark"></span>
            </div>
            <?php
			echo $this->wrapper_end($args);

			$html = ob_get_contents();
			ob_get_clean();

			return $html;
		}

        /**
         * Get the multicheckbox input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function multicheckbox( $args ) {

			$args = $this->parse_args($args);

			$options = !empty($args['options']) ? $args['options'] : '';

			ob_start();

			echo $this->wrapper_start($args);

			if( !empty($options) && is_array($options)) {
			    foreach ( $options as $key => $option ) {

			        $checked = '';
			        if( !empty($args['value']) && is_array($args['value']) && in_array($key, $args['value'])) {
				        $checked = true;
			        }

			        $name = !empty($args['name']) ? $args['name'] : '';
			        $id = !empty($args['id']) ? $args['id'] : '';
			        $checkbox = $args;
				    $checkbox['value'] = $key;
				    $checkbox['name'] = $name.'[]';
				    $checkbox['id'] = $id.'_'.$key;
				    $checkbox['checked'] = $checked;

			        echo $this->checkbox($checkbox);
			    }
			}

			echo $this->wrapper_end( $args );

			$html = ob_get_contents();
			ob_get_clean();

			return $html;
		}

        /**
         * Get the radio input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function radio( $args ) {

			$args = $this->parse_args($args);

			$autocomplete = !empty($args['autocomplete']) ? $args['autocomplete'] : '';
			$id = !empty($args['id']) ? $args['id'] : '';
			$name = !empty($args['name']) ? $args['name'] : '';
			$options = !empty($args['options']) ? $args['options'] : '';

			$default = !empty($args['default']) ? $args['default'] : '';
			$value = !empty($args['value']) ? $args['value'] : $default;

			$required = !empty( $args['required'] ) ? 'required' : '';
			$disable = !empty( $args['disabled'] ) ? 'disabled' : '';
			$readonly = !empty( $args['readonly'] ) ? 'readonly' : '';

			ob_start();

			echo $this->wrapper_start($args);

			?>
            <div class="radio-title">
                <label><?php echo !empty($args['label']) ? $args['label'] : ''; ?></label>
            </div>
            <div class="radio-options">
            <?php
                if( !empty($options) && is_array($options) ) {
                    foreach ( $options as $key => $option ) {
                        ?>
                        <label for="<?php echo $id.'_'.$key;?>" class="radio-container" ><input autocomplete="<?php echo $autocomplete; ?>" <?php echo $required.' '.$disable.' '.$readonly; ?> <?php checked( $value, $key); ?> type="radio" name="<?php echo $name; ?>" id="<?php echo $id.'_'.$key;?>" value="<?php echo $key; ?>"><?php echo $option; ?><span class="checkmark"></span></label>
                        <?php
                    }
                }
			echo '</div>';

            echo $this->description($args);

			echo $this->wrapper_end($args);

			$html = ob_get_contents();
			ob_get_clean();

			return $html;
		}

        /**
         * Get the editor input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function editor( $args ) {

			$args = $this->parse_args($args);

			$settings = !empty($args['settings']) ? $args['settings'] : array();
			$name = !empty($args['name']) ? $args['name'] : '';
			$value = !empty($args['value']) ? $args['value'] : '';

			ob_start();

			echo $this->wrapper_start($args);

			echo $this->label($args);

                wp_editor( $value, $name, $settings );

			echo $this->description($args);

			echo $this->wrapper_end($args);

			$html = ob_get_contents();
			ob_get_clean();

			return $html;
		}

        /**
         * Get the hidden input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function hidden( $args = array() ) {

			$args = $this->parse_args($args);

			$default = !empty($args['default']) ? $args['default'] : '';
			$value = !empty($args['value']) ? $args['value'] : $default;

			ob_start();

			?>
            <input type="hidden" name="<?php echo !empty($args['name']) ? $args['name'] : ''; ?>" id="<?php echo !empty($args['id']) ? $args['id'] : ''; ?>" value="<?php echo $value; ?>" >
			<?php

			return ob_get_clean();
		}

        /**
         * Get the button input field html.
         *
         * @since 1.0.0
         *
         * @param array $args get all arguments.
         * @return false|string
         */
		public function button( $args = array()) {

		    $args = $this->parse_args($args);

			$name = !empty($args['name']) ? $args['name'] : '';
			$class = !empty($args['class']) ? $args['class'] : '';
			$id = !empty($args['id']) ? $args['id'] : '';
			$value = !empty($args['value']) ? $args['value'] : '';
			$btn_type = !empty($args['btn_type']) ? $args['btn_type'] : 'button';
			ob_start();

			$attributes = '';
			if( !empty($args['attributes']) && is_array($args['attributes'])) {
				foreach ($args['attributes'] as $key => $attribute ) {
					$attributes .= $key.'="'.$attribute.'"';
				}
			}

			?>
            <button <?php echo $attributes; ?> name="<?php echo $name; ?>" class="<?php echo $class; ?>" id="<?php echo $id; ?>" type="<?php echo $btn_type; ?>"><?php echo $value; ?></button>
            <?php

			$html = ob_get_contents();
			ob_get_clean();

			return $html;
		}
	}
}

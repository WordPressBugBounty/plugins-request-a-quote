<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'EMD_MB_Datetime_Field' ) )
{
	class EMD_MB_Datetime_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			$url_css = EMD_MB_CSS_URL . 'jqueryui';
			wp_register_script( 'jquery-ui-timepicker', EMD_MB_JS_URL . 'jqueryui/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7', true );
			wp_enqueue_style( 'jquery-ui-timepicker-css', "{$url_css}/jquery-ui-timepicker-addon.css");
			$deps = array( 'jquery-ui-datepicker', 'jquery-ui-timepicker' );

                        $locale = get_locale();
                        $date_vars['closeText'] = __('Done','request-a-quote');
                        $date_vars['prevText'] = __('Prev','request-a-quote');
                        $date_vars['nextText'] = __('Next','request-a-quote');
                        $date_vars['currentText'] = __('Today','request-a-quote');
                        $date_vars['monthNames'] = Array(__('January','request-a-quote'),__('February','request-a-quote'),__('March','request-a-quote'),__('April','request-a-quote'),__('May','request-a-quote'),__('June','request-a-quote'),__('July','request-a-quote'),__('August','request-a-quote'),__('September','request-a-quote'),__('October','request-a-quote'),__('November','request-a-quote'),__('December','request-a-quote'));
                        $date_vars['monthNamesShort'] = Array(__('Jan','request-a-quote'),__('Feb','request-a-quote'),__('Mar','request-a-quote'),__('Apr','request-a-quote'),__('May','request-a-quote'),__('Jun','request-a-quote'),__('Jul','request-a-quote'),__('Aug','request-a-quote'),__('Sep','request-a-quote'),__('Oct','request-a-quote'),__('Nov','request-a-quote'),__('Dec','request-a-quote'));
                        $date_vars['dayNames'] = Array(__('Sunday','request-a-quote'),__('Monday','request-a-quote'),__('Tuesday','request-a-quote'),__('Wednesday','request-a-quote'),__('Thursday','request-a-quote'),__('Friday','request-a-quote'),__('Saturday','request-a-quote'));
                        $date_vars['dayNamesShort'] = Array(__('Sun','request-a-quote'),__('Mon','request-a-quote'),__('Tue','request-a-quote'),__('Wed','request-a-quote'),__('Thu','request-a-quote'),__('Fri','request-a-quote'),__('Sat','request-a-quote'));
                        $date_vars['dayNamesMin'] = Array(__('Su','request-a-quote'),__('Mo','request-a-quote'),__('Tu','request-a-quote'),__('We','request-a-quote'),__('Th','request-a-quote'),__('Fr','request-a-quote'),__('Sa','request-a-quote'));
                        $date_vars['weekHeader'] = __('Wk','request-a-quote');

			$time_vars['timeOnlyTitle'] = __('Choose Time','request-a-quote');
			$time_vars['timeText'] = __('Time','request-a-quote');
			$time_vars['hourText'] = __('Hour','request-a-quote');
			$time_vars['minuteText'] = __('Minute','request-a-quote');
			$time_vars['secondText'] = __('Second','request-a-quote');
			$time_vars['millisecText'] = __('Millisecond','request-a-quote');
			$time_vars['timezoneText'] = __('Time Zone','request-a-quote');
			$time_vars['currentText'] = __('Now','request-a-quote');
			$time_vars['closeText'] = __('Done','request-a-quote');

                        $vars['date'] = $date_vars;
                        $vars['time'] = $time_vars;
                        $vars['locale'] = $locale;

			wp_enqueue_script( 'emd-mb-datetime', EMD_MB_JS_URL . 'datetime.js', $deps, EMD_MB_VER, true );
                        wp_localize_script( 'emd-mb-datetime', 'dtvars', $vars);
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			if($meta != '')
                        {
                                if($field['js_options']['timeFormat'] == 'hh:mm')
                                {
                                        $getformat = 'Y-m-d H:i';
                                }
                                else
                                {
                                        $getformat = 'Y-m-d H:i:s';
                                }
				if(DateTime::createFromFormat($getformat,$meta)){
                                	$meta = DateTime::createFromFormat($getformat,$meta)->format(self::translate_format($field));
				}
                        }
                        return sprintf(
                                '<input type="text" class="emd-mb-datetime" name="%s" value="%s" id="%s" size="%s" data-options="%s" readonly/>',
                                $field['field_name'],
                                $meta,
                                isset( $field['clone'] ) && $field['clone'] ? '' : $field['id'],
                                $field['size'],
                                esc_attr( json_encode( $field['js_options'] ) )
                        );
		}

		/**
		 * Calculates the timestamp from the datetime string and returns it
		 * if $field['timestamp'] is set or the datetime string if not
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return string|int
		 */
		/*static function value( $new, $old, $post_id, $field )
		{
			if ( !$field['timestamp'] )
				return $new;

			$d = DateTime::createFromFormat( self::translate_format( $field ), $new );
			return $d ? $d->getTimestamp() : 0;
		}*/

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'size'       => 30,
				'js_options' => array(),
				'timestamp'  => false,
			) );

			// Deprecate 'format', but keep it for backward compatible
			// Use 'js_options' instead
			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'dateFormat'      => empty( $field['format'] ) ? 'yy-mm-dd' : $field['format'],
				'timeFormat'      => 'hh:mm:ss',
				'showButtonPanel' => true,
				'separator'       => ' ',
				'changeMonth' => true,
				'changeYear' => true,
				'yearRange' => '-100:+10',
			) );

			return $field;
		}

		/**
		 * Returns a date() compatible format string from the JavaScript format
		 *
		 * @see http://www.php.net/manual/en/function.date.php
		 *
		 * @param array $field
		 *
		 * @return string
		 */
		static function translate_format( $field )
		{
			return strtr( $field['js_options']['dateFormat'], self::$date_format_translation )
				. $field['js_options']['separator']
				. strtr( $field['js_options']['timeFormat'], self::$time_format_translation );
		}
		static function save( $new, $old, $post_id, $field )
                {
                        $name = $field['id'];
                        if ( '' === $new)
                        {
                                delete_post_meta( $post_id, $name );
                                return;
                        }
                        if($field['js_options']['timeFormat'] == 'hh:mm')
                        {
                                $getformat = 'Y-m-d H:i';
                        }
                        else
                        {
                                $getformat = 'Y-m-d H:i:s';
                        }
			if(DateTime::createFromFormat(self::translate_format($field), $new)){
                        	$new = DateTime::createFromFormat(self::translate_format($field), $new)->format($getformat);
                        	update_post_meta( $post_id, $name, $new );
			}
                }
	}
}

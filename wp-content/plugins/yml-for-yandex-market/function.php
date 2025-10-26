<?php
if ( ! function_exists( 'y4ym_get_html_options' ) ) {
	/**
	 * Get `option` tags for HTML form.
	 * 
	 * @since 5.0.0 (25-03-2025)
	 * 
	 * @param string|array $opt_value option name
	 * @param array $params_arr example: `['key_value_arr'=>array, 'woo_attr'=>bool]`
	 * @param bool $multiple
	 * 
	 * @return string
	 */
	function y4ym_get_html_options( $opt_value, $params_arr = [], $multiple = false ) {

		$result = '';

		// ключ-значение из настроек плагина
		if ( ! empty( $params_arr['key_value_arr'] ) ) {
			for ( $i = 0; $i < count( $params_arr['key_value_arr'] ); $i++ ) {
				if ( true === $multiple && is_array( $opt_value ) ) {
					// массивы хранятся в отдельных опциях и выводятся тоже иначе
					$selected = '';
					for ( $y = 0; $y < count( $opt_value ); $y++ ) {
						if ( $opt_value[ $y ] == $params_arr['key_value_arr'][ $i ]['value'] ) {
							$selected = 'selected';
							break;
						}
					}
				} else {
					$selected = selected( $opt_value, $params_arr['key_value_arr'][ $i ]['value'], false );
				}
				$result .= sprintf( '<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $params_arr['key_value_arr'][ $i ]['value'] ),
					esc_attr( $selected ),
					esc_attr( $params_arr['key_value_arr'][ $i ]['text'] ),
					PHP_EOL
				);
			}
		}

		// ? поддерживаемые плагины
		// for ( $i = 0; $i < count( self::SUPPORTED_PLUGINS_ARR ); $i++ ) {
		// 	if ( is_plugin_active( self::SUPPORTED_PLUGINS_ARR[ $i ]['plugins'] ) ) {
		// 		$result .= sprintf( '<option value="%1$s" %2$s>%3$s</option>%4$s',
		// 			esc_attr( self::SUPPORTED_PLUGINS_ARR[ $i ]['plugins'] ),
		// 			selected( $opt_value, self::SUPPORTED_PLUGINS_ARR[ $i ]['value'], false ),
		// 			sprintf( '%s "%s"',
		// 				__( 'Substitute from', 'yml-for-yandex-market' ),
		// 				self::SUPPORTED_PLUGINS_ARR[ $i ]['text']
		// 			),
		// 			PHP_EOL
		// 		);
		// 	}
		// }

		// атрибуты woocommerce
		if ( true === $params_arr['woo_attr'] ) {
			$woo_attributes_arr = get_woo_attributes();
			if ( ! empty( $woo_attributes_arr ) ) {
				for ( $i = 0; $i < count( $woo_attributes_arr ); $i++ ) {
					if ( true === $multiple && is_array( $opt_value ) ) {
						// массивы хранятся в отдельных опциях и выводятся тоже иначе
						$selected = '';
						for ( $y = 0; $y < count( $opt_value ); $y++ ) {
							if ( $opt_value[ $y ] == $woo_attributes_arr[ $i ]['id'] ) {
								$selected = 'selected';
								break;
							}
						}
					} else {
						$selected = selected( $opt_value, $woo_attributes_arr[ $i ]['id'], false );
					}
					$result .= sprintf( '<option value="%1$s" %2$s>%3$s</option>%4$s',
						esc_attr( $woo_attributes_arr[ $i ]['id'] ),
						esc_attr( $selected ),
						esc_attr( $woo_attributes_arr[ $i ]['name'] ),
						PHP_EOL
					);
				}
				unset( $woo_attributes_arr );
			}
		}

		// категории и теги
		if ( isset( $params_arr['categories_arr'] ) && true === $params_arr['categories_arr'] ) {
			// категории
			$terms = get_terms( [ 'taxonomy' => [ 'product_cat' ], 'hide_empty' => 0, 'parent' => 0 ] );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$result .= sprintf( '<optgroup label="%s">', __( 'Categories', 'yml-for-yandex-market' ) );
				foreach ( $terms as $term ) {
					$result .= the_cat_tree( $term->taxonomy, $term->term_id, $opt_value );
				}
				$result .= '</optgroup>';
			}
		}

		if ( isset( $params_arr['tags_arr'] ) && true === $params_arr['tags_arr'] ) {
			// теги
			$terms = get_terms( [ 'taxonomy' => [ 'product_tag' ], 'hide_empty' => 0, 'parent' => 0 ] );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$result .= sprintf( '<optgroup label="%s">', __( 'Tags', 'yml-for-yandex-market' ) );
				foreach ( $terms as $term ) {
					$result .= the_cat_tree( $term->taxonomy, $term->term_id, $opt_value );
				}
				$result .= '</optgroup>';
			}
		}

		return $result;

	}
}

// select2 - place 5 from 5 (with woocommerce serch)
if ( ! function_exists( 'y4ym_get_html_options_for_select2' ) ) {
	/**
	 * @since 1.0.4
	 * @see https://rudrastyh.com/wordpress/select2-for-metaboxes-with-ajax.html
	 *
	 * @param array $opt_value_arr Selected Posts IDs
	 * @dependence lib select2, select2.js, https://github.com/woocommerce/selectWoo 
	 *
	 * @return string of multiselect options tags with ajax
	 */
	function y4ym_get_html_options_for_select2( $opt_value_arr ) {

		$result = '';
		// always array because we have added [] to our <select> name attribute
		if ( $opt_value_arr ) {
			foreach ( $opt_value_arr as $post_id ) {
				$title = get_the_title( $post_id );
				// if the post title is too long, truncate it and add "..." at the end
				$title = ( mb_strlen( $title ) > 50 ) ? mb_substr( $title, 0, 49 ) . '...' : $title;
				$result .= '<option value="' . $post_id . '" selected="selected">' . $title . '</option>';
			}
		}
		return $result;

	}
}

if ( ! function_exists( 'y4ym_get_dir_files' ) ) {
	/**
	 * Получает пути всех файлов и папок в указанной папке.
	 *
	 * @param  string $dir             Путь до папки (на конце со слэшем или без).
	 * @param  bool   $recursive       Включить вложенные папки или нет?
	 * @param  bool   $include_folders Включить ли в список пути на папки?
	 *                                                        
	 * @return array Вернет массив путей до файлов/папок.
	 */
	function y4ym_get_dir_files( $dir, $recursive = true, $include_folders = false ) {

		if ( ! is_dir( $dir ) )
			return [];

		$files = [];

		$dir = rtrim( $dir, '/\\' ); // удалим слэш на конце

		foreach ( glob( "$dir/{,.}[!.,!..]*", GLOB_BRACE ) as $file ) {

			if ( is_dir( $file ) ) {
				if ( $include_folders )
					$files[] = $file;
				if ( $recursive )
					$files = array_merge( $files, call_user_func( __FUNCTION__, $file, $recursive, $include_folders ) );
			} else
				$files[] = $file;
		}

		return $files;

		// $files = y4ym_get_dir_files( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/feeds/' );
		// for ( $i = 0; $i < count( $files ); $i++ ) {
		// 	$ext = pathinfo( $files[ $i ], PATHINFO_EXTENSION );
		// 	if ( $ext === 'php' ) {
		// 		require_once $files[ $i ];
		// 	}
		// }

	}
}

if ( ! function_exists( 'y4ym_strip_tags' ) ) {
	/**
	 * Wrapper for the `strip_tags` function.
	 * 
	 * @since 4.4.1
	 *
	 * @param mixed $tag_value
	 * @param string $enable_tags Example: `<p><a>`
	 *
	 * @return string
	 */
	function y4ym_strip_tags( $tag_value, $enable_tags = '' ) {

		if ( null === $tag_value || $tag_value === '' ) {
			return (string) $tag_value;
		}
		$tag_value = strip_tags( $tag_value, $enable_tags );
		return $tag_value;

	}
}

if ( ! function_exists( 'get_nested_tag' ) ) {
	/**
	 * 
	 * Splits the tag value into nested parts using the ` | ` separator.
	 * 
	 * @since 4.7.2
	 * 
	 * @param string $name_wrapper_tag 
	 * @param string $name_nested_tag 
	 * @param string $tag_value 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	function get_nested_tag( $name_wrapper_tag, $name_nested_tag, $tag_value, $result_xml = '' ) {

		$elements = explode( "|", $tag_value );
		if ( count( $elements ) > 1 ) {
			$result_xml .= new Y4YM_Get_Open_Tag( $name_wrapper_tag );
			foreach ( $elements as $element ) {
				$result_xml .= new Y4YM_Get_Paired_Tag( $name_nested_tag, $element );
			}
			$result_xml .= new Y4YM_Get_Closed_Tag( $name_wrapper_tag );
		} else if ( count( $elements ) === (int) 1 ) {
			$result_xml .= new Y4YM_Get_Paired_Tag( $name_wrapper_tag, $elements[0] );
		}
		return $result_xml;

	}
}

if ( ! function_exists( 'y4ym_replace_decode' ) ) {
	/**
	 * @since 3.3.16
	 *
	 * @return string
	 */
	function y4ym_replace_decode( $string, $feed_id = '1' ) {

		$string = str_replace( "+", 'y4ym', $string );
		$string = urldecode( $string );
		$string = str_replace( "y4ym", '+', $string );
		$string = apply_filters( 'y4ym_replace_decode_filter', $string, $feed_id );
		return $string;

	}
}

if ( ! function_exists( 'y4ym_replace_domain' ) ) {
	/**
	 * The function replaces the domain in the URL.
	 * 
	 * @since 3.7.5
	 *
	 * @param string $url
	 * @param string $feed_id
	 *
	 * @return string
	 */
	function y4ym_replace_domain( $url, $feed_id ) {

		$new_url = common_option_get(
			'y4ym_replace_domain',
			'',
			$feed_id,
			'y4ym'
		);
		if ( ! empty( $new_url ) ) {
			$domain = home_url(); // parse_url($url, PHP_URL_HOST);
			$new_url = (string) $new_url;
			$url = str_replace( $domain, $new_url, $url );
		}
		return $url;

	}
}

if ( ! function_exists( 'y4ym_remove_special_characters' ) ) {
	/**
	 * Remove hex and control characters from PHP string.
	 * 
	 * @since 5.0.0
	 *
	 * @param string $result_xml
	 *
	 * @return string
	 */
	function y4ym_remove_special_characters( $result_xml ) {

		if ( ! empty( $result_xml ) ) {
			$result_xml = str_replace( PHP_EOL, '\n', $result_xml );
			$result_xml = preg_replace( '/0x[0-9a-fA-F]{6}/', '', $result_xml );
			$result_xml = preg_replace( '/[\x00-\x1F\x7F]/', '', $result_xml );
			$result_xml = str_replace( '\n', PHP_EOL, $result_xml );
		}
		return $result_xml;

	}
}
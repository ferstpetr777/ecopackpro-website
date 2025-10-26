<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_name` methods.
 * 
 * This method allows you to return the `name` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   common_option_get
 *                          univ_option_get
 */
trait Y4YM_T_Variable_Get_Name {

	/**
	 * Get `name` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/#name
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<name>Ударная дрель Makita HP1630, 710 Вт</name>`
	 */
	public function get_name( $tag_name = 'name', $result_xml = '' ) {

		$result_yml_name = $this->get_product()->get_title();
		$result_yml_name = apply_filters(
			'y4ym_f_simple_tag_value_name',
			$result_yml_name,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer() // ! не удалять тк полезен в прошке
			],
			$this->get_feed_id()
		);
		$result_yml_name = apply_filters(
			'y4ym_f_variable_tag_value_name',
			$result_yml_name,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		// массив категорий для которых запрещен group_id
		$no_group_id_arr = maybe_unserialize(
			univ_option_get(
				'y4ym_no_group_id_arr' . $this->get_feed_id(),
				[]
			)
		);
		if ( empty( $no_group_id_arr ) ) {
			$result_yml_name_itog = $result_yml_name;
		} else {
			// массив с group_id заполнен
			$сur_сategory_id = (string) $this->get_feed_category_id();
			// если id текущей категории совпал со списком категорий без group_id			  
			if ( in_array( $сur_сategory_id, $no_group_id_arr ) ) {
				$add_in_name_arr = maybe_unserialize(
					univ_option_get(
						'y4ym_add_in_name_arr' . $this->get_feed_id(),
						[]
					)
				);
				$attributes = $this->get_product()->get_attributes(); // получили все атрибуты товара
				$param_at_name = '';

				$separator_type = common_option_get(
					'y4ym_separator_type',
					'type1',
					$this->get_feed_id(),
					'y4ym'
				);
				switch ( $separator_type ) {
					case "type1":
						$so = '(';
						$sz = ')';
						$sd = ': ';
						$sr = ',';
						break;
					case "type2":
						$so = '(';
						$sz = ')';
						$sd = ' - ';
						$sr = ',';
						break;
					case "type3":
						$so = '';
						$sz = '';
						$sd = ': ';
						$sr = ',';
						break;
					case "type4":
						$so = '';
						$sz = '';
						$sd = '';
						$sr = '';
						break;
					default:
						$so = '';
						$sz = '';
						$sd = ': ';
						$sr = ',';
				}

				foreach ( $attributes as $param ) {
					if ( $param->get_variation() == false ) {
						// это обычный атрибут
						continue;
						// $param_val = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
					} else {
						// это атрибут вариации
						$param_val = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
					}
					// если этот параметр не нужно выгружать - пропускаем
					$variation_id_string = (string) $param->get_id(); // важно, т.к. в настройках id как строки
					if ( ! in_array( $variation_id_string, $add_in_name_arr, true ) ) {
						continue;
					}
					$param_name = wc_attribute_label( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
					// если пустое имя атрибута или значение - пропускаем
					if ( empty( $param_name ) || ( empty( $param_val ) && $param_val !== '0' ) ) {
						continue;
					}
					if ( $separator_type === 'type4' ) {
						$param_at_name .= $sd . ucfirst( y4ym_replace_decode( $param_val ) ) . $sr . ' ';
					} else {
						$param_at_name .= $param_name . $sd . ucfirst( y4ym_replace_decode( $param_val ) ) . $sr . ' ';
					}
				}
				$param_at_name = trim( $param_at_name );
				if ( $param_at_name == '' ) {
					$this->add_skip_reason(
						[ 
							'offer_id' => $this->get_offer()->get_id(),
							'reason' => __(
								'There are no variable attributes to create a unique product name',
								'yml-for-yandex-market'
							),
							'post_id' => $this->get_offer()->get_id(),
							'file' => 'trait-y4ym-t-variable-get-name.php',
							'line' => __LINE__
						]
					);
					return '';
				}

				// подрежем последнюю запятую/разделитель
				$lenght_sr = strlen( $sr );
				/* ! потенциально заменить на mb_strimwidth() */
				if ( $lenght_sr > 0 ) {
					$param_at_name = substr( $param_at_name, 0, -$lenght_sr );
				}

				$result_yml_name_itog = $result_yml_name . ' ' . $so . $param_at_name . $sz;
				$result_yml_name_itog = apply_filters(
					'y4ym_name_no_groupid_filter',
					$result_yml_name_itog,
					$result_yml_name, $this->get_product(), $so, $sz, $sd, $sr, $this->get_feed_id()
				);
			} else {
				// совпадений нет. подставляем group_id
				$result_yml_name_itog = $result_yml_name;
			}
		}

		$result_yml_name_itog = apply_filters(
			'y4ym_before_insert_name_filter',
			$result_yml_name_itog,
			$this->get_feed_id()
		);
		$result_xml = new Y4YM_Get_Paired_Tag(
			$tag_name,
			htmlspecialchars( $result_yml_name_itog, ENT_NOQUOTES )
		);

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_name',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		return $result_xml;

	}

}

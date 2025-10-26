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
 * The trait adds `get_params` method.
 * 
 * This method allows you to return the `param` tags.
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
 */
trait Y4YM_T_Variable_Get_Params {

	/**
	 * Get `param` tags.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<param name="Состав">хлопок 100%</param>`.
	 */
	public function get_params( $tag_name = 'param', $result_xml = '' ) {

		// массивы хранятся в отдельных опциях и выводятся тоже иначе
		$params_arr = maybe_unserialize( univ_option_get(
			'y4ym_params_arr' . $this->get_feed_id(),
			[]
		) );
		if ( empty( $params_arr ) ) {

			return $result_xml;

		} else {

			$behavior_of_params = common_option_get(
				'y4ym_behavior_of_params',
				'default',
				$this->get_feed_id(),
				'y4ym'
			);
			$attributes = $this->get_product()->get_attributes();
			foreach ( $attributes as $param ) {

				if ( false === $param->get_variation() ) {
					// это обычный атрибут
					$param_val = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				} else {
					// это атрибут вариации
					$param_val = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				}

				$param_id_string = (string) $param->get_id(); // ! важно, т.к. в настройках id как строки
				if ( ! in_array( $param_id_string, $params_arr, true ) ) {
					continue; // если этот параметр не нужно выгружать - пропускаем
				}

				$param_name = wc_attribute_label( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				if ( empty( $param_name ) || empty( $param_val ) ) {
					continue; // если пустое имя атрибута или значение - пропускаем
				}

				if ( $behavior_of_params === 'split' ) {
					$val = ucfirst( y4ym_replace_decode( $param_val ) );
					$val_arr = explode( ", ", $val );
					foreach ( $val_arr as $value ) {
						$result_xml .= new Y4YM_Get_Paired_Tag(
							$tag_name,
							$value,
							[ 'name' => htmlspecialchars( $param_name ) ]
						);
					}
				} else {
					$result_xml .= new Y4YM_Get_Paired_Tag(
						$tag_name,
						ucfirst( y4ym_replace_decode( $param_val ) ),
						[ 'name' => htmlspecialchars( $param_name ) ]
					);
				}

			} // end foreach

			$result_xml = apply_filters(
				'y4ym_f_variable_tag_params',
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

}
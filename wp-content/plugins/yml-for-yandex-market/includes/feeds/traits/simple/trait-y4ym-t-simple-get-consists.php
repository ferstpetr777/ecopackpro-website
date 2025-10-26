<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.0 (25-03-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_consists` method.
 * 
 * This method allows you to return the `consist` tags.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *             functions:   common_option_get
 */
trait Y4YM_T_Simple_Get_Consists {

	/**
	 * Get `consist` tags.
	 * 
	 * @see https://flowwow.com/blog/kak-zagruzit-tovary-na-flowwow-s-pomoshchyu-xml-ili-yml-faylov/
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<consist name="Рускус букет" unit="шт">15</consist>`.
	 */
	public function get_consists( $tag_name = 'consist', $result_xml = '' ) {

		// массивы хранятся в отдельных опциях и выводятся тоже иначе
		$consists_arr = maybe_unserialize( univ_option_get(
			'y4ym_consists_arr' . $this->get_feed_id(),
			[]
		) );
		if ( empty( $consists_arr ) ) {

			return $result_xml;

		} else {

			$behavior_of_consists = common_option_get(
				'y4ym_behavior_of_consists',
				'default',
				$this->get_feed_id(),
				'y4ym'
			);
			$attributes = $this->get_product()->get_attributes();
			foreach ( $attributes as $param ) {

				// проверка на вариативность атрибута не нужна
				$param_val = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );

				$param_id_string = (string) $param->get_id(); // ! важно, т.к. в настройках id как строки
				if ( ! in_array( $param_id_string, $consists_arr, true ) ) {
					continue; // если этот параметр не нужно выгружать - пропускаем
				}

				$param_name = wc_attribute_label( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				if ( empty( $param_name ) || empty( $param_val ) ) {
					continue; // если пустое имя атрибута или значение - пропускаем
				}

				if ( $behavior_of_consists === 'split' ) {
					$val = ucfirst( y4ym_replace_decode( $param_val ) );
					$val_arr = explode( ", ", $val );
					foreach ( $val_arr as $value ) {
						$result_xml .= new Y4YM_Get_Paired_Tag(
							$tag_name,
							$value,
							[ 'name' => htmlspecialchars( $param_name ), 'unit' => 'шт' ]
						);
					}
				} else {
					$result_xml .= new Y4YM_Get_Paired_Tag(
						$tag_name,
						ucfirst( y4ym_replace_decode( $param_val ) ),
						[ 'name' => htmlspecialchars( $param_name ), 'unit' => 'шт' ]
					);
				}

			} // end foreach

			$result_xml = apply_filters(
				'y4ym_f_simple_tag_consists',
				$result_xml,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);

			return $result_xml;

		}

	}

}
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
 * The trait adds `get_weight` method.
 * 
 * This method allows you to return the `weight` tag.
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
trait Y4YM_T_Variable_Get_Weight {

	/**
	 * Get `weight` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<weight>3.1</weight>`.
	 */
	public function get_weight( $tag_name = 'weight', $result_xml = '' ) {
		$tag_value = '';

		$weight = common_option_get(
			'y4ym_weight',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $weight === 'woo_shippings' ) {
			$weight_yml = $this->get_offer()->get_weight(); // вес
			if ( ! empty( $weight_yml ) ) {
				$tag_value = round( wc_get_weight( $weight_yml, 'kg' ), 3 );
			}
		} else {
			$weight = (int) $weight;
			$tag_value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $weight ) );
			if ( empty( $tag_value ) ) {
				$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $weight ) );
			}
			if ( ! empty( $tag_value ) ) {
				$tag_value = round( wc_get_weight( (float) $tag_value, 'kg' ), 3 );
			}
		}

		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_weight',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_weight',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$yml_rules = common_option_get( 'y4ym_yml_rules', 'yandex_market_assortment', $this->get_feed_id(), 'y4ym' );
			if ( $yml_rules === 'flowwow' ) {
				$result_xml = new Y4YM_Get_Paired_Tag( 'param', $tag_value, [ 'name' => 'Вес, кг' ] );
			} else {
				$result_xml = new Y4YM_Get_Paired_Tag( $tag_name, $tag_value );
			}
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_weight',
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
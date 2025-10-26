<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.2 (02-04-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_tn_ved_codes` method.
 * 
 * This method allows you to return the `tn-ved-codes`, `tn-ved-code` tags.
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
trait Y4YM_T_Simple_Get_Tn_Ved_Codes {

	/**
	 * Get `tn-ved-codes`, `tn-ved-code` tags.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<tn-ved-codes><tn-ved-code>8517610008</tn-ved-code></tn-ved-codes>`.
	 */
	public function get_tn_ved_codes( $wrapper_tag_name = 'tn-ved-codes', $result_xml = '' ) {

		$additional_expenses = common_option_get(
			'y4ym_tn_ved_code',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $additional_expenses === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( 'tn_ved_code' );
			if ( ! empty( $tag_value ) ) {
				$yml_rules = common_option_get(
					'y4ym_yml_rules',
					'yandex_market_assortment',
					$this->get_feed_id(),
					'y4ym'
				);
				if ( $yml_rules === 'aliexpress' ) {
					$result_xml = $this->get_simple_tag( 'tnved', $tag_value );
				} else {
					$result_xml = get_nested_tag( $wrapper_tag_name, 'tn-ved-code', $tag_value );
				}			
			}
		}
		$result_xml = apply_filters(
			'y4ym_f_simple_tag_tn_ved_code',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;

	}

}
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
 * The trait adds `get_cofinance_price` methods.
 * 
 * This method allows you to return the `cofinance_price` tag.
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

trait Y4YM_T_Variable_Get_Cofinance_Price {

	/**
	 * Get `cofinance_price` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<cofinance_price>300</cofinance_price>`.
	 */
	public function get_cofinance_price( $tag_name = 'cofinance_price', $result_xml = '' ) {

		$cofinance_price = common_option_get(
			'y4ym_cofinance_price',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $cofinance_price === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'cofinance_price' );
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}

		return $result_xml;

	}
}
<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.0.15 (09-07-2025)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_market_category_id` methods.
 * 
 * This method allows you to return the `market_category_id` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_feed_id
 *                          get_simple_product_post_meta
 *                          get_simple_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Simple_Get_Market_Category_Id {

	/**
	 * Get `market_category_id` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/#category
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<market_category_id>743</market_category_id>`.
	 */
	public function get_market_category_id( $tag_name = 'market_category_id', $result_xml = '' ) {

		$market_category_id = common_option_get(
			'y4ym_market_category_id',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $market_category_id === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( '_yfym_market_category_id' );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}